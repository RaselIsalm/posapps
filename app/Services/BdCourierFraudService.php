<?php

namespace App\Services;

use App\Models\FraudCheckSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BdCourierFraudService
{
    protected ?string $apiKey;

    protected ?string $baseUrl;

    protected bool $isActive;

    public function __construct()
    {
        $setting = FraudCheckSetting::first();

        $this->apiKey = $setting?->api_key ?? null;
        $this->baseUrl = $setting?->api_url ?? null;
        $this->isActive = (bool) ($setting?->is_active ?? false);
    }

    public function hasCredentials(): bool
    {
        return $this->isActive && ! empty($this->apiKey) && ! empty($this->baseUrl);
    }

    public function checkPhone(string $phone): array
    {
        if (! $this->hasCredentials()) {
            return [
                'success' => false,
                'configured' => false,
                'message' => 'Fraud check is turned off. Enable it in API Settings.',
                'data' => null,
            ];
        }

        $phone = $this->normalizePhone($phone);
        if (! $this->isValidBdPhone($phone)) {
            return [
                'success' => false,
                'configured' => true,
                'message' => 'Enter a valid Bangladesh mobile number.',
                'data' => null,
            ];
        }

        $cacheKey = 'bdcourier_fraud_'.md5($phone);
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false)) {
            return $cached;
        }

        $result = $this->makeRequest($phone);
        if ($result['success']) {
            Cache::put($cacheKey, $result, 1800);
        }

        return $result;
    }

    public function checkPhoneFresh(string $phone): array
    {
        $phone = $this->normalizePhone($phone);
        Cache::forget('bdcourier_fraud_'.md5($phone));

        return $this->checkPhone($phone);
    }

    protected function isValidBdPhone(string $phone): bool
    {
        return (bool) preg_match('/^01[3-9]\d{8}$/', $phone);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '880') && strlen($phone) >= 13) {
            $phone = '0'.substr($phone, 3);
        }

        return $phone;
    }

    protected function makeRequest(string $phone): array
    {
        try {
            $url = rtrim((string) $this->baseUrl, '/');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->connectTimeout(5)
                ->timeout(8)
                ->get($url, [
                    'phone' => $phone,
                    'number' => $phone,
                ]);

            Log::info('Fraud check request', [
                'phone' => $phone,
                'status' => $response->status(),
            ]);

            $result = $response->json() ?? [];

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'configured' => true,
                    'message' => $result['message'] ?? 'Fraud check API error ('.$response->status().').',
                    'data' => null,
                ];
            }

            $payload = $result['data'] ?? $result;
            $parsed = $this->parseCourierPayload($payload);

            return [
                'success' => true,
                'configured' => true,
                'message' => 'Fraud check completed.',
                'data' => $parsed['totals'],
                'couriers' => $parsed['couriers'],
            ];
        } catch (\Exception $e) {
            Log::warning('Fraud check connection failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'configured' => true,
                'message' => 'Could not reach the fraud check service. Try again.',
                'data' => null,
            ];
        }
    }

    protected function parseCourierPayload(mixed $payload): array
    {
        $total = 0;
        $success = 0;
        $cancel = 0;
        $couriers = [];

        if (! is_array($payload)) {
            return [
                'totals' => [
                    'total_parcel' => 0,
                    'success_parcel' => 0,
                    'cancel_parcel' => 0,
                ],
                'couriers' => [],
            ];
        }

        $looksLikeStats = isset($payload['total_parcel']) || isset($payload['total']) || isset($payload['success_parcel']);

        if ($looksLikeStats && ! $this->isCourierMap($payload)) {
            $total = (int) ($payload['total_parcel'] ?? $payload['total'] ?? 0);
            $success = (int) ($payload['success_parcel'] ?? $payload['success'] ?? 0);
            $cancel = (int) ($payload['cancelled_parcel'] ?? $payload['cancel_parcel'] ?? $payload['cancel'] ?? 0);

            return [
                'totals' => [
                    'total_parcel' => $total,
                    'success_parcel' => $success,
                    'cancel_parcel' => $cancel,
                ],
                'couriers' => [],
            ];
        }

        foreach ($payload as $courier => $stats) {
            if (! is_array($stats)) {
                continue;
            }

            $rowTotal = (int) ($stats['total_parcel'] ?? $stats['total'] ?? 0);
            $rowSuccess = (int) ($stats['success_parcel'] ?? $stats['success'] ?? 0);
            $rowCancel = (int) ($stats['cancelled_parcel'] ?? $stats['cancel_parcel'] ?? $stats['cancel'] ?? 0);

            $total += $rowTotal;
            $success += $rowSuccess;
            $cancel += $rowCancel;

            $couriers[] = [
                'name' => ucfirst(str_replace(['_', '-'], ' ', (string) $courier)),
                'total' => $rowTotal,
                'success' => $rowSuccess,
                'cancel' => $rowCancel,
            ];
        }

        return [
            'totals' => [
                'total_parcel' => $total,
                'success_parcel' => $success,
                'cancel_parcel' => $cancel,
            ],
            'couriers' => $couriers,
        ];
    }

    protected function isCourierMap(array $payload): bool
    {
        foreach ($payload as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    public static function assessRisk(array $data): array
    {
        $total = (int) ($data['total_parcel'] ?? 0);
        $delivered = (int) ($data['success_parcel'] ?? 0);
        $rate = $total > 0 ? round(($delivered / $total) * 100) : 0;

        if ($total === 0) {
            return [
                'level' => 'unknown',
                'label' => 'New customer',
                'color' => 'secondary',
                'success_rate' => 0,
                'hint' => 'No courier history for this number.',
            ];
        }

        if ($rate >= 70) {
            return [
                'level' => 'low',
                'label' => 'Low risk',
                'color' => 'success',
                'success_rate' => $rate,
                'hint' => 'Good delivery record. Safe to ship.',
            ];
        }

        if ($rate >= 40) {
            return [
                'level' => 'medium',
                'label' => 'Medium risk',
                'color' => 'warning',
                'success_rate' => $rate,
                'hint' => 'Mixed record. Confirm the address before shipping.',
            ];
        }

        return [
            'level' => 'high',
            'label' => 'High risk',
            'color' => 'danger',
            'success_rate' => $rate,
            'hint' => 'High cancel rate. Confirm with the customer before shipping.',
        ];
    }
}
