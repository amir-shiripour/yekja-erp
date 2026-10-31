<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Module;
use App\Models\Tenant;
use App\Services\Modules\ModuleMenuService;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\Invoice;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\Order as MarketOrder;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Events\QuotaThresholdExceeded;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Throwable;

class TenantQuotaService
{
    /**
     * In-memory cache for schema table/column checks to prevent information_schema query bottlenecks.
     */
    protected static array $schemaCache = [];

    protected static function getSchemaVersion(): string
    {
        return (string)config('resellerhub.schema_cache.version', 'v1');
    }

    protected static function getSchemaTtl(): int
    {
        return (int)config('resellerhub.schema_cache.ttl_seconds', 86400);
    }

    public static function clearSchemaCache(): void
    {
        self::$schemaCache = [];
    }


    protected function cacheTtl(): int
    {
        return max(1, (int)config('resellerhub.quotas.cache_ttl_seconds', 30));
    }

    protected static function checkTable(string $table): bool
    {
        if (!array_key_exists($table, self::$schemaCache)) {
            $ver = self::getSchemaVersion();
            $ttl = self::getSchemaTtl();
            self::$schemaCache[$table] = Cache::remember("rh_schema_{$ver}_tbl_{$table}", $ttl, function () use ($table) {
                return Schema::hasTable($table);
            });
        }
        return (bool)self::$schemaCache[$table];
    }

    protected static function checkColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";
        if (!array_key_exists($key, self::$schemaCache)) {
            $ver = self::getSchemaVersion();
            $ttl = self::getSchemaTtl();
            self::$schemaCache[$key] = Cache::remember("rh_schema_{$ver}_col_{$key}", $ttl, function () use ($table, $column) {
                return self::checkTable($table) && Schema::hasColumn($table, $column);
            });
        }
        return (bool)self::$schemaCache[$key];
    }

    /**
     * Complete Entitlements & Quotas Catalog powered by QuotaKey enum SSOT.
     */
    public static function getDefinitions(): array
    {
        $definitions = [];
        foreach (QuotaKey::cases() as $case) {
            $definitions[$case->value] = $case->toDefinition();
        }
        return $definitions;
    }


    public function getLimit(?Tenant $tenant, string|QuotaKey $key): ?int
    {
        $keyName = $key instanceof QuotaKey ? $key->value : $key;

        if (!$tenant) {
            return null; // System direct is unlimited
        }

        // Reseller tenants without an active paid plan or package have 0 limit
        if ($tenant->reseller_id && !$tenant->hasActivePaidPlan()) {
            return 0;
        }

        // 1. Direct tenant setting limits
        $limits = data_get($tenant->settings, 'limits');
        if (is_array($limits) && array_key_exists($keyName, $limits)) {
            $val = $limits[$keyName];
            if ($val === -1 || $val === '-1' || $val === 'unlimited' || $val === '' || $val === null) {
                return null; // Explicit unlimited override on tenant
            }
            return (int)$val;
        }

        // 2. Fallback to assigned package limits
        if ($tenant->package_id) {
            $package = $tenant->package ?: ResellerPackage::find($tenant->package_id);
            if ($package && is_array($package->limits) && array_key_exists($keyName, $package->limits)) {
                $val = $package->limits[$keyName];
                if ($val !== '' && $val !== null) {
                    return (int)$val;
                }
            }
        }

        return null;
    }

    /**
     * Check if a specific limit is configured (i.e. not unlimited).
     */
    public function hasLimit(?Tenant $tenant, string|QuotaKey $key): bool
    {
        return !is_null($this->getLimit($tenant, $key));
    }

    /**
     * Check if a boolean feature is enabled for this tenant.
     */
    public function hasFeature(?Tenant $tenant, string|QuotaKey $key, ?bool $default = null): bool
    {
        $quotaKey = $key instanceof QuotaKey ? $key : QuotaKey::tryFrom($key);
        $keyName = $quotaKey ? $quotaKey->value : (string)$key;

        if (!$tenant) {
            return true; // Direct central system has all features
        }

        // Reseller tenants without an active paid plan or package have no modular features
        if ($tenant->reseller_id && !$tenant->hasActivePaidPlan()) {
            return false;
        }

        // 1. Check direct tenant settings
        $features = data_get($tenant->settings, 'features');
        if (is_array($features)) {
            if (in_array($keyName, $features, true)) {
                return true;
            }
            if (array_key_exists($keyName, $features)) {
                return (bool)$features[$keyName];
            }
            // Explicit features set on tenant: feature is disabled
            return false;
        }

        // 2. Check package features (only when tenant settings has no features configured)
        if ($tenant->package_id) {
            $package = $tenant->package ?: ResellerPackage::find($tenant->package_id);
            if ($package && is_array($package->features)) {
                if (in_array($keyName, $package->features, true)) {
                    return true;
                }
                if (array_key_exists($keyName, $package->features)) {
                    return (bool)$package->features[$keyName];
                }
            }
        }

        // 3. Fallback to enum default if not explicitly provided
        if (!is_null($default)) {
            return $default;
        }

        return (bool)($quotaKey?->default() ?? false);
    }

    /**
     * Calculate live resource usage for a tenant without touching core module files.
     */
    public function getUsage(?Tenant $tenant, string|QuotaKey $key): int
    {
        if (!$tenant) {
            return 0;
        }

        $tenantId = (int)$tenant->id;
        $quotaKey = $key instanceof QuotaKey ? $key : QuotaKey::tryFrom($key);
        if (!$quotaKey) {
            return 0;
        }

        $cacheKey = "tenant_{$tenantId}_quota_{$quotaKey->value}";

        return Cache::remember($cacheKey, $this->cacheTtl(), function () use ($tenantId, $quotaKey) {
            try {
                return match ($quotaKey) {
                    QuotaKey::Clients => (class_exists(Client::class) && self::checkTable('clients'))
                        ? Client::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                        : 0,

                    QuotaKey::Appointments => (class_exists(Appointment::class) && self::checkTable('appointments'))
                        ? (self::checkColumn('appointments', 'tenant_id')
                            ? Appointment::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                            : Appointment::withoutGlobalScopes()
                                ->whereHas('client', function ($q) use ($tenantId) {
                                    $q->withoutGlobalScopes()->where('tenant_id', $tenantId);
                                })->count())
                        : 0,

                    QuotaKey::BookingProviders => (class_exists(BookingServiceProvider::class) && self::checkTable('booking_service_providers'))
                        ? (self::checkColumn('booking_service_providers', 'tenant_id')
                            ? BookingServiceProvider::withoutGlobalScopes()
                                ->where('tenant_id', $tenantId)
                                ->distinct('provider_user_id')
                                ->count('provider_user_id')
                            : BookingServiceProvider::withoutGlobalScopes()
                                ->whereHas('provider', function ($q) use ($tenantId) {
                                    $q->where('tenant_id', $tenantId);
                                })
                                ->distinct('provider_user_id')
                                ->count('provider_user_id'))
                        : 0,

                    QuotaKey::Invoices => (function () use ($tenantId) {
                        $totalInvoices = 0;
                        if (class_exists(Invoice::class) && self::checkTable('accounting_invoices')) {
                            $accQuery = Invoice::withoutGlobalScopes();
                            if (self::checkColumn('accounting_invoices', 'tenant_id')) {
                                $totalInvoices += $accQuery->where('accounting_invoices.tenant_id', $tenantId)->count();
                            } else {
                                $tenantUserIds = \App\Models\User::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id');
                                $totalInvoices += $accQuery->whereHas('customer', function ($q) use ($tenantId, $tenantUserIds) {
                                    if (self::checkColumn('clients', 'tenant_id')) {
                                        $q->withoutGlobalScopes()->where('clients.tenant_id', $tenantId);
                                    } elseif ($tenantUserIds->isNotEmpty()) {
                                        $q->withoutGlobalScopes()->whereIn('clients.created_by', $tenantUserIds);
                                    } else {
                                        $q->withoutGlobalScopes()->whereRaw('1=0');
                                    }
                                })->count();
                            }
                        }
                        if (class_exists(\Modules\Services\App\Http\Models\Invoice::class) && self::checkTable('service_invoices')) {
                            $servQuery = \Modules\Services\App\Http\Models\Invoice::withoutGlobalScopes();
                            if (self::checkColumn('service_invoices', 'tenant_id')) {
                                $totalInvoices += $servQuery->where('service_invoices.tenant_id', $tenantId)
                                    ->whereNotNull('invoice_number')
                                    ->count();
                            } else {
                                $tenantUserIds = \App\Models\User::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id');
                                $totalInvoices += $servQuery->whereHas('customer', function ($q) use ($tenantId, $tenantUserIds) {
                                    if (self::checkColumn('clients', 'tenant_id')) {
                                        $q->withoutGlobalScopes()->where('clients.tenant_id', $tenantId);
                                    } elseif ($tenantUserIds->isNotEmpty()) {
                                        $q->withoutGlobalScopes()->whereIn('clients.created_by', $tenantUserIds);
                                    } else {
                                        $q->withoutGlobalScopes()->whereRaw('1=0');
                                    }
                                })->whereNotNull('invoice_number')->count();
                            }
                        }
                        return $totalInvoices;
                    })(),

                    QuotaKey::Products => (class_exists(MasterProduct::class) && self::checkTable('market_master_products'))
                        ? (self::checkColumn('market_master_products', 'tenant_id')
                            ? MasterProduct::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                            : 0)
                        : 0,

                    QuotaKey::Warehouses => (class_exists(Warehouse::class) && self::checkTable('market_warehouses'))
                        ? (self::checkColumn('market_warehouses', 'tenant_id')
                            ? Warehouse::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                            : Warehouse::whereHas('vendor.user', function ($q) use ($tenantId) {
                                $q->where('tenant_id', $tenantId);
                            })->count())
                        : 0,

                    QuotaKey::Stores => (class_exists(Vendor::class) && self::checkTable('market_vendors'))
                        ? (self::checkColumn('market_vendors', 'tenant_id')
                            ? Vendor::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                            : Vendor::whereHas('user', function ($q) use ($tenantId) {
                                $q->where('tenant_id', $tenantId);
                            })->count())
                        : 0,

                    QuotaKey::Orders => (class_exists(MarketOrder::class) && self::checkTable('market_orders'))
                        ? (self::checkColumn('market_orders', 'tenant_id')
                            ? MarketOrder::withoutGlobalScopes()->where('tenant_id', $tenantId)->count()
                            : 0)
                        : 0,

                    default => 0,
                };
            } catch (Throwable $e) {
                Log::error("[TenantQuotaService] Error computing usage for tenant {$tenantId}, quota {$quotaKey->value}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Invalidate cached usage for a tenant.
     */
    public function invalidateUsage(?Tenant $tenant, string|QuotaKey $key = null): void
    {
        if (!$tenant) {
            return;
        }

        $tenantId = (int)$tenant->id;

        if ($key) {
            $quotaKey = $key instanceof QuotaKey ? $key : QuotaKey::tryFrom($key);
            if ($quotaKey) {
                Cache::forget("tenant_{$tenantId}_quota_{$quotaKey->value}");
            }
        } else {
            foreach (QuotaKey::cases() as $qKey) {
                Cache::forget("tenant_{$tenantId}_quota_{$qKey->value}");
            }
        }
    }

    /**
     * Check if tenant has remaining quota for a given key.
     * Returns true if unlimited (null) or current usage < limit.
     */
    public function hasRemainingQuota(?Tenant $tenant, string|QuotaKey $key): bool
    {
        if (!$tenant) {
            return true;
        }

        $quotaKey = $key instanceof QuotaKey ? $key : QuotaKey::tryFrom($key);
        if (!$quotaKey) {
            return true;
        }

        // If it's a boolean feature
        if ($quotaKey->isFeature()) {
            if ($quotaKey === QuotaKey::MultiVendor) {
                $isAllowed = $this->hasFeature($tenant, QuotaKey::MultiVendor);
                try {
                    $existingStores = $this->getUsage($tenant, QuotaKey::Stores);
                } catch (Throwable) {
                    return false; // Fail-closed
                }
                if (!$isAllowed && $existingStores >= 1) {
                    return false;
                }
                return true;
            }
            return $this->hasFeature($tenant, $quotaKey);
        }

        // Stores check (both multi_vendor rule and numeric limit)
        if ($quotaKey === QuotaKey::Stores) {
            $isMultiAllowed = $this->hasFeature($tenant, QuotaKey::MultiVendor);
            try {
                $existingStores = $this->getUsage($tenant, QuotaKey::Stores);
            } catch (Throwable) {
                return false; // Fail-closed
            }
            if (!$isMultiAllowed && $existingStores >= 1) {
                return false;
            }
        }

        // Warehouses check (WMS feature check and numeric limit)
        if ($quotaKey === QuotaKey::Warehouses) {
            $isWmsAllowed = $this->hasFeature($tenant, QuotaKey::WmsEnabled);
            if (!$isWmsAllowed) {
                return false;
            }
        }

        $limit = $this->getLimit($tenant, $quotaKey);
        if (is_null($limit)) {
            return true; // Unlimited
        }

        try {
            return $this->getUsage($tenant, $quotaKey) < $limit;
        } catch (Throwable $e) {
            Log::critical("[TenantQuotaService] Fail-closed: Denied access for tenant {$tenant->id}, key {$quotaKey->value} due to usage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate remaining quota.
     * Returns null if unlimited, or max.
     */
    public function remainingQuota(?Tenant $tenant, string|QuotaKey $key): ?int
    {
        if (!$tenant) {
            return null;
        }

        $limit = $this->getLimit($tenant, $key);
        if (is_null($limit)) {
            return null;
        }

        try {
            $usage = $this->getUsage($tenant, $key);
            return max(0, $limit - $usage);
        } catch (Throwable $e) {
            Log::critical("[TenantQuotaService] Fail-closed: Error in remainingQuota for tenant {$tenant->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if a module is active or accessible for the tenant.
     */
    public function isModuleActiveForTenant(?Tenant $tenant, string $moduleSlug): bool
    {
        if (!$tenant) {
            return true;
        }

        // Reseller tenants without an active paid plan or package have no active modules
        if ($tenant->reseller_id && !$tenant->hasActivePaidPlan()) {
            return false;
        }

        $slug = strtolower(trim($moduleSlug));

        // Invoices quota can be powered by either 'services' or 'accounting'
        if ($slug === 'services') {
            return $tenant->hasModule('services')
                || $tenant->hasModule('accounting')
                || $tenant->isModulePurchased('services')
                || $tenant->isModulePurchased('accounting');
        }

        return $tenant->hasModule($slug) || $tenant->isModulePurchased($slug);
    }

    /**
     * Get a comprehensive summary array of all quotas and features for UI presentation.
     * When a tenant is provided, only quotas and features for active/purchased modules are returned.
     * Cached for 30s per tenant to eliminate repeated count queries on dashboard views.
     */
    public function getQuotasSummary(?Tenant $tenant, bool $fresh = false): array
    {
        if (!$tenant) {
            return $this->buildQuotasSummary(null);
        }

        $cacheKey = "reseller_tenant_quotas_summary_{$tenant->id}";
        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTtl(), function () use ($tenant) {
            return $this->buildQuotasSummary($tenant);
        });
    }

    /**
     * Clear cached quotas summary for a tenant.
     */
    public function clearQuotasCache(int|string|Tenant $tenant): void
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        Cache::forget("reseller_tenant_quotas_summary_{$tenantId}");
    }

    /**
     * Check if a numeric quota has reached/exceeded the 90% warning threshold and trigger alerts.
     * Features an automated 30-day billing cycle cache throttle to prevent SMS flooding.
     */
    public function checkAndDispatchQuotaWarning(Tenant $tenant, string|QuotaKey $key, int $used, int $limit): void
    {
        $quotaKey = $key instanceof QuotaKey ? $key : QuotaKey::tryFrom((string)$key);
        if (!$quotaKey || $quotaKey->isFeature() || $limit <= 0) {
            return;
        }

        $ratio = $used / $limit;
        if ($ratio >= 0.90) {
            $currentMonth = JalaliDateHelper::now()->format('Ym');
            $cacheKey = "rh_quota_90_alert_{$tenant->id}_{$quotaKey->value}_{$currentMonth}";

            // Prevent repeated SMS/alerts during the same 30-day monthly cycle
            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, true, now()->addDays(30));

                $percent = (int)round($ratio * 100);

                event(new QuotaThresholdExceeded(
                    tenant: $tenant,
                    quotaKey: $quotaKey,
                    limit: $limit,
                    used: $used,
                    severity: 'warning'
                ));

                try {
                    $workflowService = app(ResellerWorkflowService::class);
                    $workflowService->onQuotaThresholdExceeded($tenant, $quotaKey->value, $limit, $used);
                } catch (Throwable $e) {
                    Log::warning("[TenantQuotaService] Failed to trigger quota workflow for tenant #{$tenant->id}: " . $e->getMessage());
                }

                Log::warning("[TenantQuotaService] Quota warning dispatched for Tenant #{$tenant->id}, Key: {$quotaKey->value}, Used: {$used}/{$limit} ({$percent}%)");
            }
        }
    }

    /**
     * Calculate quotas summary array directly without cache layer.
     */
    public function buildQuotasSummary(?Tenant $tenant): array
    {
        $definitions = self::getDefinitions();
        $summary = [
            'quotas' => [],
            'features' => [],
            'all_healthy' => true,
        ];

        foreach ($definitions as $key => $def) {
            $quotaKey = QuotaKey::resolve($key);
            $module = $quotaKey?->module() ?? ($def['module'] ?? null);

            // Skip quota/feature if the owning module is not enabled or purchased for this tenant
            if ($tenant && $module && !$this->isModuleActiveForTenant($tenant, $module)) {
                continue;
            }

            if ($def['type'] === 'quota') {
                $limit = $this->getLimit($tenant, $quotaKey ?: $key);
                $used = $this->getUsage($tenant, $quotaKey ?: $key);
                $isUnlimited = is_null($limit);
                $remaining = $isUnlimited ? null : max(0, $limit - $used);
                $percent = ($isUnlimited || $limit <= 0) ? 0 : min(100, (int)round(($used / $limit) * 100));

                $colorClass = 'emerald';
                if (!$isUnlimited) {
                    if ($percent >= 90) {
                        $colorClass = 'rose';
                        $summary['all_healthy'] = false;
                        if ($tenant) {
                            $this->checkAndDispatchQuotaWarning($tenant, $quotaKey ?: $key, $used, (int)$limit);
                        }
                    } elseif ($percent >= 70) {
                        $colorClass = 'amber';
                    }
                }

                $iconBgClass = match ($quotaKey) {
                    QuotaKey::Clients => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400',
                    QuotaKey::Appointments => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400',
                    QuotaKey::Invoices => 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400',
                    QuotaKey::Orders => 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400',
                    default => 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400',
                };

                $percentBadgeClass = match (true) {
                    $percent >= 90 => 'bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300',
                    $percent >= 75 => 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300',
                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                };

                $progressBarClass = match (true) {
                    $percent >= 90 => 'bg-rose-500',
                    $percent >= 75 => 'bg-amber-500',
                    default => 'bg-indigo-600',
                };

                $summary['quotas'][$key] = array_merge($def, [
                    'limit' => $limit,
                    'used' => $used,
                    'remaining' => $remaining,
                    'is_unlimited' => $isUnlimited,
                    'is_exceeded' => !$isUnlimited && $used >= $limit,
                    'percent' => $percent,
                    'color_class' => $colorClass,
                    'icon_bg_class' => $iconBgClass,
                    'percent_badge_class' => $percentBadgeClass,
                    'progress_bar_class' => $progressBarClass,
                ]);
            } else {
                $isEnabled = $this->hasFeature($tenant, $quotaKey ?: $key);
                $summary['features'][$key] = array_merge($def, [
                    'is_enabled' => $isEnabled,
                ]);
            }
        }

        return $summary;
    }

    /**
     * Build a rich payload for UI modals and alerts when a quota is reached or exceeded.
     */
    public function getExceededQuotaPayload(?Tenant $tenant, string|QuotaKey $key): array
    {
        $quotaKey = QuotaKey::resolve($key);
        $keyName = $quotaKey ? $quotaKey->value : (string)$key;

        $def = $quotaKey?->toDefinition() ?? [
            'module' => 'general',
            'module_title' => 'سیستم',
            'key' => $keyName,
            'title' => 'سقف مجاز قابلیت',
            'description' => 'سقف مجاز برای این قابلیت پر شده است.',
            'type' => 'quota',
            'unit' => 'مورد',
            'icon' => 'receipt',
        ];

        $limit = $this->getLimit($tenant, $quotaKey ?: $keyName);
        $usage = $this->getUsage($tenant, $quotaKey ?: $keyName);
        $remaining = is_null($limit) ? null : max(0, $limit - $usage);
        $reseller = $tenant?->reseller;

        $supportPhone = $tenant?->getBranding('support_phone')
            ?: ($reseller?->support_phone ?? null);
        $supportEmail = $tenant?->getBranding('support_email')
            ?: ($reseller?->support_email ?? null);
        $resellerName = $reseller?->company_name
            ?: ($reseller?->user?->name ?? __('resellerhub::messages.common.official_reseller'));

        $packageName = $tenant?->package?->name ?? __('resellerhub::resellerhub.services.quota.standard_plan');
        $unit = $def['unit'] ?? __('resellerhub::resellerhub.services.quota.item_unit');
        $title = $def['title'] ?? __('resellerhub::resellerhub.services.quota.limit_title');
        $isFeature = ($def['type'] ?? 'quota') === 'feature';

        if ($quotaKey === QuotaKey::Stores && !$this->hasFeature($tenant, QuotaKey::MultiVendor, false) && $this->getUsage($tenant, QuotaKey::Stores) >= 1) {
            $isFeature = true;
            $title = QuotaKey::MultiVendor->title();
            $message = "سامانه شما در حالت تک‌فروشگاهی فعال است و امکان ساخت شعب یا فروشگاه‌های بیشتر در این پلن مجاز نمی‌باشد. جهت ارتقا به حالت چندفروشگاهی، با نماینده فروش خود تماس حاصل فرمایید.";
        } elseif ($quotaKey === QuotaKey::Warehouses && !$this->hasFeature($tenant, QuotaKey::WmsEnabled, true)) {
            $isFeature = true;
            $title = QuotaKey::WmsEnabled->title();
            $message = "سیستم انبارداری پیشرفته در پلن اشتراک سامانه شما غیرفعال است. جهت دسترسی به مدیریت انبارها، اشتراک خود را ارتقا دهید.";
        } elseif ($isFeature) {
            $message = __('resellerhub::resellerhub.services.quota.feature_disabled', ['title' => $title]);
        } else {
            $message = __('resellerhub::resellerhub.services.quota.quota_exceeded', ['title' => $title, 'limit' => $limit, 'unit' => $unit]);
        }

        if ($supportPhone) {
            $message .= " (تلفن پشتیبانی: {$supportPhone})";
        }

        $resellerSettings = $reseller ? ResellerSetting::allForReseller($reseller->id) : [];
        $currency = $resellerSettings['currency'] ?? 'toman';
        $currencyLabel = reseller_currency_label($currency);

        return [
            'key' => $keyName,
            'title' => $title,
            'module' => $def['module'] ?? 'general',
            'module_title' => $def['module_title'] ?? 'سیستم',
            'unit' => $unit,
            'currency' => $currency,
            'currency_label' => $currencyLabel,
            'icon' => $def['icon'] ?? 'receipt',
            'is_feature' => $isFeature,
            'limit' => $isFeature ? null : $limit,
            'usage' => $isFeature ? null : $usage,
            'remaining' => $isFeature ? null : $remaining,
            'tenant_id' => $tenant?->id,
            'tenant_name' => $tenant?->name ?? 'سامانه کاربر',
            'package_name' => $packageName,
            'reseller_name' => $resellerName,
            'support_phone' => $supportPhone,
            'support_email' => $supportEmail,
            'message' => $message,
        ];
    }

    /**
     * Resolves all guarded route names defined across QuotaKeys to active runtime URLs and paths.
     * This provides a dynamic, zero-hardcode configuration for frontend interceptors.
     *
     * @return array<int, array{key: string, url: string, path: string}>
     */
    public function getGuardedRoutesForFrontend(): array
    {
        $guarded = [];

        foreach (QuotaKey::cases() as $case) {
            foreach ($case->guardedRouteNames() as $routeName) {
                // Only resolve creation or action routes that users navigate to via GET
                if (
                    str_ends_with($routeName, '.create') ||
                    str_ends_with($routeName, '.import') ||
                    str_ends_with($routeName, '.export')
                ) {
                    if (Route::has($routeName)) {
                        try {
                            $url = route($routeName);
                            $path = parse_url($url, PHP_URL_PATH) ?: $url;
                            $guarded[] = [
                                'key' => $case->value,
                                'url' => $url,
                                'path' => '/' . ltrim($path, '/'),
                            ];
                        } catch (Throwable $e) {
                            // Route requires parameters that cannot be inferred without context
                        }
                    }
                }
            }
        }

        return $guarded;
    }

    /**
     * Build rich payload for UI modal when a tenant has a pending/unpaid order locking their module(s).
     */
    public function getPendingOrderPayload(Tenant $tenant, ?string $moduleSlug = null): array
    {
        $tenant->loadMissing(['package', 'reseller.user']);
        $reseller = $tenant->reseller;

        // Find pending or inactive orders for this tenant
        $pendingOrder = $tenant->orders()
            ->whereIn('status', [ResellerOrder::STATUS_PENDING, ResellerOrder::STATUS_INACTIVE, 'pending', 'inactive'])
            ->latest('id')
            ->first();

        // Find associated unpaid invoice
        $unpaidInvoice = null;
        if ($pendingOrder && $pendingOrder->invoice_id) {
            $unpaidInvoice = ResellerInvoice::find($pendingOrder->invoice_id);
        }
        if (!$unpaidInvoice) {
            $unpaidInvoice = ResellerInvoice::where('tenant_id', $tenant->id)
                ->whereNotIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::CANCELLED->value])
                ->latest('id')
                ->first();
        }

        $packageName = $tenant->package?->name ?? ($pendingOrder?->package_snapshot['name'] ?? 'پلن اشتراک');

        $moduleTitle = null;
        if ($moduleSlug) {
            $menuService = class_exists(ModuleMenuService::class)
                ? app(ModuleMenuService::class)
                : null;
            $moduleTitle = $menuService ? $menuService->resolveModuleGroupTitle($moduleSlug, $moduleSlug) : ucfirst($moduleSlug);
        }

        $supportPhone = $tenant->getBranding('support_phone')
            ?: ($reseller?->support_phone ?? null);
        $supportEmail = $tenant->getBranding('support_email')
            ?: ($reseller?->support_email ?? null);
        $resellerName = $reseller?->company_name
            ?: ($reseller?->user?->name ?? 'نماینده رسمی نرم‌افزار');

        $resellerSettings = $reseller ? ResellerSetting::allForReseller($reseller->id) : [];
        $currency = $resellerSettings['currency'] ?? 'toman';
        $currencyLabel = reseller_currency_label($currency);

        $invoiceDue = $unpaidInvoice ? max(0, (int)$unpaidInvoice->total - (int)$unpaidInvoice->paid_amount) : 0;
        $invoiceTotal = $unpaidInvoice ? (int)$unpaidInvoice->total : 0;
        $paymentUrl = $unpaidInvoice
            ? (request()->getSchemeAndHttpHost() . '/get-started/pay/' . $unpaidInvoice->id)
            : null;

        $discountAmount = $unpaidInvoice ? (int)($unpaidInvoice->items->sum('discount_amount') ?: ($unpaidInvoice->discount_amount ?? 0)) : 0;
        $hasPackageDiscount = $discountAmount > 0 || !empty($unpaidInvoice?->meta['has_package_discount']);
        $originalTotal = $invoiceTotal + $discountAmount;

        $title = $moduleTitle ? "ماژول «{$moduleTitle}»" : "ماژول‌های پلن «{$packageName}»";
        $message = "سفارش شما برای پلن «{$packageName}» ثبت شده است، اما تا زمان تکمیل فرآیند پرداخت و فعال‌سازی سفارش، دسترسی به این بخش غیرفعال می‌باشد. جهت فعال‌سازی آنی، فاکتور مربوطه را پرداخت فرمایید.";

        return [
            'is_order_pending' => true,
            'module_slug' => $moduleSlug,
            'module_title' => $moduleTitle,
            'title' => $title,
            'message' => $message,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name ?? 'سامانه کاربر',
            'package_name' => $packageName,
            'has_package_discount' => $hasPackageDiscount,
            'discount_amount' => $discountAmount,
            'discount_amount_formatted' => $discountAmount > 0 ? reseller_price($discountAmount, $currency) : null,
            'original_total' => $originalTotal,
            'original_total_formatted' => $discountAmount > 0 ? reseller_price($originalTotal, $currency) : null,
            'order_id' => $pendingOrder?->id,
            'order_number' => $pendingOrder?->order_number,
            'order_number_formatted' => $pendingOrder?->order_number ? reseller_fa_num($pendingOrder->order_number) : null,
            'order_status' => $pendingOrder?->status ?? 'pending',
            'invoice_id' => $unpaidInvoice?->id,
            'invoice_number' => $unpaidInvoice?->invoice_number,
            'invoice_number_formatted' => $unpaidInvoice?->invoice_number ? reseller_fa_num($unpaidInvoice->invoice_number) : null,
            'currency' => $currency,
            'currency_label' => $currencyLabel,
            'invoice_due' => $invoiceDue,
            'invoice_due_formatted' => reseller_price($invoiceDue, $currency),
            'invoice_total' => $invoiceTotal,
            'invoice_total_formatted' => reseller_price($invoiceTotal, $currency),
            'invoice_due_date_jalali' => $unpaidInvoice?->due_date ? reseller_format_jalali($unpaidInvoice->due_date) : null,
            'invoice_issue_date_jalali' => $unpaidInvoice?->issue_date ? reseller_format_jalali($unpaidInvoice->issue_date) : null,
            'invoice_status' => $unpaidInvoice?->status ?? 'unpaid',
            'payment_url' => $paymentUrl,
            'reseller_name' => $resellerName,
            'support_phone' => $supportPhone,
            'support_email' => $supportEmail,
        ];
    }

    /**
     * Get all locked modules for the current tenant that need client-side click interception.
     *
     * @return array<int, array{slug: string, name: string, title: string, prefixes: array<string>, payload: array}>
     */
    public function getLockedModulesForFrontend(?Tenant $tenant = null): array
    {
        if (!$tenant) {
            $tenantManager = app(TenantManager::class);
            $tenant = $tenantManager->hasTenant() ? $tenantManager->getTenant() : null;
        }

        if (!$tenant) {
            return [];
        }

        $allModules = Module::where('installed', true)->where('active', true)->get();
        $locked = [];
        $menuService = class_exists(ModuleMenuService::class)
            ? app(ModuleMenuService::class)
            : null;

        foreach ($allModules as $module) {
            if (!empty($module->is_core)) {
                continue;
            }

            $slug = strtolower(trim($module->slug));
            $name = strtolower(trim($module->name));

            $isPurchased = $tenant->isModulePurchased($slug) || $tenant->isModulePurchased($name);
            $isActive = $tenant->hasModule($slug) || $tenant->hasModule($name);

            if ($isPurchased && !$isActive) {
                $payload = $this->getPendingOrderPayload($tenant, $slug);
                $title = $menuService ? $menuService->resolveModuleGroupTitle($slug, $module->name) : $module->name;

                $prefixes = [
                    '/' . $slug,
                    '/user/' . $slug,
                    '/' . $name,
                    '/user/' . $name,
                ];

                if (!empty($tenant->subdomain)) {
                    $sub = strtolower(trim($tenant->subdomain));
                    $prefixes[] = '/' . $sub . '/' . $slug;
                    $prefixes[] = '/' . $sub . '/user/' . $slug;
                    $prefixes[] = '/' . $sub . '/' . $name;
                    $prefixes[] = '/' . $sub . '/user/' . $name;
                }

                $locked[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'title' => $title,
                    'prefixes' => array_values(array_unique($prefixes)),
                    'payload' => $payload,
                ];
            }
        }

        return $locked;
    }
}

