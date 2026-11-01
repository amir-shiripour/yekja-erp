<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Support\PermissionCatalog;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\Traits\BelongsToTenant;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;
use Throwable;

/**
 * Enterprise Zero-Touch Tenancy Registry
 */
class TenancyModelRegistry
{
    /**
     * Set of registered model class names.
     *
     * @var array<string, bool>
     */
    protected static array $models = [];

    /**
     * Cache of successfully bound models.
     *
     * @var array<string, bool>
     */
    protected static array $bound = [];

    /**
     * Cache of resolved Persian entity labels.
     *
     * @var array<string, string>
     */
    protected static array $entityLabels = [];

    /**
     * Flush the bound and label cache.
     */
    public static function flushBound(): void
    {
        static::$bound = [];
        static::$entityLabels = [];
    }

    /**
     * Fixed system baseline models required for CRM core operation.
     * These models cannot be deleted from the registry as they maintain critical CRM logic.
     *
     * @return array<string>
     */
    public static function systemModels(): array
    {
        return [
            // Booking Module
            'Modules\Booking\Entities\BookingService',
            'Modules\Booking\Entities\Appointment',
            'Modules\Booking\Entities\BookingCategory',
            'Modules\Booking\Entities\BookingForm',
            'Modules\Booking\Entities\BookingPayment',
            'Modules\Booking\Entities\BookingServiceProvider',
            'Modules\Booking\Entities\BookingSetting',

            // Clients Module
            'Modules\Clients\Entities\Client',
            'Modules\Clients\Entities\ClientForm',
            'Modules\Clients\Entities\ClientSetting',

            // Services Module
            'Modules\Services\App\Http\Models\Invoice',
            'Modules\Services\App\Http\Models\InvoiceItem',
            'Modules\Services\App\Http\Models\Order',
            'Modules\Services\App\Http\Models\Payment',
            'Modules\Services\App\Http\Models\Service',
            'Modules\Services\App\Http\Models\ServiceCategory',
            'Modules\Services\App\Http\Models\ServicePackage',

            // Market Module
            'Modules\Market\App\Models\Order',
            'Modules\Market\Entities\MarketSetting',
            'Modules\Market\Entities\MasterProduct',
            'Modules\Market\Entities\Vendor',
            'Modules\Market\Entities\Warehouse',

            // Accounting Module
            'Modules\Accounting\Entities\Invoice',
        ];
    }

    /**
     * Check if a model is a core system default model.
     */
    public static function isSystemModel(string $modelClass): bool
    {
        $trimmed = trim($modelClass, "\\ \t\n\r\0\x0B");
        return in_array($trimmed, static::systemModels(), true);
    }

    /**
     * Default catalog of module models that belong to tenants.
     *
     * @return array<class-string<Model>>
     */
    public static function defaultModels(): array
    {
        $configModels = config('tenancy.models');
        if (is_array($configModels) && !empty($configModels)) {
            return array_values(array_unique($configModels));
        }

        return static::systemModels();
    }

    /**
     * Persist model list into config/tenancy.php with strict validation.
     *
     * @param array<string> $models
     */
    public static function saveToConfig(array $models): bool
    {
        $validModels = [];
        foreach ($models as $model) {
            $trimmed = trim((string)$model, "\\ \t\n\r\0\x0B");
            if (empty($trimmed)) {
                continue;
            }
            if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\\\]*$/', $trimmed) && class_exists($trimmed)) {
                $validModels[] = $trimmed;
            }
        }

        $uniqueModels = array_values(array_unique($validModels));
        sort($uniqueModels);

        $modelsCode = '';
        foreach ($uniqueModels as $model) {
            $escaped = addslashes($model);
            $modelsCode .= "        '{$escaped}',\n";
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Dynamically Scoped Tenant Models (Enterprise Multi-Tenancy)
    |--------------------------------------------------------------------------
    |
    | Models listed here will have TenantScope, automatic tenant_id injection,
    | and anti-tampering guards applied dynamically at runtime.
    |
    */
    'models' => [
{$modelsCode}    ],
];

PHP;

        $path = config_path('tenancy.php');
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Dynamically add a model to tenancy configuration and bind it.
     */
    public static function addModel(string $modelClass): bool
    {
        $trimmed = trim($modelClass, "\\ \t\n\r\0\x0B");
        $current = static::defaultModels();
        if (!in_array($trimmed, $current, true)) {
            $current[] = $trimmed;
            static::saveToConfig($current);
        }
        config(['tenancy.models' => $current]);
        return static::bind($trimmed);
    }

    /**
     * Dynamically remove a model from tenancy configuration.
     * Core system default models are protected and cannot be removed.
     */
    public static function removeModel(string $modelClass): bool
    {
        $trimmed = trim($modelClass, "\\ \t\n\r\0\x0B");

        // Protect system baseline models from removal
        if (static::isSystemModel($trimmed)) {
            return false;
        }

        $current = static::defaultModels();
        $updated = array_values(array_filter($current, fn(string $m): bool => $m !== $trimmed));
        $saved = static::saveToConfig($updated);
        config(['tenancy.models' => $updated]);
        unset(static::$bound[$trimmed]);
        return $saved;
    }

    /**
     * Register one or multiple models to be dynamically scoped.
     *
     * @param array<class-string<Model>>|class-string<Model> $models
     */
    public static function register(array|string $models): void
    {
        foreach ((array)$models as $model) {
            static::$models[$model] = true;
        }
    }

    /**
     * Boot and dynamically bind tenancy to all registered models.
     */
    public static function bootAll(): void
    {
        $allModels = array_unique(array_merge(
            static::defaultModels(),
            array_keys(static::$models)
        ));

        foreach ($allModels as $modelClass) {
            static::bind($modelClass);
        }
    }

    /**
     * Dynamically bind Tenancy (TenantScope + creating + saving hooks) to a given model.
     *
     * @param class-string<Model> $modelClass
     */
    public static function bind(string $modelClass): bool
    {
        if (isset(static::$bound[$modelClass])) {
            return true;
        }

        try {
            if (!class_exists($modelClass)) {
                return false;
            }

            if (!is_subclass_of($modelClass, Model::class)) {
                return false;
            }

            // If the model already physically uses BelongsToTenant, it is already protected
            $traits = class_uses_recursive($modelClass);
            if (in_array(BelongsToTenant::class, $traits, true)) {
                static::$bound[$modelClass] = true;
                return true;
            }
        } catch (Throwable $e) {
            return false;
        }

        // 1. Add Global Scope
        $modelClass::addGlobalScope(new TenantScope());

        // 2. Creating Hook: Set tenant_id and Fail-Fast Guard
        $modelClass::creating(static function (Model $model): void {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isBypassed()) {
                return;
            }

            if (empty($model->getAttribute('tenant_id')) && $tenantManager->hasTenant()) {
                $model->setAttribute('tenant_id', $tenantManager->getTenantId());
            }

            // Fail-Fast Guard: Never allow an empty tenant_id in an active tenant context
            if ($tenantManager->hasTenant() && empty($model->getAttribute('tenant_id'))) {
                throw new DomainException(
                    'Security Violation: Cannot persist ' . get_class($model) . ' without tenant_id in active tenant context [' . $tenantManager->getTenantId() . '].'
                );
            }
        });

        // 3. Saving Hook: Anti-Tampering Guard
        $modelClass::saving(static function (Model $model): void {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isBypassed()) {
                return;
            }

            // Anti-Tampering Guard: Ensure record strictly belongs to the current tenant
            if ($tenantManager->hasTenant()) {
                $tenantId = (int)$tenantManager->getTenantId();
                $modelTenantId = $model->getAttribute('tenant_id');

                if (empty($modelTenantId)) {
                    $model->setAttribute('tenant_id', $tenantId);
                } elseif ((int)$modelTenantId !== $tenantId) {
                    throw new DomainException(
                        'Security Violation: Cross-tenant data tampering detected on ' . get_class($model) . ' ID [' . $model->getKey() . '].'
                    );
                }
            }
        });

        static::$bound[$modelClass] = true;
        return true;
    }

    /**
     * Get a comprehensive health report of all registered models.
     *
     * @return array<int, array{model: string, module: string, table: string, has_tenant_column: bool, protection: string, status: string}>
     */
    public static function getHealthReport(): array
    {
        $allModels = array_unique(array_merge(
            static::defaultModels(),
            array_keys(static::$models)
        ));

        $report = [];

        foreach ($allModels as $modelClass) {
            try {
                if (!class_exists($modelClass)) {
                    $report[] = [
                        'model' => $modelClass,
                        'module' => static::extractModuleName($modelClass),
                        'table' => 'N/A',
                        'has_tenant_column' => false,
                        'protection' => 'Missing Class',
                        'status' => 'inactive',
                    ];
                    continue;
                }
            } catch (Throwable $e) {
                $report[] = [
                    'model' => $modelClass,
                    'module' => static::extractModuleName($modelClass),
                    'table' => 'N/A',
                    'has_tenant_column' => false,
                    'protection' => 'Autoload Error: ' . $e->getMessage(),
                    'status' => 'error',
                ];
                continue;
            }

            try {
                /** @var Model $instance */
                $instance = new $modelClass();
                $table = $instance->getTable();
                $hasTenantColumn = Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id');

                $traits = class_uses_recursive($modelClass);
                $hasTrait = in_array(BelongsToTenant::class, $traits, true);
                $isBound = isset(static::$bound[$modelClass]);

                $protection = $hasTrait ? 'Trait (BelongsToTenant)' : ($isBound ? 'Zero-Touch Dynamic Scope' : 'Unprotected');
                $status = ($hasTrait || $isBound) ? 'active' : 'unprotected';

                $baseName = class_basename($modelClass);
                $moduleName = static::extractModuleName($modelClass);

                $report[] = [
                    'model' => $baseName,
                    'raw_class' => $modelClass,
                    'module' => $moduleName,
                    'module_label' => static::getModuleLabel($moduleName),
                    'entity_label' => static::getEntityLabel($baseName, $moduleName),
                    'table' => $table,
                    'has_tenant_column' => $hasTenantColumn,
                    'protection' => $protection,
                    'status' => $status,
                    'is_system' => static::isSystemModel($modelClass),
                ];
            } catch (Throwable $e) {
                $baseName = class_basename($modelClass);
                $moduleName = static::extractModuleName($modelClass);

                $report[] = [
                    'model' => $baseName,
                    'raw_class' => $modelClass,
                    'module' => $moduleName,
                    'module_label' => static::getModuleLabel($moduleName),
                    'entity_label' => static::getEntityLabel($baseName, $moduleName),
                    'table' => 'Protected',
                    'has_tenant_column' => false,
                    'protection' => 'Error: ' . $e->getMessage(),
                    'status' => 'error',
                    'is_system' => static::isSystemModel($modelClass),
                ];
            }
        }

        return $report;
    }

    public static function getModuleLabel(string $module): string
    {
        if (class_exists(ModuleTitleResolver::class)) {
            try {
                $resolved = app(ModuleTitleResolver::class)->resolve($module);
                if (!empty($resolved) && strcasecmp($resolved, $module) !== 0) {
                    return $resolved;
                }
            } catch (Throwable) {
                // Ignore and fall through to catalog/fallback
            }
        }

        if (class_exists(PermissionCatalog::class)) {
            $slugLower = strtolower(trim($module));
            $groups = PermissionCatalog::groups();
            if (isset($groups[$slugLower]['title'])) {
                $clean = preg_replace('/\s*\(.*?\)\s*/u', '', $groups[$slugLower]['title']);
                if (!empty($clean)) {
                    return trim($clean);
                }
            }
        }

        return $module;
    }


    public static function getEntityLabel(string $entity, ?string $module = null): string
    {
        $cacheKey = ($module ?? 'Core') . ':' . $entity;
        if (isset(static::$entityLabels[$cacheKey])) {
            return static::$entityLabels[$cacheKey];
        }

        $module = $module ?? 'Core';
        $modLower = strtolower(trim($module));
        $variants = [$entity];
        if (str_starts_with($entity, $module) && strlen($entity) > strlen($module)) {
            $variants[] = substr($entity, strlen($module));
        }
        $singleMod = rtrim($module, 's');
        if ($singleMod && str_starts_with($entity, $singleMod) && strlen($entity) > strlen($singleMod)) {
            $variants[] = substr($entity, strlen($singleMod));
        }

        // Add suffix words for compound entity names (e.g. ClientForm -> Form, BookingServiceProvider -> Provider)
        $words = preg_split('/(?=[A-Z])/', $entity, -1, PREG_SPLIT_NO_EMPTY);
        if ($words !== false && count($words) > 1) {
            $variants[] = end($words);
            if (count($words) > 2) {
                $variants[] = $words[count($words) - 2] . end($words);
            }
        }
        $variants = array_values(array_unique(array_filter($variants)));

        $groups = class_exists(PermissionCatalog::class) ? PermissionCatalog::groups() : [];

        // 1. Direct PermissionCatalog groups match (Highest accuracy for domain catalog)
        foreach ($variants as $var) {
            $snake = Str::snake($var);
            $pluralSnake = Str::plural($snake);

            $candidateKeys = [
                "{$modLower}_{$pluralSnake}",
                "{$modLower}_{$snake}",
                $pluralSnake,
                $snake,
            ];

            foreach ($candidateKeys as $ck) {
                if (isset($groups[$ck]['title'])) {
                    $title = $groups[$ck]['title'];
                    if (str_contains($title, ':')) {
                        $parts = explode(':', $title);
                        $title = trim(end($parts));
                    }
                    $clean = preg_replace('/\s*\(.*?\)\s*/u', '', $title);
                    if (!empty(trim((string)$clean))) {
                        return static::$entityLabels[$cacheKey] = trim((string)$clean);
                    }
                }
            }
        }

        // 2. PermissionCatalog translation map probe
        if (class_exists(PermissionCatalog::class)) {
            $actions = ['view', 'manage', 'index', 'edit', 'delete', 'all'];
            foreach ($variants as $var) {
                $kebab = Str::kebab($var);
                $pluralKebab = Str::plural($kebab);
                $snake = Str::snake($var);
                $pluralSnake = Str::plural($snake);

                $patterns = [
                    "{$modLower}.{$pluralKebab}.",
                    "{$modLower}.{$kebab}.",
                    "{$modLower}.{$pluralSnake}.",
                    "{$modLower}.{$snake}.",
                    "{$pluralKebab}.",
                    "{$kebab}.",
                ];
                foreach ($patterns as $pat) {
                    foreach ($actions as $act) {
                        $permKey = rtrim($pat, '.') . '.' . $act;
                        $trans = PermissionCatalog::translate($permKey);
                        if (preg_match('/[\x{0600}-\x{06FF}]/u', $trans)) {
                            $clean = preg_replace('/^(مشاهده|مدیریت|ایجاد|ویرایش|حذف|تخصیص|ثبت|لیست|نمایش)\s+/u', '', $trans);
                            $clean = preg_replace('/\s*\(.*?\)\s*/u', '', (string)$clean);
                            if (!empty(trim((string)$clean))) {
                                return static::$entityLabels[$cacheKey] = trim((string)$clean);
                            }
                        }
                    }
                }
            }
        }

        // 3. Module menu.php probe
        $menuPaths = [
            base_path("Modules/{$module}/resources/menu.php"),
            base_path("Modules/" . ucfirst($modLower) . "/resources/menu.php"),
        ];
        foreach ($menuPaths as $menuPath) {
            if (file_exists($menuPath)) {
                try {
                    $items = include $menuPath;
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (empty($item['title'])) {
                                continue;
                            }
                            $route = $item['route'] ?? '';
                            $perm = $item['permission'] ?? '';
                            foreach ($variants as $var) {
                                $kebab = Str::kebab($var);
                                $pluralKebab = Str::plural($kebab);
                                if ((!empty($route) && (str_contains($route, ".{$pluralKebab}") || str_contains($route, ".{$kebab}") || str_ends_with($route, "{$pluralKebab}") || str_ends_with($route, "{$kebab}"))) ||
                                    (!empty($perm) && (str_contains($perm, ".{$pluralKebab}.") || str_contains($perm, ".{$kebab}.") || str_ends_with($perm, "{$pluralKebab}") || str_ends_with($perm, "{$kebab}")))) {
                                    $clean = preg_replace('/\s*\(.*?\)\s*/u', '', (string)$item['title']);
                                    $clean = preg_replace('/^(لیست|مدیریت|افزودن)\s+/u', '', (string)$clean);
                                    if (!empty(trim((string)$clean))) {
                                        return static::$entityLabels[$cacheKey] = trim((string)$clean);
                                    }
                                }
                            }
                        }
                    }
                } catch (Throwable) {
                    // Ignore menu parsing errors
                }
            }
        }

        // 4. Check if compound entity ends with Item (e.g. InvoiceItem -> اقلام و سطرهای فاکتورها)
        if (str_ends_with($entity, 'Item') && strlen($entity) > 4) {
            $parentEntity = substr($entity, 0, -4);
            $parentLabel = static::getEntityLabel($parentEntity, $module);
            return static::$entityLabels[$cacheKey] = 'اقلام و سطرهای ' . $parentLabel;
        }

        // 5. Matchers in PermissionCatalog groups
        foreach ($variants as $var) {
            $snake = Str::snake($var);
            $pluralSnake = Str::plural($snake);
            $kebab = Str::kebab($var);
            $pluralKebab = Str::plural($kebab);

            foreach ($groups as $grpKey => $grp) {
                if ($modLower && !str_starts_with($grpKey, $modLower) && !str_contains($grpKey, $modLower)) {
                    continue;
                }
                $matchers = $grp['matchers'] ?? [];
                foreach ($matchers as $m) {
                    if (str_contains($m, ".{$kebab}.") || str_contains($m, ".{$pluralKebab}.") ||
                        str_contains($m, ".{$snake}.") || str_contains($m, ".{$pluralSnake}.")) {
                        $title = $grp['title'];
                        if (str_contains($title, ':')) {
                            $parts = explode(':', $title);
                            $title = trim(end($parts));
                        }
                        $clean = preg_replace('/\s*\(.*?\)\s*/u', '', (string)$title);
                        if (!empty(trim((string)$clean))) {
                            return static::$entityLabels[$cacheKey] = trim((string)$clean);
                        }
                    }
                }
            }
        }

        // 6. Generic PermissionCatalog search across all groups
        foreach ($variants as $var) {
            $snake = Str::snake($var);
            $pluralSnake = Str::plural($snake);
            foreach ($groups as $grpKey => $grp) {
                if (str_ends_with($grpKey, "_{$pluralSnake}") || str_ends_with($grpKey, "_{$snake}") || $grpKey === $pluralSnake || $grpKey === $snake) {
                    $title = $grp['title'];
                    if (str_contains($title, ':')) {
                        $parts = explode(':', $title);
                        $title = trim(end($parts));
                    }
                    $clean = preg_replace('/\s*\(.*?\)\s*/u', '', (string)$title);
                    if (!empty(trim((string)$clean))) {
                        return static::$entityLabels[$cacheKey] = trim((string)$clean);
                    }
                }
            }
        }

        // 7. Laravel translation files (trans)
        foreach ($variants as $var) {
            $snake = Str::snake($var);
            $pluralSnake = Str::plural($snake);
            foreach ([
                         "{$modLower}::menu.{$pluralSnake}",
                         "{$modLower}::menu.{$snake}",
                         "{$modLower}::general.{$pluralSnake}",
                         "{$modLower}::general.{$snake}",
                         "menu.{$pluralSnake}",
                         "menu.{$snake}",
                     ] as $transKey) {
                $t = trans($transKey);
                if ($t !== $transKey && !empty($t) && preg_match('/[\x{0600}-\x{06FF}]/u', (string)$t)) {
                    return static::$entityLabels[$cacheKey] = trim((string)$t);
                }
            }
        }

        // 8. Fallback: Headline format of entity name
        return static::$entityLabels[$cacheKey] = Str::headline($entity);
    }

    /**
     * Discover available Eloquent models from installed modules that are not yet registered.
     * @return array<int, array{class: string, model: string, module: string, label: string}>
     */
    public static function discoverAvailableModels(): array
    {
        $registered = array_flip(static::defaultModels());
        $discovered = [];

        $modulesPath = base_path('Modules');
        if (!is_dir($modulesPath)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulesPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            if (!str_contains($path, DIRECTORY_SEPARATOR . 'Entities' . DIRECTORY_SEPARATOR) &&
                !str_contains($path, DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $relativePath = str_replace([base_path() . DIRECTORY_SEPARATOR, '.php'], '', $path);
            $className = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

            if (isset($registered[$className])) {
                continue;
            }

            try {
                if (class_exists($className) && is_subclass_of($className, Model::class)) {
                    $ref = new \ReflectionClass($className);
                    if ($ref->isAbstract()) {
                        continue;
                    }

                    $moduleName = static::extractModuleName($className);
                    $baseName = class_basename($className);
                    $moduleLabel = static::getModuleLabel($moduleName);
                    $discovered[] = [
                        'class' => $className,
                        'model' => $baseName,
                        'module' => $moduleName,
                        'module_label' => $moduleLabel,
                        'label' => $moduleLabel . ' ← ' . static::getEntityLabel($baseName, $moduleName),
                    ];
                }
            } catch (Throwable) {
                continue;
            }
        }

        usort($discovered, fn($a, $b) => strcmp($a['module'], $b['module']));

        return $discovered;
    }

    public static function extractModuleName(string $modelClass): string
    {
        if (preg_match('/Modules\\\\([^\\\\]+)/', $modelClass, $matches)) {
            return $matches[1];
        }

        return 'Core';
    }
}
