<?php

namespace App\Services;

use App\Models\Setting;
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

    public function createOrder(array $data): array
    {
        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/create_order", $data);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('Steadfast API Error: ' . $response->body());
        return [
            'status' => $response->status(),
            'message' => 'Steadfast API request failed.',
        ];
    }
}
