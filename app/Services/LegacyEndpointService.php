<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LegacyEndpointService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.legacy_api.base_url', env('LEGACY_API_BASE_URL'));
        $this->token = config('services.legacy_api.token', env('LEGACY_API_TOKEN'));
    }

    /**
     * Fetch sales orders from the legacy API.
     *
     * @return array
     */
    public function fetchSalesOrders(): array
    {
        Log::info('Fetching sales orders from legacy API...');

        $response = Http::withToken($this->token)
            ->timeout(120)
            ->get("{$this->baseUrl}/salesOrder");

        if ($response->failed()) {
            Log::error('Failed to fetch sales orders from legacy API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $data = $response->json();

        // Assuming the data is in a 'data' key or is a direct array
        return $data['data'] ?? $data ?? [];
    }

    /**
     * Fetch users from the legacy API.
     *
     * @return array
     */
    public function fetchUsers(): array
    {
        Log::info('Fetching users from legacy API...');

        $response = Http::withToken($this->token)
            ->timeout(120)
            ->get("{$this->baseUrl}/users");

        if ($response->failed()) {
            Log::error('Failed to fetch users from legacy API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $data = $response->json();

        return $data['data'] ?? $data ?? [];
    }
}
