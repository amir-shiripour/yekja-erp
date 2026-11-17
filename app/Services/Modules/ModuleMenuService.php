<?php

namespace App\Services\Modules;

use App\Models\Module;
use App\Tenancy\TenantManager;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;

class ModuleMenuService
{
    /**
     * Get all menu items for a given user.
     */
    public function getAllForUser($user): array
    {
        $items = [];
        $moduleGroups = [];
        $settingsItems = [];

        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->hasTenant() ? $tenantManager->getTenant() : null;
        if (!$tenant && app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
        }
        if (!$tenant && view()->shared('currentTenant')) {
            $tenant = view()->shared('currentTenant');
        }
        if (!$tenant && $user) {
            $tenant = $user->tenant ?? ($user->tenant_id ? \App\Models\Tenant::find($user->tenant_id) : null);
        }
        if (!$tenant && auth()->check()) {
            $tenant = auth()->user()->tenant ?? (auth()->user()->tenant_id ? \App\Models\Tenant::find(auth()->user()->tenant_id) : null);
        }

        // Core menu (Central platform administration - skip inside tenant workspace or user dashboard)
        $isUserWorkspace = request()->is('user*') || request()->is('*/user*') || $tenant !== null;
        $coreMenuPath = resource_path('menu.php');
        if (!$isUserWorkspace && file_exists($coreMenuPath)) {
            try {
                $coreMenu = include $coreMenuPath;
                if (is_array($coreMenu)) {
                    $coreItems = [];
                    $coreSettings = [];

                    foreach ($coreMenu as $m) {
                        if (!empty($m['permission'])) {
                            if (!$user->can($m['permission'])) {
                                continue;
                            }
                        }

                        if (isset($m['show'])) {
                            $show = is_callable($m['show']) ? $m['show']() : (bool)$m['show'];
                            if (!$show) {
                                continue;
                            }
                        }

                        $m['module'] = 'core';
                        $m['module_name'] = 'سیستم';

                        $isSettings = $this->isSettingsItem($m);

                        if ($isSettings) {
                            $coreSettings[] = $m;
                        } else {
                            $coreItems[] = $m;
                        }
                    }

                    if (count($coreItems) > 1) {
                        $moduleGroups[] = [
                            'module' => 'admin',
                            'module_name' => __('menu.system_management') ?: 'مدیریت سیستم',
                            'items' => $coreItems,
                        ];
                    } elseif (count($coreItems) === 1) {
                        $items[] = $coreItems[0];
                    }

                    $settingsItems = array_merge($settingsItems, $coreSettings);
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to load core menu: " . $e->getMessage());
            }
        }

        // User management menu item for tenant workspace
        if ($user->can('users.view')) {
            $items[] = [
                'title' => 'مدیریت کاربران',
                'route' => 'user.users.index',
                'active_routes' => ['user.users.*'],
                'permission' => 'users.view',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
                'group' => null,
                'position' => 12,
            ];
            $items[] = [
                'title' => 'نقش‌ها و دسترسی‌ها',
                'route' => 'user.roles.index',
                'active_routes' => ['user.roles.*'],
                'permission' => 'users.view',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>',
                'group' => null,
                'position' => 13,
            ];
        }

        $modules = Module::where('installed', true)->where('active', true)->get();

        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->hasTenant() ? $tenantManager->getTenant() : null;
        if ($tenant) {
            $modules = $modules->filter(function ($mod) use ($tenant) {
                return !empty($mod->is_core)
                    || $tenant->hasModule($mod->slug)
                    || $tenant->hasModule($mod->name)
                    || $tenant->isModulePurchased($mod->slug)
                    || $tenant->isModulePurchased($mod->name);
            });
        }

        foreach ($modules as $module) {
            $moduleName = $module->name;
            $menuPath = base_path("Modules/{$moduleName}/resources/menu.php");

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    if (is_array($menu)) {
                        $moduleItems = [];
                        $moduleSettings = [];

                        foreach ($menu as $m) {
                            if (!empty($m['permission'])) {
                                if (!$user->can($m['permission'])) {
                                    continue;
                                }
                            }

                            if (isset($m['show'])) {
                                $show = is_callable($m['show']) ? $m['show']() : (bool)$m['show'];
                                if (!$show) {
                                    continue;
                                }
                            }

                            $m['module'] = $module->slug;
                            $m['module_name'] = $module->name;
                            if ($tenant && empty($module->is_core) && !$tenant->hasModule($module->slug) && !$tenant->hasModule($module->name)) {
                                $m['is_module_locked'] = true;
                            }

                            $isSettings = $this->isSettingsItem($m);

                            if ($isSettings) {
                                $moduleSettings[] = $m;
                            } else {
                                $moduleItems[] = $m;
                            }
                        }

                        if (count($moduleItems) > 1) {
                            $resolvedTitle = $this->resolveModuleGroupTitle($module->slug, $module->name);
                            $moduleGroups[] = [
                                'module' => $module->slug,
                                'module_name' => $resolvedTitle,
                                'items' => $moduleItems,
                            ];
                        } elseif (count($moduleItems) === 1) {
                            $items[] = $moduleItems[0];
                        }

                        $settingsItems = array_merge($settingsItems, $moduleSettings);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to load menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

        usort($items, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        foreach ($moduleGroups as &$group) {
            usort($group['items'], function ($a, $b) {
                return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
            });
        }

        usort($settingsItems, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        $result = [
            'items' => $items,
            'groups' => $moduleGroups,
            'settings' => $settingsItems,
        ];

        try {
            return app(MenuCustomizationService::class)->applyOverrides($result, $user);
        } catch (\Throwable $e) {
            \Log::warning("MenuCustomizationService failed: " . $e->getMessage());
            return $result;
        }
    }

    /**
     * Get raw menu without applying customizations.
     */
    public function getRawForUser($user): array
    {
        $items = [];
        $moduleGroups = [];
        $settingsItems = [];

        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->hasTenant() ? $tenantManager->getTenant() : null;
        if (!$tenant && app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
        }
        if (!$tenant && view()->shared('currentTenant')) {
            $tenant = view()->shared('currentTenant');
        }
        if (!$tenant && $user) {
            $tenant = $user->tenant ?? ($user->tenant_id ? \App\Models\Tenant::find($user->tenant_id) : null);
        }
        if (!$tenant && auth()->check()) {
            $tenant = auth()->user()->tenant ?? (auth()->user()->tenant_id ? \App\Models\Tenant::find(auth()->user()->tenant_id) : null);
        }

        $isUserWorkspace = request()->is('user*') || request()->is('*/user*') || $tenant !== null;
        $coreMenuPath = resource_path('menu.php');
        if (!$isUserWorkspace && file_exists($coreMenuPath)) {
            try {
                $coreMenu = include $coreMenuPath;
                if (is_array($coreMenu)) {
                    $coreItems = [];
                    $coreSettings = [];

                    foreach ($coreMenu as $m) {
                        $m['module'] = 'core';
                        $m['module_name'] = 'سیستم';
                        $isSettings = $this->isSettingsItem($m);
                        if ($isSettings) {
                            $coreSettings[] = $m;
                        } else {
                            $coreItems[] = $m;
                        }
                    }

                    if (count($coreItems) > 1) {
                        $moduleGroups[] = [
                            'module' => 'admin',
                            'module_name' => __('menu.system_management') ?: 'مدیریت سیستم',
                            'items' => $coreItems,
                        ];
                    } elseif (count($coreItems) === 1) {
                        $items[] = $coreItems[0];
                    }
                    $settingsItems = array_merge($settingsItems, $coreSettings);
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to load core raw menu: " . $e->getMessage());
            }
        }

        // User management menu item for tenant workspace
        $items[] = [
            'title' => 'مدیریت کاربران',
            'route' => 'user.users.index',
            'active_routes' => ['user.users.*'],
            'permission' => 'users.view',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
            'group' => null,
            'position' => 12,
        ];
        $items[] = [
            'title' => 'نقش‌ها و دسترسی‌ها',
            'route' => 'user.roles.index',
            'active_routes' => ['user.roles.*'],
            'permission' => 'users.view',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>',
            'group' => null,
            'position' => 13,
        ];

        $modules = Module::where('installed', true)->where('active', true)->get();

        $tenantManager = app(TenantManager::class);
        if ($tenantManager->hasTenant()) {
            $tenant = $tenantManager->getTenant();
            $modules = $modules->filter(function ($mod) use ($tenant) {
                return !empty($mod->is_core) || $tenant->hasModule($mod->slug) || $tenant->hasModule($mod->name);
            });
        }

        foreach ($modules as $module) {
            $moduleName = $module->name;
            $menuPath = base_path("Modules/{$moduleName}/resources/menu.php");

            if (file_exists($menuPath)) {
                try {
                    $menu = include $menuPath;
                    if (is_array($menu)) {
                        $moduleItems = [];
                        $moduleSettings = [];

                        foreach ($menu as $m) {
                            $m['module'] = $module->slug;
                            $m['module_name'] = $module->name;
                            $isSettings = $this->isSettingsItem($m);
                            if ($isSettings) {
                                $moduleSettings[] = $m;
                            } else {
                                $moduleItems[] = $m;
                            }
                        }

                        if (count($moduleItems) > 1) {
                            $resolvedTitle = $this->resolveModuleGroupTitle($module->slug, $module->name);
                            $moduleGroups[] = [
                                'module' => $module->slug,
                                'module_name' => $resolvedTitle,
                                'items' => $moduleItems,
                            ];
                        } elseif (count($moduleItems) === 1) {
                            $items[] = $moduleItems[0];
                        }
                        $settingsItems = array_merge($settingsItems, $moduleSettings);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to load raw menu for module {$moduleName}: " . $e->getMessage());
                    continue;
                }
            }
        }

        usort($items, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        foreach ($moduleGroups as &$group) {
            usort($group['items'], function ($a, $b) {
                return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
            });
        }

        usort($settingsItems, function ($a, $b) {
            return ($a['position'] ?? 999) <=> ($b['position'] ?? 999);
        });

        return [
            'items' => $items,
            'groups' => $moduleGroups,
            'settings' => $settingsItems,
        ];
    }

    /**
     * Resolve module group title from module translation files.
     */
    public function resolveModuleGroupTitle(string $moduleSlug, string $moduleName): string
    {
        if ($moduleSlug === 'admin') {
            $trans = __('menu.system_management');
            return ($trans !== 'menu.system_management' && !empty($trans)) ? $trans : 'مدیریت سیستم';
        }
        if ($moduleSlug === 'clients') {
            return 'مدیریت ' . config('clients.labels.plural', 'مشتریان');
        }
        if ($moduleSlug === 'settings') {
            return 'تنظیمات';
        }
        if ($moduleSlug === 'dashboard') {
            return 'پیشخوان';
        }

        // 1. Resolve directly from PermissionCatalog as Single Source of Truth (Guaranteed Strictly Persian)
        if (class_exists(ModuleTitleResolver::class)) {
            $catalogTitle = app(ModuleTitleResolver::class)->resolveFromPermissionCatalog($moduleSlug);
            if (!empty($catalogTitle)) {
                return $catalogTitle;
            }
        }

        $transKey = "{$moduleSlug}::menu.group_title";
        $translated = trans($transKey);
        if ($translated !== $transKey && !empty($translated)) {
            return $translated;
        }

        if ($moduleSlug === 'single') {
            return 'آیتم‌های عمومی و مستقل';
        }

        return ucfirst($moduleSlug);
    }

    /**
     * Check if a menu item is a settings item.
     */
    protected function isSettingsItem(array $item): bool
    {
        $group = $item['group'] ?? '';

        // اگر group به -settings ختم شود یا شامل settings باشد
        if (str_ends_with($group, '-settings') || str_contains(strtolower($group), 'settings')) {
            return true;
        }

        // اگر title شامل "تنظیمات" باشد
        $title = $item['title'] ?? '';
        if (str_contains($title, 'تنظیمات')) {
            return true;
        }

        // اگر route شامل "settings" باشد
        $route = $item['route'] ?? '';
        if (str_contains(strtolower($route), 'settings')) {
            return true;
        }

        return false;
    }
}
