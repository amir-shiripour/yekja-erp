<?php

namespace Modules\SmsReseller\App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\SmsReseller\App\Models\SmsResellerSetting;

class SmsResellerApiService
{
    protected ?string $apiKey = null;
    protected ?string $baseUrl = null;

    public function __construct()
    {
        $setting = SmsResellerSetting::first();
        if ($setting) {
            $this->apiKey = $setting->api_key;
            $this->baseUrl = rtrim($setting->base_url, '/');
        }
    }


    public function getResellerPaymentList(int $page = 1, int $size = 20): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            return ['success' => false, 'message' => 'تنظیمات API ناقص است.'];
        }

        $offset = (($page - 1) * $size) + 1;

        try {
            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/ResellerPaymentList', [
                'Page' => $offset,
                'Size' => $size
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'message' => 'خطا در ارتباط با سرور: ' . $response->status()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'استثنا در ارتباط: ' . $e->getMessage()];
        }
    }


    public function getAllResellerPayments(): array
    {
        $all = [];
        $offset = 1;
        $limit = 100;

        while (true) {
            try {
                $response = Http::timeout(30)->withHeaders([
                    'ApiKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/api/ResellerPaymentList', [
                    'Page' => $offset,
                    'Size' => $limit
                ]);

                if (!$response->successful()) {
                    break;
                }

                $json = $response->json();
                $items = $json['result']['data'] ?? [];
                if (empty($items)) {
                    break;
                }

                $all = array_merge($all, $items);
                $totalRecords = (int) str_replace(',', '', (string)($json['result']['recordsTotal'] ?? 0));
                if ($totalRecords > 0 && count($all) >= $totalRecords) {
                    break;
                }

                $offset += $limit;
                if ($offset > 50000) break;
            } catch (\Throwable $e) {
                break;
            }
        }

        return $all;
    }


    public function getResellerCreditList(int $page = 1, int $size = 20): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            return ['success' => false, 'message' => 'تنظیمات API ناقص است.'];
        }

        $offset = (($page - 1) * $size) + 1;

        try {
            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/ResellerCreditList', [
                'Page' => $offset,
                'Size' => $size
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'message' => 'خطا در ارتباط با سرور: ' . $response->status()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'استثنا در ارتباط: ' . $e->getMessage()];
        }
    }


    public function getAllResellerCredits(int $maxRecords = 15000): array
    {
        $all = [];
        $offset = 1;
        $limit = 100;

        while ($offset <= $maxRecords) {
            try {
                $response = Http::withHeaders([
                    'ApiKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/api/ResellerCreditList', [
                    'Page' => $offset,
                    'Size' => $limit
                ]);

                if (!$response->successful()) {
                    break;
                }

                $json = $response->json();
                $items = $json['result']['data'] ?? [];
                if (empty($items)) {
                    break;
                }

                $all = array_merge($all, $items);
                $totalRecords = (int) str_replace(',', '', (string)($json['result']['recordsTotal'] ?? 0));
                if ($totalRecords > 0 && count($all) >= $totalRecords) {
                    break;
                }

                $offset += $limit;
            } catch (\Throwable $e) {
                break;
            }
        }

        return $all;
    }


    public function getPaymentList(int $page = 1, int $size = 20): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            return ['success' => false, 'message' => 'تنظیمات API ناقص است.'];
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/GetPaymentList', [
                'Page' => $page,
                'Size' => $size
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'message' => 'خطا در ارتباط با سرور: ' . $response->status()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'استثنا در ارتباط: ' . $e->getMessage()];
        }
    }


    public function getAllPanelPayments(): array
    {
        $all = [];
        $page = 1;
        $pageSize = 100;

        while (true) {
            $res = $this->getPaymentList($page, $pageSize);
            if (!isset($res['success']) || !$res['success']) {
                break;
            }

            $items = $res['result']['item1'] ?? [];
            if (empty($items)) {
                break;
            }

            $all = array_merge($all, $items);
            $totalRecords = (int) ($res['result']['item2'] ?? 0);
            if ($totalRecords > 0 && count($all) >= $totalRecords) {
                break;
            }

            if (count($items) < $pageSize) {
                break;
            }

            $page++;
            if ($page > 500) break;
        }

        return $all;
    }

    public function increaseUserCredit(string $userName, int $amount): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            return ['success' => false, 'message' => 'تنظیمات API ناقص است.'];
        }

        try {
            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/IncreaseUserCredit', [
                'UserName' => $userName,
                'Amount' => $amount
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'message' => 'خطا در ارتباط با سرور: ' . $response->status()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'استثنا در ارتباط: ' . $e->getMessage()];
        }
    }


    public function getCurrentCredit(): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            return ['success' => false, 'message' => 'تنظیمات API ناقص است.'];
        }

        try {
            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
            ])->withBody('{}', 'application/json')->post($this->baseUrl . '/api/getcurrentcredit');

            if ($response->successful() && $response->json()) {
                return $response->json();
            }

            return ['success' => false, 'message' => 'خطا در دریافت موجودی از سرور: ' . $response->status()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'استثنا در ارتباط: ' . $e->getMessage()];
        }
    }
}
