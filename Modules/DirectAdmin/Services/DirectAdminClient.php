<?php

namespace Modules\DirectAdmin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DirectAdminClient
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected bool $ssl;
    protected int $timeout;

    public function __construct(string $host, string $username, string $password, int $port = 2222, bool $ssl = true, int $timeout = 30)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->ssl = $ssl;
        $this->timeout = $timeout;
    }

    /**
     * Get target full URL
     */
    protected function getUrl(string $endpoint): string
    {
        $protocol = $this->ssl ? 'https' : 'http';
        $host = trim($this->host, '/');
        // Remove protocol if user included it in host
        $host = preg_replace('/^https?:\/\//i', '', $host);
        return "{$protocol}://{$host}:{$this->port}/" . ltrim($endpoint, '/');
    }

    /**
     * Get common HTTP client instance with auth
     */
    protected function client()
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->timeout($this->timeout);
    }


    /**
     * Test connection and credentials
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);
        try {
            $response = $this->client()->get($this->getUrl('api/session/state'));
            $latency = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'latency' => $latency,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'latency' => $latency,
                'error' => 'HTTP Status: ' . $response->status() . ' - ' . $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'latency' => round((microtime(true) - $startTime) * 1000),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system and DA state
     */
    public function getServerState(): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl('api/session/state'));
            return $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getServerState failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get reseller config for current session
     */
    public function getResellerConfig(): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl('api/session/reseller-config'));
            return $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getResellerConfig failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get reseller and sub-users aggregate resource usage
     */
    public function getResellerUsage(): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl("api/resellers/{$this->username}/usage"));
            return $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getResellerUsage failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get accessible user list from REST search endpoint
     */
    public function getUsersList(string $query = '', int $limit = 100): array
    {
        try {
            $response = $this->client()->get($this->getUrl('api/search/users'), [
                'q' => $query,
                'limit' => $limit,
            ]);
            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getUsersList failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single user configuration via CMD_API_SHOW_USER_CONFIG for legacy keys
     */
    public function getUserCmdConfig(string $username): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl('CMD_API_SHOW_USER_CONFIG'), ['user' => $username]);
            if ($response->successful()) {
                parse_str($response->body(), $result);
                return $result;
            }
            return null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getUserCmdConfig failed for {$username}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get single user configuration
     */
    public function getUserConfig(string $username): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl("api/users/{$username}/config"));
            return $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getUserConfig failed for {$username}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get single user resource usage
     */
    public function getUserUsage(string $username): ?array
    {
        try {
            $response = $this->client()->get($this->getUrl("api/users/{$username}/usage"));
            return $response->successful() ? $response->json() : null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getUserUsage failed for {$username}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user login history
     */
    public function getUserLoginHistory(string $username): array
    {
        try {
            $response = $this->client()->get($this->getUrl("api/users/{$username}/login-history"));
            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getUserLoginHistory failed for {$username}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get WordPress installations
     */
    public function getWordPressLocations(?string $domain = null): array
    {
        try {
            $params = [];
            if ($domain) {
                $params['domain'] = $domain;
            }
            $response = $this->client()->get($this->getUrl('api/wordpress/locations'), $params);
            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getWordPressLocations failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Install WordPress Quick
     */
    public function installWordPressQuick(array $data): array
    {
        try {
            // First try modern REST API
            $response = $this->client()
                ->asJson()
                ->post($this->getUrl('api/wordpress/install-quick'), $data);

            $resBody = $response->body();
            Log::info("[DirectAdminClient] REST API install-quick response status=" . $response->status() . " body=" . $resBody);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json['error']) && $json['error']) {
                    return ['success' => false, 'error' => $json['message'] ?? $json['error'] ?? 'خطا در نصب وردپرس'];
                }
                return ['success' => true, 'data' => $json];
            }

            // Fallback: DirectAdmin CMSInstaller Plugin Raw API
            $domain = str_replace(['domains/', '/public_html'], '', $data['filePath'] ?? '');
            if (str_contains($domain, '/')) {
                $domain = explode('/', $domain)[0];
            }

            $pluginPayload = [
                'action' => 'install',
                'script' => 'wordpress',
                'domain' => $domain,
                'title' => $data['title'] ?? 'WordPress Site',
                'admin_name' => $data['adminName'] ?? 'admin',
                'admin_pass' => $data['adminPass'] ?? '',
                'admin_email' => $data['adminEmail'] ?? '',
                'user' => explode('|', $this->username)[1] ?? $this->username,
            ];

            $pluginResp = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_PLUGINS/cmsinstaller/index.raw'), $pluginPayload);

            $body = $pluginResp->body();
            Log::info("[DirectAdminClient] CMSInstaller Plugin response: status=" . $pluginResp->status() . " body=" . substr($body, 0, 500));

            if ($pluginResp->successful()) {
                // If DirectAdmin returns full HTML Vue app shell, the API endpoint is interactive UI only, not automated raw install
                if (str_starts_with(trim($body), '<!DOCTYPE html>') || str_contains($body, '<html')) {
                    Log::warning("[DirectAdminClient] CMSInstaller returned full HTML page instead of API response");
                    return ['success' => false, 'error' => 'پلاگین CMSInstaller سرور شما از نصب خودکار پشت‌صحنه پشتیبانی نمی‌کند (نیاز به رابط کاربری وب دارد).'];
                }

                parse_str($body, $parsed);
                if (isset($parsed['error']) && $parsed['error'] != '0') {
                    return ['success' => false, 'error' => $parsed['details'] ?? 'خطا در نصب وردپرس توسط پلاگین CMSInstaller سرور.'];
                }
                if (str_contains($body, 'Access Denied') || str_contains($body, 'login_form') || str_contains($body, 'Unauthorized')) {
                    return ['success' => false, 'error' => 'سطح دسترسی لازم برای اجرای پلاگین CMSInstaller در سرور وجود ندارد.'];
                }
                return ['success' => true, 'data' => $body];
            }

            // Extract error message from REST or server response
            $errDetail = $resBody;
            $jsonErr = json_decode($resBody, true);
            if (is_array($jsonErr) && isset($jsonErr['message'])) {
                $errDetail = $jsonErr['message'];
            }

            return ['success' => false, 'error' => $errDetail];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] installWordPressQuick Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete WordPress installation
     */
    public function deleteWordPress(string $locationId): bool
    {
        try {
            $response = $this->client()->delete($this->getUrl("api/wordpress/locations/{$locationId}"));
            return $response->successful();
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] deleteWordPress failed for location {$locationId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate WordPress SSO Login link
     */
    public function getWordPressSso(string $locationId, int $userId): ?string
    {
        try {
            $response = $this->client()->post($this->getUrl("api/wordpress/locations/{$locationId}/users/{$userId}/sso-login"));
            if ($response->successful()) {
                return $response->json()['url'] ?? null;
            }
            return null;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getWordPressSso failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * List WordPress Users for a location
     */
    public function getWordPressUsers(string $locationId): array
    {
        try {
            $response = $this->client()->get($this->getUrl("api/wordpress/locations/{$locationId}/users"));
            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getWordPressUsers failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Change WordPress User Password
     */
    public function changeWordPressPassword(string $locationId, int $userId, string $password): bool
    {
        try {
            $response = $this->client()->post($this->getUrl("api/wordpress/locations/{$locationId}/users/{$userId}/change-password"), [
                'password' => $password
            ]);
            return $response->successful();
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] changeWordPressPassword failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create hosted user account
     */
    public function createUser(array $data): array
    {
        // $data keys: username, email, passwd, domain, package
        $payload = [
            'action' => 'create',
            'add' => 'Submit',
            'username' => $data['username'],
            'email' => $data['email'],
            'passwd' => $data['password'],
            'domain' => $data['domain'],
            'package' => $data['package'] ?? 'Default',
            'ip' => $data['ip'] ?? 'shared',
            'notify' => 'no',
        ];

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_ACCOUNT_USER'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                if (isset($result['error']) && $result['error'] != '0') {
                    return [
                        'success' => false,
                        'error' => $result['details'] ?? 'Unknown DirectAdmin error response.',
                    ];
                }
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => 'HTTP Connection failed with status ' . $response->status(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete hosted user account
     */
    public function deleteUser(string $username): bool
    {
        $payload = [
            'action' => 'delete',
            'select0' => $username,
        ];

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_ACCOUNT_USER'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                return !isset($result['error']) || $result['error'] == '0';
            }
            return false;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] deleteUser failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Suspend user account
     */
    public function suspendUser(string $username): bool
    {
        $payload = [
            'action' => 'suspend',
            'suspend' => 'Suspend',
            'select0' => $username,
        ];

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_SELECT_USERS'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                if (isset($result['error']) && $result['error'] != '0') {
                    Log::error("[DirectAdminClient] suspendUser error: " . ($result['details'] ?? $response->body()));
                    return false;
                }
                return true;
            }
            return false;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] suspendUser failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsuspend user account
     */
    public function unsuspendUser(string $username): bool
    {
        $payload = [
            'action' => 'unsuspend',
            'unsuspend' => 'Unsuspend',
            'select0' => $username,
        ];

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_SELECT_USERS'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                if (isset($result['error']) && $result['error'] != '0') {
                    Log::error("[DirectAdminClient] unsuspendUser error: " . ($result['details'] ?? $response->body()));
                    return false;
                }
                return true;
            }
            return false;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] unsuspendUser failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available IP addresses for account creation
     */
    public function getIpAddresses(): array
    {
        try {
            $response = $this->client()->get($this->getUrl('CMD_API_SHOW_RESELLER_IPS'));
            if ($response->successful()) {
                parse_str($response->body(), $result);
                return array_keys($result['list'] ?? []);
            }
            return [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getIpAddresses failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available user packages for this reseller account
     */
    public function getPackages(): array
    {
        try {
            $response = $this->client()->get($this->getUrl('CMD_API_PACKAGES_USER'));
            if ($response->successful()) {
                parse_str($response->body(), $result);
                return $result['list'] ?? [];
            }
            return [];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] getPackages failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Modify user account settings / package in DirectAdmin
     */
    public function modifyUser(string $username, array $data): array
    {
        // If changing package, DirectAdmin CMD_API_MODIFY_USER requires action=package and package=NAME
        if (isset($data['package'])) {
            $payload = [
                'action' => 'package',
                'user' => $username,
                'package' => $data['package'],
            ];
        } else {
            // Manual custom settings modification
            $payload = array_merge([
                'action' => 'customize',
                'user' => $username,
            ], $data);
        }

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_MODIFY_USER'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                if (isset($result['error']) && $result['error'] != '0') {
                    return [
                        'success' => false,
                        'error' => $result['details'] ?? 'خطا در ویرایش کاربر در دایرکت ادمین.',
                    ];
                }
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'ارتباط با سرور برقرار نشد (کد ' . $response->status() . ')'];
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] modifyUser failed for {$username}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Change user Unix/System password
     */
    public function changePassword(string $username, string $password): bool
    {
        $payload = [
            'username' => $username,
            'passwd' => $password,
            'passwd2' => $password,
        ];

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->getUrl('CMD_API_USER_PASSWD'), $payload);

            if ($response->successful()) {
                parse_str($response->body(), $result);
                return !isset($result['error']) || $result['error'] == '0';
            }
            return false;
        } catch (Throwable $e) {
            Log::error("[DirectAdminClient] changePassword failed for user {$username}: " . $e->getMessage());
            return false;
        }
    }
}
