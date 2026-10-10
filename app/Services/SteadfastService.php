<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SteadfastService
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('steadfast_api_key', '');
        $this->secretKey = Setting::getValue('steadfast_secret_key', '');
        $this->baseUrl = 'https://portal.packzy.com/api/v1';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    protected function headers(): array
    {
        return [
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createOrder(array $data): array
    {
        $response = Http::withHeaders($this->headers())->post("{$this->baseUrl}/create_order", $data);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('Steadfast API Error: ' . $response->body());
        return [
            'status' => $response->status(),
            'message' => 'Steadfast API request failed.',
        ];
    }

    /**
     * Steadfast wants the local 11 digit form (01XXXXXXXXX), but orders can
     * hold +8801XXXXXXXXX or 8801XXXXXXXXX, so keep the last 11 digits.
     */
    public static function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return strlen($digits) > 11 ? substr($digits, -11) : (string) $digits;
    }

    /**
     * Delivery history for a customer number, from GET /fraud_check/score/{phone}.
     *
     * This endpoint reports proportions, not tallies: it answers with
     * volume_range, delivery_ratio and cancellation_ratio and no delivered or
     * cancelled count. The raw counts live on the sibling /fraud_check/{phone}.
     * Since the counts are wanted here, they are reconstructed from the volume
     * and the ratios, and flagged as derived so the UI can say so.
     *
     * Every counter stays nullable. A missing counter must not render as a
     * clean zero, because "no record" and "zero delivered" are very different
     * signals when deciding whether to ship a COD parcel.
     */
    public function fraudCheck(string $phone): array
    {
        $local = self::normalisePhone($phone);

        if (!preg_match('/^01[3-9]\d{8}$/', $local)) {
            return [
                'ok' => false,
                'phone' => $local,
                'message' => 'This phone number is not a valid 11 digit Bangladeshi number.',
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'phone' => $local,
                'message' => 'Steadfast API credentials are not configured.',
            ];
        }

        // The endpoint is rate limited per merchant, and the order list renders
        // one lookup per row, so identical numbers are answered from cache. The
        // v4 prefix drops answers cached from earlier endpoints and mappings.
        $cached = Cache::remember("steadfast.fraud_check.v4.{$local}", now()->addMinutes(15), function () use ($local) {
            try {
                $response = Http::withHeaders($this->headers())
                    ->timeout(10)
                    ->get("{$this->baseUrl}/fraud_check/score/{$local}");
            } catch (\Throwable $e) {
                \Log::error('Steadfast fraud check request failed: ' . $e->getMessage());

                return ['ok' => false, 'message' => 'Could not reach Steadfast.'];
            }

            if (!$response->successful()) {
                \Log::error('Steadfast fraud check error: ' . $response->body());

                return ['ok' => false, 'message' => 'Steadfast returned an error for this number.'];
            }

            return ['ok' => true, 'payload' => $response->json()];
        });

        if (!($cached['ok'] ?? false)) {
            return ['ok' => false, 'phone' => $local, 'message' => $cached['message'] ?? 'Fraud check failed.'];
        }

        // Steadfast has answered this same endpoint with and without the
        // delivered/cancelled counts depending on the number, so every payload
        // is recorded rather than only the ones that come back short.
        \Log::info('Steadfast fraud check payload for ' . $local . ': ' . json_encode($cached['payload'] ?? []));

        return $this->normaliseFraudPayload($cached['payload'] ?? [], $local);
    }

    /**
     * Flatten either fraud_check payload shape into the counters the admin UI
     * shows. Only scalars are extracted so the view never has to guess.
     */
    protected function normaliseFraudPayload(array $payload, string $phone): array
    {
        $data = $this->unwrap($payload);

        $reports = $this->firstScalar($data, ['fraud_reports', 'total_fraud_reports', 'total_reports'])
            ?? count((array) ($data['frauds'] ?? $data['fraud_categories'] ?? []));

        $delivered = $this->firstScalar($data, ['delivered_count', 'total_delivered', 'delivered', 'success'])
            ?? $this->countShapedKey($data, '/deliver|success/i');
        $cancelled = $this->firstScalar($data, ['cancelled_count', 'total_cancelled', 'cancelled', 'cancel'])
            ?? $this->countShapedKey($data, '/cancel/i');

        // volume_range holds the parcel count, but Steadfast caps it as "50+"
        // once a number has a long history. It stays text so a capped volume is
        // never reported as an exact one.
        $volumeRange = $this->firstString($data, ['volume_range']);
        $parcels = $this->firstScalar($data, ['Total_parcels', 'total_parcels', 'total_parcel'])
            ?? $this->firstScalar($data, ['volume_range']);

        $deliveryRatio = $this->firstScalar($data, ['delivery_ratio', 'success_ratio', 'delivered_percentage']);
        $cancelRatio = $this->firstScalar($data, ['cancellation_ratio', 'cancel_ratio']);

        // The score endpoint sends no counts, so rebuild them from the parcel
        // volume and the ratios. Only possible with an exact volume: "50+"
        // cannot be multiplied out, and guessing there would invent a number.
        $derived = false;

        if (($delivered === null || $cancelled === null) && $parcels !== null) {
            if ($deliveryRatio !== null) {
                $delivered ??= (int) round($parcels * $deliveryRatio / 100);
                $derived = true;
            }

            if ($cancelRatio !== null) {
                $cancelled ??= (int) round($parcels * $cancelRatio / 100);
                $derived = true;
            }
        }

        $result = [
            'ok' => true,
            'phone' => $phone,
            'volume_range' => $volumeRange,
            'total_parcels' => $parcels,
            'total_delivered' => $delivered,
            'total_cancelled' => $cancelled,
            'derived' => $derived,
            'total_fraud_reports' => $reports,
            'delivery_ratio' => $deliveryRatio,
            'cancellation_ratio' => $cancelRatio,
            'volume_band' => $this->firstString($data, ['volume_band']),
            'level' => $this->firstString($data, ['level', 'risk_level']),
            'score' => $this->firstScalar($data, ['score']),
            'reported_by_you' => (bool) ($data['reported_by_you'] ?? false),
            'message' => $payload['message'] ?? 'Fraud check completed.',
        ];

        if ($result['total_delivered'] === null || $result['total_cancelled'] === null) {
            // Recorded so the exact payload Steadfast sent can be read off the
            // log instead of guessing at field names from a screenshot.
            \Log::warning('Steadfast fraud check payload missing a parcel counter for ' . $phone . ': ' . json_encode($data));
        }

        return $result;
    }

    /**
     * Last resort for a counter: any numeric field whose name matches and that
     * reads as a tally rather than a percentage.
     */
    protected function countShapedKey(array $data, string $pattern): ?int
    {
        foreach ($data as $key => $value) {
            if (!is_string($key) || !preg_match($pattern, $key)) {
                continue;
            }

            if (preg_match('/ratio|percent|rate|score/i', $key)) {
                continue;
            }

            if (is_array($value)) {
                return count($value);
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * /fraud_check/score/{phone} answers flat at the top level. The
     * fraud_check endpoint nests the same shape under "scorecard", and a
     * {"status":200,"data":{...}} wrapper turns up on some accounts, so try
     * each before falling back to the payload itself.
     */
    protected function unwrap(array $payload): array
    {
        foreach (['scorecard', 'data'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return $payload;
    }

    protected function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * Steadfast reports the fraud report tally as a list on one endpoint and a
     * number on the other, so accept either and report null when absent.
     */
    protected function firstScalar(array $data, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (is_array($value)) {
                return count($value);
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}
