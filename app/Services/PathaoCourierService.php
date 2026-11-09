<?php

namespace App\Services;

use App\Models\Courier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourierService
{
    protected string $baseUrl;

    protected string $clientId;

    protected string $clientSecret;

    protected string $username;

    protected string $password;

    protected string $storeId;

    public function __construct(Courier $courier)
    {
        $config = $courier->api_config ?? [];

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api-hermes.pathao.com', '/');

        $params = $config['params'] ?? [];
        $mappedParams = collect($params)->pluck('value', 'key')->toArray();

        $this->clientId = $mappedParams['client_id'] ?? '';
        $this->clientSecret = $mappedParams['client_secret'] ?? '';
        $this->username = $mappedParams['username'] ?? '';
        $this->password = $mappedParams['password'] ?? '';
        $this->storeId = $mappedParams['store_id'] ?? '';
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret) && ! empty($this->username) && ! empty($this->password);
    }

    protected function getAccessToken()
    {
        $cacheKey = 'pathao_access_token_'.md5($this->clientId.$this->username);

        return Cache::remember($cacheKey, 86400 * 5, function () {
            $response = Http::when(app()->environment('local'), fn ($http) => $http->withoutVerifying())->post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Pathao Token Error: '.$response->body());

            return null;
        });
    }

    public function getStores(): array
    {
        return $this->makeRequest('GET', '/aladdin/api/v1/stores');
    }

    public function placeOrder(array $orderData): array
    {
        $pathaoData = [
            'store_id' => $this->storeId,
            'merchant_order_id' => $orderData['invoice'] ?? '',
            'sender_name' => 'System Sender',
            'sender_phone' => '01700000000',
            'recipient_name' => $orderData['recipient_name'] ?? 'Customer',
            'recipient_phone' => $orderData['recipient_phone'] ?? '',
            'recipient_address' => $orderData['recipient_address'] ?? '',
            'recipient_city' => $orderData['city_id'] ?? 1,
            'recipient_zone' => $orderData['zone_id'] ?? 1,
            'recipient_area' => $orderData['area_id'] ?? 1,
            'delivery_type' => 48,
            'item_type' => 2,
            'special_instruction' => $orderData['note'] ?? '',
            'item_quantity' => 1,
            'item_weight' => 0.5,
            'amount_to_collect' => $orderData['cod_amount'] ?? 0,
            'item_description' => 'Products from '.config('app.name'),
        ];

        return $this->makeRequest('POST', '/aladdin/api/v1/orders', $pathaoData);
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return [
                'success' => false,
                'status' => 401,
                'data' => ['message' => 'Failed to generate Pathao Access Token.'],
            ];
        }

        try {
            $url = $this->baseUrl.$endpoint;

            $request = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())->timeout(30);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                default => $request->get($url, $data),
            };

            $result = $response->json() ?? [];

            Log::channel('single')->info('Pathao API Response', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $result,
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $result,
            ];

        } catch (\Exception $e) {
            Log::channel('single')->error('Pathao API Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'data' => ['message' => 'API connection failed: '.$e->getMessage()],
            ];
        }
    }

    public static function mapStatus(string $status): string
    {
        $statusMap = [
            'Pending' => 'pending',
            'Pick Up Requested' => 'processing',
            'Merchant Collected' => 'processing',
            'In Transit' => 'shipped',
            'Delivered' => 'delivered',
            'Cancelled' => 'cancelled',
            'Returned' => 'returned',
            'Return In Transit' => 'returned',
            'Return Processed' => 'returned',
            'On Hold' => 'on_hold',
        ];

        return $statusMap[$status] ?? 'pending';
    }
}
