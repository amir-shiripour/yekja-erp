<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class TenantStorage
{
    /**
     * Resolve the scoped path for the given relative path and tenant.
     */
    public static function path(string $path = '', ?int $tenantId = null): string
    {
        $manager = app(TenantManager::class);
        $id = $tenantId ?? $manager->getTenantId();
        $cleanPath = ltrim($path, '/');

        if ($id !== null) {
            return $cleanPath !== '' ? "tenants/{$id}/{$cleanPath}" : "tenants/{$id}";
        }

        return $cleanPath;
    }

    /**
     * Get the underlying filesystem disk instance.
     */
    public static function disk(?string $disk = null): Filesystem
    {
        return Storage::disk($disk ?? config('filesystems.default', 'public'));
    }

    /**
     * Store content in tenant-isolated storage.
     */
    public static function put(string $path, mixed $contents, ?string $disk = null, ?int $tenantId = null): bool
    {
        $scoped = static::path($path, $tenantId);
        return static::disk($disk)->put($scoped, $contents);
    }

    /**
     * Retrieve content from tenant-isolated storage.
     */
    public static function get(string $path, ?string $disk = null, ?int $tenantId = null): ?string
    {
        $scoped = static::path($path, $tenantId);
        return static::disk($disk)->get($scoped);
    }

    /**
     * Check if a file exists in tenant-isolated storage.
     */
    public static function exists(string $path, ?string $disk = null, ?int $tenantId = null): bool
    {
        $scoped = static::path($path, $tenantId);
        return static::disk($disk)->exists($scoped);
    }

    /**
     * Delete one or more files in tenant-isolated storage.
     */
    public static function delete(string|array $paths, ?string $disk = null, ?int $tenantId = null): bool
    {
        if (is_array($paths)) {
            $scoped = array_map(fn($p) => static::path($p, $tenantId), $paths);
        } else {
            $scoped = static::path($paths, $tenantId);
        }

        return static::disk($disk)->delete($scoped);
    }

    /**
     * Get public URL for a file in tenant-isolated storage.
     */
    public static function url(string $path, ?string $disk = null, ?int $tenantId = null): string
    {
        $scoped = static::path($path, $tenantId);
        return static::disk($disk)->url($scoped);
    }

    /**
     * List all direct files inside a tenant directory.
     */
    public static function files(string $directory = '', ?string $disk = null, ?int $tenantId = null): array
    {
        $scoped = static::path($directory, $tenantId);
        return static::disk($disk)->files($scoped);
    }

    /**
     * List all files recursively inside a tenant directory.
     */
    public static function allFiles(string $directory = '', ?string $disk = null, ?int $tenantId = null): array
    {
        $scoped = static::path($directory, $tenantId);
        return static::disk($disk)->allFiles($scoped);
    }

    /**
     * Delete an entire tenant's directory and all its files.
     */
    public static function deleteTenantDirectory(?int $tenantId = null, ?string $disk = null): bool
    {
        $manager = app(TenantManager::class);
        $id = $tenantId ?? $manager->getTenantId();

        if ($id === null) {
            return false;
        }

        return static::disk($disk)->deleteDirectory("tenants/{$id}");
    }
}
