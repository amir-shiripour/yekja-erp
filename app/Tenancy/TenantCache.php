<?php

declare(strict_types=1);

namespace App\Tenancy;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class TenantCache
{
    /**
     * Build the tenant-prefixed cache key.
     */
    public static function prefixKey(string $key, ?int $tenantId = null): string
    {
        $manager = app(TenantManager::class);
        $id = $tenantId ?? $manager->getTenantId();

        if ($id !== null) {
            return "tenant:{$id}:{$key}";
        }

        return "system:{$key}";
    }

    /**
     * Get the cache repository for an optional store name.
     */
    public static function store(?string $store = null): Repository
    {
        return Cache::store($store);
    }

    /**
     * Retrieve an item from the cache by tenant key.
     */
    public static function get(string $key, mixed $default = null, ?int $tenantId = null, ?string $store = null): mixed
    {
        return static::store($store)->get(static::prefixKey($key, $tenantId), $default);
    }

    /**
     * Store an item in the tenant-scoped cache.
     */
    public static function put(string $key, mixed $value, mixed $ttl = null, ?int $tenantId = null, ?string $store = null): bool
    {
        return static::store($store)->put(static::prefixKey($key, $tenantId), $value, $ttl);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $key, mixed $ttl, Closure $callback, ?int $tenantId = null, ?string $store = null): mixed
    {
        return static::store($store)->remember(static::prefixKey($key, $tenantId), $ttl, $callback);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     */
    public static function rememberForever(string $key, Closure $callback, ?int $tenantId = null, ?string $store = null): mixed
    {
        return static::store($store)->rememberForever(static::prefixKey($key, $tenantId), $callback);
    }

    /**
     * Determine if an item exists in the tenant cache.
     */
    public static function has(string $key, ?int $tenantId = null, ?string $store = null): bool
    {
        return static::store($store)->has(static::prefixKey($key, $tenantId));
    }

    /**
     * Remove an item from the tenant cache.
     */
    public static function forget(string $key, ?int $tenantId = null, ?string $store = null): bool
    {
        return static::store($store)->forget(static::prefixKey($key, $tenantId));
    }

    /**
     * Increment the value of an item in the tenant cache.
     */
    public static function increment(string $key, int $value = 1, ?int $tenantId = null, ?string $store = null): int|bool
    {
        return static::store($store)->increment(static::prefixKey($key, $tenantId), $value);
    }

    /**
     * Decrement the value of an item in the tenant cache.
     */
    public static function decrement(string $key, int $value = 1, ?int $tenantId = null, ?string $store = null): int|bool
    {
        return static::store($store)->decrement(static::prefixKey($key, $tenantId), $value);
    }

    /**
     * Get a tagged cache repository prefixed with the tenant tag.
     */
    public static function tags(array|string $names, ?int $tenantId = null): Repository
    {
        $manager = app(TenantManager::class);
        $id = $tenantId ?? $manager->getTenantId();
        $tenantTag = $id !== null ? "tenant_{$id}" : "tenant_system";

        $tags = is_array($names) ? $names : [$names];
        array_unshift($tags, $tenantTag);

        return Cache::tags($tags);
    }

    /**
     * Flush all cache entries tagged for this tenant (if using a cache driver supporting tags).
     */
    public static function flushTenant(?int $tenantId = null): bool
    {
        $manager = app(TenantManager::class);
        $id = $tenantId ?? $manager->getTenantId();

        if ($id === null) {
            return false;
        }

        try {
            Cache::tags(["tenant_{$id}"])->flush();
            return true;
        } catch (\BadMethodCallException) {
            // Driver does not support cache tagging
            return false;
        }
    }
}
