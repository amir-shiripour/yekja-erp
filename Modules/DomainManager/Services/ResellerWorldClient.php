<?php

namespace Modules\DomainManager\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResellerWorldClient
{
    protected $authUserId;
    protected $apiKey;
    protected $baseUrl;

    public function __construct($authUserId, $apiKey)
    {
        $this->authUserId = $authUserId;
        $this->apiKey = preg_replace('/^bearer\s+/i', '', trim($apiKey));
        $this->baseUrl = config('domainmanager.api_base_url', 'https://api.reseller.world/v1.5/multi_registrar/');
    }

    /**
     * Test credentials and connectivity
     */
    public function testConnection()
    {
        $result = $this->request('GET', 'statistics/domain-status');

        if (isset($result['status']) && $result['status'] === 'error') {
            return [
                'success' => false,
                'error' => $result['message'] ?? 'احراز هویت با خطا مواجه شد.'
            ];
        }

        if (isset($result['error'])) {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }

        return [
            'success' => true,
            'data' => $result,
        ];
    }

    /**
     * Get reseller wallet balance (Not natively supported, returning dummy)
     */
    public function getBalance()
    {
        return ['sellingbalance' => 0.0];
    }

    public function searchDomains($pageNo = 1, $noOfRecords = 20)
    {
        return $this->request('POST', 'domains', [
            'page' => $pageNo,
            'total' => $noOfRecords
        ]);
    }

    /**
     * Check domain availability
     */
    public function checkAvailability($domainName, $tlds = ['com', 'net', 'org', 'info', 'ir'])
    {
        $results = [];

        foreach ($tlds as $tld) {
            $domain = "{$domainName}.{$tld}";
            $result = $this->request('POST', "domain/{$domain}/check", []);

            // If there's an error in response, or it states unavailable, set status accordingly
            if (isset($result['status']) && $result['status'] === 'error') {
                $results[$domain] = ['status' => 'unavailable'];
            } elseif (isset($result['error']) || isset($result['message'])) {
                $results[$domain] = ['status' => 'unavailable'];
            } else {
                // If it returns empty array/object or prices, it is available
                $results[$domain] = ['status' => 'available'];
            }
        }

        return $results;
    }

    /**
     * Get domain details
     */
    public function getDomainDetails($domainName)
    {
        return $this->request('GET', "domain/{$domainName}");
    }

    /**
     * Modify Domain Nameservers
     */
    public function modifyNameservers($domainName, array $ns)
    {
        return $this->request('PUT', "domain/{$domainName}/dns", [
            'dnsList' => $ns
        ]);
    }

    /**
     * Enable/Disable WHOIS Privacy Protection (Fallback to success)
     */
    public function modifyPrivacyProtection($domainName, $protect = true)
    {
        return ['status' => 'success'];
    }

    /**
     * Retrieve Domain Transfer/EPP Auth Code
     */
    public function getAuthCode($domainName)
    {
        return $this->request('GET', "domain/{$domainName}/epp");
    }

    /**
     * Register domain
     */
    public function registerDomain($domainName, $years, $holderHandle, array $ns)
    {
        return $this->request('POST', 'domain/register', [
            'domain' => $domainName,
            'period' => (int)$years,
            'confirmPrice' => true,
            'dnsList' => $ns,
            'holder' => $holderHandle,
            'billing' => $holderHandle,
            'admin' => $holderHandle,
            'tech' => $holderHandle
        ]);
    }

    /**
     * Transfer domain
     */
    public function transferDomain($domainName, $authCode, $holderHandle, array $ns)
    {
        return $this->request('POST', 'domain/transfer', [
            'domain' => $domainName,
            'epp' => $authCode,
            'dnsList' => $ns,
            'holder' => $holderHandle,
            'billing' => $holderHandle,
            'admin' => $holderHandle,
            'tech' => $holderHandle
        ]);
    }

    /**
     * Renew domain
     */
    public function renewDomain($domainName, $years)
    {
        return $this->request('PUT', "domain/{$domainName}/renew", [
            'period' => (int)$years,
            'confirmPrice' => true
        ]);
    }

    /**
     * Create contact details for a Customer
     */
    public function createContact($tld, $email, $name, $country, $state, $city, $address, $postCode, $phone, $organization)
    {
        return $this->request('POST', 'contact/create', [
            'tld' => $tld,
            'email' => $email,
            'name' => $name,
            'country' => $country ?: 'US',
            'state' => $state ?: 'N/A',
            'city' => $city ?: 'N/A',
            'address' => $address ?: 'N/A',
            'postCode' => $postCode ?: 'N/A',
            'phone' => $phone ?: '+98.9123456789',
            'organization' => $organization ?: 'N/A'
        ]);
    }

    /**
     * Lock/Unlock domain
     */
    public function toggleLock($domainName, $lock = true)
    {
        $status = $lock ? 'lock' : 'unlock';
        return $this->request('PUT', "domain/{$domainName}/{$status}");
    }

    /**
     * Modify Domain contacts
     */
    public function modifyContacts($domainName, array $contact)
    {
        return $this->request('PUT', "domain/{$domainName}/contacts", [
            'contacts' => [
                [
                    'holder' => $contact['holder'] ?? 'N/A',
                    'name' => $contact['name'] ?? 'N/A',
                    'email' => $contact['email'] ?? 'N/A',
                    'country' => $contact['country'] ?? 'US',
                    'state' => $contact['state'] ?? 'N/A',
                    'city' => $contact['city'] ?? 'N/A',
                    'address' => $contact['address'] ?? 'N/A',
                    'postCode' => $contact['postCode'] ?? 'N/A',
                    'phone' => $contact['phone'] ?? 'N/A',
                    'fax' => $contact['fax'] ?? 'N/A',
                    'organization' => $contact['organization'] ?? 'N/A'
                ]
            ]
        ]);
    }

    /**
     * List domain child hosts
     */
    public function getChildHosts($domainName)
    {
        return $this->request('GET', "domain/{$domainName}/child");
    }

    /**
     * Add child host NS
     */
    public function addChildHost($domainName, $ns, $ip)
    {
        return $this->request('POST', "domain/{$domainName}/child", [
            'ns' => $ns,
            'ip' => $ip
        ]);
    }

    /**
     * Delete child host NS
     */
    public function deleteChildHost($domainName, $ns)
    {
        return $this->request('DELETE', "domain/{$domainName}/child", [
            'ns' => $ns
        ]);
    }

    /**
     * Add DNSSEC key
     */
    public function addDnsSec($domainName, $tag, $alg, $digestType, $digest)
    {
        return $this->request('PUT', "domain/{$domainName}/dns-sec", [
            'dnsSecList' => [
                [
                    'tag' => $tag,
                    'algorithm' => $alg,
                    'digestType' => $digestType,
                    'digest' => $digest
                ]
            ]
        ]);
    }

    /**
     * Delete DNSSEC key
     */
    public function deleteDnsSec($domainName)
    {
        return $this->request('DELETE', "domain/{$domainName}/dns-sec");
    }

    /**
     * Get TLD final prices strictly according to OpenAPI spec (/final/prices)
     */
    public function getPrices()
    {
        return $this->request('GET', 'final/prices');
    }

    /**
     * Get API transactions history
     */
    public function getTransactions($page = 1)
    {
        return $this->request('POST', 'transaction/index', [
            'page' => $page,
            'total' => 20
        ]);
    }

    /**
     * Core request helper
     */
    protected function request($method, $path, array $params = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        try {
            $request = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]);

            if (strtoupper($method) === 'GET') {
                $response = $request->get($url, $params);
            } elseif (strtoupper($method) === 'PUT') {
                $response = $request->put($url, $params);
            } elseif (strtoupper($method) === 'DELETE') {
                $response = $request->delete($url, $params);
            } else {
                $response = $request->post($url, $params);
            }

            if ($response->failed()) {
                $body = $response->body();
                Log::error("[ResellerWorldClient] HTTP Error " . $response->status() . " Body: " . $body);
                $json = json_decode($body, true);


                $msg = null;
                if (is_array($json)) {
                    $msg = $json['message'] ?? $json['error'] ?? $json['msg'] ?? null;
                    if (is_array($msg)) {
                        $msg = $msg['message'] ?? implode(', ', array_filter($msg));
                    }
                }

                if (!$msg) {
                    $msg = 'خطای سرور کارگزار (کد ' . $response->status() . ')';
                }

                return [
                    'status' => 'error',
                    'message' => 'اتصال با کارگزار برقرار نشد: ' . $msg
                ];
            }

            return $response->json();
        } catch (Throwable $e) {
            Log::error("[ResellerWorldClient] API Request failed to {$path}: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'خطای سیستمی در ارتباط با کارگزار: ' . $e->getMessage()
            ];
        }
    }
}
