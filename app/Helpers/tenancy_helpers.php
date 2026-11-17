<?php

declare(strict_types=1);

use App\Tenancy\TenantCache;
use App\Tenancy\TenantStorage;

if (!function_exists('tenant_storage')) {
    /**
     * Helper to interact with tenant-scoped storage.
     */
    function tenant_storage(?string $path = null, ?int $tenantId = null): string|TenantStorage
    {
        if ($path !== null) {
            return TenantStorage::path($path, $tenantId);
        }

        return app(TenantStorage::class);
    }
}

if (!function_exists('tenant_cache')) {
    /**
     * Helper to interact with tenant-scoped cache.
     */
    function tenant_cache(?string $key = null, mixed $default = null, ?int $tenantId = null): mixed
    {
        if ($key === null) {
            return app(TenantCache::class);
        }

        return TenantCache::get($key, $default, $tenantId);
    }
}
