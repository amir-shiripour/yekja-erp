<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Module;

class TenantModuleActivator extends FileActivator
{
    /**
     * In-memory cache of tenant active modules for the current request.
     *
     * @var array<int, array<string>>
     */
    protected static array $tenantModulesCache = [];

    /**
     * Core system modules that are always active regardless of tenant subscription.
     *
     * @var array<string>
     */
    protected array $systemModules = [
        'core',
        'settings',
        'usermanagement',
        'user-management',
    ];

    /**
     * Determine whether the given status matches the module status.
     */
    public function hasStatus(Module $module, bool $status): bool
    {
        // 1. Check if globally enabled in modules_statuses.json
        $isGloballyEnabled = parent::hasStatus($module, true);

        // If module is disabled globally, it is never enabled for anyone
        if (!$isGloballyEnabled) {
            return $status === false;
        }

        $lowerName = strtolower($module->getLowerName() ?: $module->getName());

        // 2. Always allow core system modules
        if (in_array($lowerName, $this->systemModules, true)) {
            return $status === true;
        }

        // 3. Check current tenant context
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->getTenant() ?? (auth()->check() ? auth()->user()->tenant : null);

        if ($tenant) {
            $activeSlugs = $this->getTenantActiveSlugs($tenant);
            $isTenantActive = in_array($lowerName, $activeSlugs, true);

            return $status === $isTenantActive;
        }

        // 4. Central system context (no tenant) -> global status
        return $status === true;
    }

    /**
     * Get active module slugs for tenant with memoization.
     */
    protected function getTenantActiveSlugs(Tenant $tenant): array
    {
        $tenantId = (int) $tenant->id;

        if (!isset(static::$tenantModulesCache[$tenantId])) {
            static::$tenantModulesCache[$tenantId] = $tenant->activeModuleSlugs();
        }

        return static::$tenantModulesCache[$tenantId];
    }

    /**
     * Flush in-memory tenant modules cache.
     */
    public static function flushTenantCache(?int $tenantId = null): void
    {
        if ($tenantId !== null) {
            unset(static::$tenantModulesCache[$tenantId]);
        } else {
            static::$tenantModulesCache = [];
        }
    }
}
