<?php

namespace App\Traits;

use App\Services\ImageOptimizerService;
use App\Tenancy\TenantManager;

trait FileUploadTrait
{
    /**
     * Upload and optionally optimize a file.
     * Automatically scopes uploads into tenant folder if running in tenant context.
     *
     * @param mixed $file
     * @param string $directory
     * @param string $disk
     * @return string
     */
    protected function uploadFile($file, string $directory, string $disk = 'public'): string
    {
        if (app()->bound(TenantManager::class)) {
            $tenantManager = app(TenantManager::class);
            if ($tenantManager->hasTenant() && !str_starts_with(ltrim($directory, '/'), 'tenants/')) {
                $directory = $tenantManager->getStoragePath($directory);
            }
        }

        $optimizer = app(ImageOptimizerService::class);
        return $optimizer->uploadAndOptimize($file, $directory, $disk);
    }
}
