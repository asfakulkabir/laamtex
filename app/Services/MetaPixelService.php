<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class MetaPixelService
{
    protected string $pixelId;
    protected string $accessToken;
    protected ?string $testEventCode;

    public function __construct()
    {
        $this->pixelId = Setting::getValue('meta_pixel_id', '');
        $this->accessToken = Setting::getValue('meta_conversion_api_token', '');
        $this->testEventCode = Setting::getValue('meta_test_event_code', '');
    }

    public function isEnabled(): bool
    {
        return !empty($this->pixelId);
    }

    public function isConversionApiEnabled(): bool
    {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    public function sendEvent(string $eventName, array $customData = [], array $userData = []): void
    {
        if (!$this->isConversionApiEnabled()) {
            return;
        }

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_source_url' => request()->url(),
                    'action_source' => 'website',
                    'user_data' => array_merge([
                        'client_ip_address' => request()->ip() ?? '127.0.0.1',
                        'client_user_agent' => request()->userAgent() ?? 'Unknown',
                    ], $userData),
                    'custom_data' => $customData,
                ],
            ],
            'access_token' => $this->accessToken,
        ];

        if ($this->testEventCode) {
            $payload['test_event_code'] = $this->testEventCode;
        }

        try {
            Http::timeout(3)
                ->post("https://graph.facebook.com/v18.0/{$this->pixelId}/events", $payload);
        } catch (\Exception $e) {
            \Log::error('Meta Conversion API Error: ' . $e->getMessage());
        }
    }
}
