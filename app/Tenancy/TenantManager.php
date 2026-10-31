<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Throwable;

class TenantManager
{

    protected ?Tenant $currentTenant = null;


    protected bool $bypassed = false;


    protected array $reservedSubdomains = [
        'www',
        'admin',
        'api',
        'mail',
        'crm',
        'root',
        'reseller',
        'public',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'two-factor-challenge',
        'user',
        'livewire',
        'sanctum',
        'storage',
        'build',
        'assets',
        'css',
        'js',
        'images',
        'vendor',
        'favicon.ico',
        'robots.txt',
        'ping',
        'debug-attributes',
        'staging',
        'dev',
        'test',
        'external-api',
        'install',
        'app',
        'central',
        'master',
        'system',
        'cp',
        'panel',
        'portal',
        'get-started',
    ];

    public function setTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
        TenantModuleActivator::flushTenantCache();
    }


    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }


    public function getTenantId(): ?int
    {
        return $this->currentTenant?->id;
    }


    public function hasTenant(): bool
    {
        return $this->currentTenant !== null;
    }


    public function isDirectSystem(): bool
    {
        return !$this->hasTenant();
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    public function bypass(callable $callback)
    {
        $previous = $this->bypassed;
        $this->bypassed = true;

        try {
            return $callback();
        } finally {
            $this->bypassed = $previous;
        }
    }


    public function extractSubdomain(string $host): ?string
    {
        $host = Str::before($host, ':');
        $host = strtolower(trim($host));

        if (filter_var($host, FILTER_VALIDATE_IP) || $host === 'localhost') {
            return null;
        }

        $centralDomains = $this->getCentralDomains();

        foreach ($centralDomains as $centralDomain) {
            $centralDomain = strtolower(trim($centralDomain));

            if ($host === $centralDomain) {
                return null;
            }

            $suffix = '.' . $centralDomain;
            if (str_ends_with($host, $suffix)) {
                $sub = substr($host, 0, -strlen($suffix));
                $parts = explode('.', $sub);
                $cleanSub = trim(end($parts));

                if (!empty($cleanSub) && !$this->isReservedSubdomain($cleanSub)) {
                    return $cleanSub;
                }
            }
        }

        return null;
    }



    public function getCentralDomains(): array
    {
        $domains = [];

        $customCentral = env('CENTRAL_DOMAINS');
        if (!empty($customCentral)) {
            $parts = explode(',', $customCentral);
            foreach ($parts as $p) {
                $trimmed = strtolower(trim($p));
                if (!empty($trimmed) && !filter_var($trimmed, FILTER_VALIDATE_IP) && $trimmed !== 'localhost') {
                    $domains[] = $trimmed;
                }
            }
        }

        $appUrl = config('app.url');
        if (!empty($appUrl)) {
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            if (!empty($parsedHost) && !filter_var($parsedHost, FILTER_VALIDATE_IP) && $parsedHost !== 'localhost') {
                $domains[] = strtolower(trim($parsedHost));
            }
        }

        try {
            if (app()->bound('request') && request()) {
                $currentHost = request()->getHost();
                if (!empty($currentHost) && !filter_var($currentHost, FILTER_VALIDATE_IP) && $currentHost !== 'localhost') {
                    $domains[] = strtolower(trim($currentHost));
                }
            }
        } catch (Throwable $e) {
        }

        $filtered = array_values(array_unique(array_filter($domains)));
        if (empty($filtered)) {
            return ['crm-laravel.test'];
        }

        return $filtered;
    }


    public function isReservedSubdomain(string $subdomain): bool
    {
        return in_array(strtolower(trim($subdomain)), $this->reservedSubdomains, true);
    }


    public function getReservedSubdomains(): array
    {
        return $this->reservedSubdomains;
    }

    public function getTenantUrl(Tenant $tenant, string $path = ''): string
    {
        $request = request();
        $protocol = ($request && $request->isSecure()) ? 'https://' : 'http://';
        $cleanPath = ltrim($path, '/');
        $subPath = $cleanPath ? '/' . $cleanPath : '';

        if (!empty($tenant->custom_domain)) {
            return $protocol . $tenant->custom_domain . ($cleanPath ? '/' . $cleanPath : '');
        }

        if ($request && $request->getHttpHost()) {
            $host = $request->getHttpHost();
        } else {
            $appUrl = config('app.url', '');
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            $parsedPort = parse_url($appUrl, PHP_URL_PORT);
            if ($parsedHost) {
                $host = $parsedPort ? "{$parsedHost}:{$parsedPort}" : $parsedHost;
            } else {
                $host = $this->getCentralDomains()[0] ?? 'crm-laravel.test';
            }
        }

        return $protocol . $host . '/' . $tenant->subdomain . $subPath;
    }

    public function getTenantDisplayUrl(Tenant $tenant): string
    {
        if (!empty($tenant->custom_domain)) {
            return $tenant->custom_domain;
        }

        $request = request();
        if ($request && $request->getHttpHost()) {
            $host = $request->getHttpHost();
        } else {
            $appUrl = config('app.url', '');
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            $parsedPort = parse_url($appUrl, PHP_URL_PORT);
            if ($parsedHost) {
                $host = $parsedPort ? "{$parsedHost}:{$parsedPort}" : $parsedHost;
            } else {
                $host = $this->getCentralDomains()[0] ?? 'crm-laravel.test';
            }
        }

        return $host . '/' . $tenant->subdomain;
    }

    public function extractTenantFromPath(string $path): ?string
    {
        $cleanPath = trim($path, '/');
        if (empty($cleanPath)) {
            return null;
        }

        $segments = explode('/', $cleanPath);
        $firstSegment = strtolower(trim($segments[0]));

        if (empty($firstSegment) || $this->isReservedSubdomain($firstSegment)) {
            return null;
        }

        return $firstSegment;
    }

    /**
     * Get a tenant-isolated storage path.
     */
    public function getStoragePath(string $path = ''): string
    {
        $tenantId = $this->getTenantId() ?? 'direct';
        $cleanPath = ltrim($path, '/');
        return "tenants/{$tenantId}/" . $cleanPath;
    }

    /**
     * Check if a module is active for the current tenant or system.
     */
    public function isModuleActive(string $moduleSlug): bool
    {
        $normalizedSlug = strtolower(trim($moduleSlug));

        if ($normalizedSlug === 'core') {
            return true;
        }

        if (class_exists(Module::class)) {
            $mod = Module::find($moduleSlug);
            return $mod ? $mod->isEnabled() : false;
        }

        $tenant = $this->getTenant() ?? (auth()->check() ? auth()->user()->tenant : null);
        if ($tenant) {
            return $tenant->hasModule($moduleSlug);
        }

        return true;
    }
}

