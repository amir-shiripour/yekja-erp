<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Support\Str;

class DomainVerificationService
{
    /**
     * Clean and normalize a domain or hostname.
     */
    public function sanitizeDomain(?string $domain): ?string
    {
        if (empty($domain)) {
            return null;
        }

        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = Str::before($domain, '/');
        $domain = Str::before($domain, ':');
        $domain = strtolower(trim($domain));

        return $domain ?: null;
    }

    /**
     * Check if the domain syntax is a valid domain or subdomain name.
     */
    public function isValidDomain(string $domain): bool
    {
        if (strlen($domain) < 4 || strlen($domain) > 100) {
            return false;
        }

        // Must contain at least one dot and valid domain characters
        if (!preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $domain)) {
            return false;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the domain conflicts with reserved or system domains.
     */
    public function isReservedDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        if ($domain === 'localhost') {
            return true;
        }

        $centralDomains = app(TenantManager::class)->getCentralDomains();
        foreach ($centralDomains as $central) {
            if ($domain === strtolower(trim($central))) {
                return true;
            }
        }

        $reserved = app(TenantManager::class)->getReservedSubdomains();
        $parts = explode('.', $domain);
        if (count($parts) === 2 && in_array($parts[0], $reserved, true)) {
            return true;
        }

        return false;
    }

    /**
     * Check uniqueness against existing tenants.
     */
    public function isDomainAvailable(string $domain, ?int $ignoreTenantId = null): bool
    {
        $query = Tenant::where('custom_domain', $domain);
        if ($ignoreTenantId) {
            $query->where('id', '!=', $ignoreTenantId);
        }

        return !$query->exists();
    }

    /**
     * Determine expected server IP for A-Record matching.
     * Returns null if running in production and no public server IP could be determined,
     * avoiding false positives/negatives against localhost.
     */
    public function getTargetServerIp(): ?string
    {
        // 1. Explicitly configured server IP via config
        $customIp = config('resellerhub.server_ip');
        if (!empty($customIp) && filter_var(trim($customIp), FILTER_VALIDATE_IP)) {
            return trim($customIp);
        }

        // 2. Web server IP from SERVER_ADDR if it is a valid public IP
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ip = trim($_SERVER['SERVER_ADDR']);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        // 3. Dynamically resolve primary central domain host
        $centralDomains = app(TenantManager::class)->getCentralDomains();
        $primaryHost = !empty($centralDomains[0]) ? $centralDomains[0] : parse_url(config('app.url'), PHP_URL_HOST);
        if (empty($primaryHost) && request()) {
            $primaryHost = request()->getHost();
        }

        if (!empty($primaryHost) && !in_array($primaryHost, ['localhost', '127.0.0.1'], true)) {
            $resolved = @gethostbyname($primaryHost);
            if (!empty($resolved) && $resolved !== $primaryHost && filter_var($resolved, FILTER_VALIDATE_IP)) {
                return $resolved;
            }
        }

        // 4. Fallback only in local or testing environments
        if (app()->environment('local', 'testing')) {
            return '127.0.0.1';
        }

        return null;
    }

    /**
     * Determine expected target host for CNAME record matching.
     */
    public function getTargetCname(): string
    {
        $customTarget = config('resellerhub.cname_target');
        if (!empty($customTarget)) {
            return trim($customTarget);
        }

        $centralDomains = app(TenantManager::class)->getCentralDomains();
        if (!empty($centralDomains[0])) {
            return $centralDomains[0];
        }

        $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);
        if (!empty($appUrlHost)) {
            return $appUrlHost;
        }

        return request() ? request()->getHost() : 'localhost';
    }

    /**
     * Perform live DNS verification for the given domain.
     */
    public function verifyDns(string $domain): array
    {
        $targetIp = $this->getTargetServerIp();
        $targetCname = $this->getTargetCname();

        $resolvedIps = [];
        $resolvedCnames = [];

        // Check A records
        if (function_exists('dns_get_record')) {
            $aRecords = @dns_get_record($domain, DNS_A);
            if (is_array($aRecords)) {
                foreach ($aRecords as $rec) {
                    if (!empty($rec['ip'])) {
                        $resolvedIps[] = $rec['ip'];
                    }
                }
            }

            $cnameRecords = @dns_get_record($domain, DNS_CNAME);
            if (is_array($cnameRecords)) {
                foreach ($cnameRecords as $rec) {
                    if (!empty($rec['target'])) {
                        $resolvedCnames[] = strtolower(rtrim($rec['target'], '.'));
                    }
                }
            }
        }

        // Fallback with gethostbynamel
        if (empty($resolvedIps) && function_exists('gethostbynamel')) {
            $ips = @gethostbynamel($domain);
            if (is_array($ips)) {
                $resolvedIps = array_merge($resolvedIps, $ips);
            }
        }

        $resolvedIps = array_values(array_unique($resolvedIps));
        $resolvedCnames = array_values(array_unique($resolvedCnames));

        $isIpMatched = !empty($targetIp) && in_array($targetIp, $resolvedIps, true);
        $isCnameMatched = false;
        foreach ($resolvedCnames as $cname) {
            if ($cname === strtolower($targetCname) || str_ends_with($cname, '.' . strtolower($targetCname))) {
                $isCnameMatched = true;
                break;
            }
        }

        // Determine status
        if ($isIpMatched || $isCnameMatched) {
            $status = 'verified';
            $statusLabel = 'متصل و تایید شده';
            $message = 'دامنه اختصاصی با موفقیت به سامانه متصل شده است و رکوردهای DNS آن معتبر هستند.';
        } elseif (!empty($resolvedIps) || !empty($resolvedCnames)) {
            $status = 'pending';
            $statusLabel = 'در انتظار تنظیم یا انتشار DNS';
            $message = 'دامنه ثبت شده اما هنوز به آدرس میزبان سامانه متصل نشده است. اعمال تغییرات DNS معمولاً بین چند دقیقه تا چند ساعت زمان می‌برد.';
        } else {
            $status = 'unresolved';
            $statusLabel = 'رکوردی یافت نشد';
            $message = 'هیچ رکورد DNS فعالی برای این دامنه یافت نشد. لطفاً رکوردهای دامنه را مطابق راهنما تنظیم فرمایید.';
        }

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'is_connected' => ($status === 'verified'),
            'message' => $message,
            'target_cname' => $targetCname,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Complete check: sanitization, validation, availability, and DNS check.
     */
    public function fullCheck(?string $rawDomain, ?int $ignoreTenantId = null): array
    {
        $domain = $this->sanitizeDomain($rawDomain);

        if (empty($domain)) {
            return [
                'valid' => false,
                'available' => false,
                'domain' => null,
                'message' => 'نام دامنه نمی‌تواند خالی باشد.',
            ];
        }

        if (!$this->isValidDomain($domain)) {
            return [
                'valid' => false,
                'available' => false,
                'domain' => $domain,
                'message' => 'فرمت دامنه نامعتبر است (مثال صحیح: crm.clinic.ir یا myclinic.com). پروتکل http:// نباید وارد شود.',
            ];
        }

        if ($this->isReservedDomain($domain)) {
            return [
                'valid' => false,
                'available' => false,
                'domain' => $domain,
                'message' => 'این دامنه با دامنه‌های مرکزی سیستم تداخل دارد و قابل انتساب به عنوان دامنه اختصاصی نیست.',
            ];
        }

        $available = $this->isDomainAvailable($domain, $ignoreTenantId);
        if (!$available) {
            return [
                'valid' => true,
                'available' => false,
                'domain' => $domain,
                'message' => 'این دامنه اختصاصی قبلاً برای مشتری یا CRM دیگری در سیستم ثبت شده است.',
            ];
        }

        $dns = $this->verifyDns($domain);

        return [
            'valid' => true,
            'available' => true,
            'domain' => $domain,
            'dns' => $dns,
            'message' => $dns['message'],
        ];
    }
}
