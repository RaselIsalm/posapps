<?php

namespace App\Services;

use App\Models\Courier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $secretKey;

    public function __construct(Courier $courier)
    {
        $config = $courier->api_config ?? [];

        $this->baseUrl = rtrim($config['base_url']);

        $params = $config['params'] ?? [];
        $mappedParams = collect($params)->pluck('value', 'key')->toArray();

        $this->apiKey = $mappedParams['api_key'] ?? '';
        $this->secretKey = $mappedParams['secret_key'] ?? '';
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->secretKey);
    }

    /**
     * Place a single order with SteadFast
     *
     * @param  array  $orderData  [invoice, recipient_name, recipient_phone, recipient_address, cod_amount, note]
     */
    public function placeOrder(array $orderData): array
    {
        return $this->makeRequest('POST', '/create_order', $orderData);
    }

    /**
     * Place bulk orders with SteadFast
     *
     * @param  array  $ordersData  Array of order arrays
     */
    public function bulkCreateOrders(array $ordersData): array
    {
        return $this->makeRequest('POST', '/create_order/bulk-order', [
            'data' => json_encode($ordersData),
        ]);
    }

    public function checkStatusByConsignmentId(int|string $consignmentId): array
    {
        return $this->makeRequest('GET', "/status_by_cid/{$consignmentId}");
    }

    public function checkStatusByInvoice(string $invoice): array
    {
        return $this->makeRequest('GET', "/status_by_invoice/{$invoice}");
    }

    public function checkStatusByTrackingCode(string $trackingCode): array
    {
        return $this->makeRequest('GET', "/status_by_trackingcode/{$trackingCode}");
    }

    public function getCurrentBalance(): array
    {
        return $this->makeRequest('GET', '/get_balance');
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = $this->baseUrl.$endpoint;

            $request = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                default => $request->get($url, $data),
            };

            $result = $response->json() ?? [];

            Log::channel('single')->info('API Response', [
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
            Log::channel('single')->error('API Error', [
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

    public static function mapStatus(string $steadfastStatus): string
    {
        return match ($steadfastStatus) {
            'in_review' => 'processing',
            'pending' => 'pending',
            'delivered' => 'delivered',
            'delivered_approval_pending' => 'delivered',
            'partial_delivered' => 'delivered',
            'partial_delivered_approval_pending' => 'delivered',
            'cancelled' => 'cancelled',
            'cancelled_approval_pending' => 'cancelled',
            'unknown_approval_pending' => 'processing',
            'hold' => 'processing',
            'unknown' => 'processing',
            default => 'pending',
        };
    }
}
