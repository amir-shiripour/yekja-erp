<?php

namespace Modules\ResellerHub\App\Services;

use Modules\ResellerHub\App\Helpers\ResellerHelper;

use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Services\Modules\ModuleMenuService;
use App\Tenancy\TenantManager;
use Carbon\Carbon;
use Exception;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Support\TenantSubdomainUrl;
use Modules\ResellerHub\App\Models\ResellerModulePricing;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Throwable;

class TenantProvisioningService
{
    public function __construct(
        protected ResellerBillingService         $billingService,
        protected ModuleCapabilityCatalogService $catalogService
    )
    {
    }

    /**
     *
     * @param Reseller $reseller
     * @param array $data
     * @return Tenant
     * @throws Exception
     */
    public function provision(Reseller $reseller, array $data): Tenant
    {
        if (!$reseller->isActive()) {
            throw new ResellerHubException(__('resellerhub::messages.errors.reseller_suspended'));
        }

        $subdomain = strtolower(trim($data['subdomain']));
        $this->validateSubdomain($subdomain);

        return DB::transaction(function () use ($reseller, $data, $subdomain) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            $months = array_key_exists('expires_in_months', $data)
                ? ($data['expires_in_months'] !== null ? (int)$data['expires_in_months'] : null)
                : 12;
            $expiresAt = ($months && $months > 0 && $months < 1200) ? Carbon::now()->addMonths($months) : null;

            $isCustomBranding = !empty($data['white_label_type']) && $data['white_label_type'] === 'custom';
            $customBrandingData = $data['branding'] ?? [];

            $brandLogo = $reseller->brand_logo;
            $brandFavicon = $reseller->brand_favicon;
            $brandPrimaryColor = ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5');

            if ($isCustomBranding) {
                if (!empty($data['uploaded_brand_logo'])) {
                    $brandLogo = $data['uploaded_brand_logo'];
                }
                if (!empty($data['uploaded_brand_favicon'])) {
                    $brandFavicon = $data['uploaded_brand_favicon'];
                }
                if (!empty($customBrandingData['brand_primary_color'])) {
                    $brandPrimaryColor = trim($customBrandingData['brand_primary_color']);
                }
            }

            $branding = [
                'is_custom' => $isCustomBranding,
                'brand_title' => ($isCustomBranding && !empty($customBrandingData['brand_title'])) ? trim($customBrandingData['brand_title']) : ($reseller->brand_title ?: $data['name']),
                'brand_primary_color' => $brandPrimaryColor,
                'brand_logo' => $brandLogo,
                'brand_favicon' => $brandFavicon,
                'brand_footer_text' => ($isCustomBranding && array_key_exists('brand_footer_text', $customBrandingData)) ? trim($customBrandingData['brand_footer_text']) : $reseller->brand_footer_text,
                'support_phone' => ($isCustomBranding && array_key_exists('support_phone', $customBrandingData)) ? trim($customBrandingData['support_phone']) : $reseller->support_phone,
                'support_email' => ($isCustomBranding && array_key_exists('support_email', $customBrandingData)) ? trim($customBrandingData['support_email']) : $reseller->support_email,
            ];

            $settings = $data['settings'] ?? [];
            $packageId = !empty($data['package_id']) ? (int)$data['package_id'] : null;
            $flattenFeatures = function ($raw): array {
                if (empty($raw)) return [];
                $list = [];
                foreach ((array)$raw as $val) {
                    if (is_array($val)) {
                        foreach ($val as $subVal) {
                            if (is_string($subVal) && $subVal !== '') {
                                $list[] = $subVal;
                            }
                        }
                    } elseif (is_string($val) && $val !== '') {
                        $list[] = $val;
                    }
                }
                return array_values(array_unique($list));
            };

            if (!empty($data['features'])) {
                $settings['features'] = $flattenFeatures($data['features']);
            } elseif ($packageId) {
                $pkg = ResellerPackage::where('reseller_id', $reseller->id)->where('is_active', true)->find($packageId);
                if ($pkg && !empty($pkg->features)) {
                    $settings['features'] = $flattenFeatures($pkg->features);
                }
            }

            $packageLimits = [];
            if ($packageId) {
                $pkg = ResellerPackage::where('reseller_id', $reseller->id)->where('is_active', true)->find($packageId);
                if ($pkg && !empty($pkg->limits)) {
                    $packageLimits = (array)$pkg->limits;
                }
            }

            if (isset($data['limits']) && is_array($data['limits'])) {
                $customLimits = [];
                foreach ($data['limits'] as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $customLimits[$k] = is_numeric($v) ? (int)$v : $v;
                    } else {
                        $customLimits[$k] = null; // Explicit unlimited override
                    }
                }
                $settings['limits'] = array_merge($packageLimits, $customLimits);
            } elseif (!empty($packageLimits)) {
                $settings['limits'] = $packageLimits;
            }

            $subAccountId = !empty($data['sub_account_id']) ? (int)$data['sub_account_id'] : null;
            $subAccount = null;
            if ($subAccountId) {
                $subAccount = ResellerSubAccount::where('reseller_id', $reseller->id)->find($subAccountId);
                if (!$subAccount) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.sub_account_invalid'));
                }
            }

            $tenantName = !empty($data['name'])
                ? trim($data['name'])
                : ($subAccount?->company_name ?: ($subAccount?->name ?: ($branding['brand_title'] ?: $subdomain)));

            $tenant = Tenant::create([
                'reseller_id' => $reseller->id,
                'package_id' => $packageId,
                'sub_account_id' => $subAccount?->id,
                'name' => $tenantName,
                'subdomain' => $subdomain,
                'custom_domain' => !empty($data['custom_domain']) ? strtolower(trim($data['custom_domain'])) : null,
                'status' => $data['status'] ?? Tenant::STATUS_ACTIVE,
                'plan_expires_at' => $expiresAt,
                'branding' => $branding,
                'settings' => $settings,
            ]);

            $adminName = $subAccount?->name ?: trim($data['admin_name'] ?? 'مدیر سامانه');
            $adminEmail = $subAccount?->email ?: (!empty($data['admin_email']) ? trim($data['admin_email']) : ($subdomain . '@crm.local'));
            $adminMobile = $subAccount?->mobile ?: (!empty($data['admin_mobile']) ? trim($data['admin_mobile']) : null);
            $rawPassword = !empty($data['password'])
                ? $data['password']
                : Str::random(12);

            $user = null;
            if ($subAccount && $subAccount->user_id) {
                // SECURITY: only reuse a user that already belongs to this tenant,
                // or an unbound user explicitly linked to the sub-account. Never
                // attach a user that belongs to another tenant.
                $user = User::where('tenant_id', $tenant->id)->find($subAccount->user_id);

                if (!$user) {
                    $candidate = User::find($subAccount->user_id);
                    if ($candidate && !$candidate->tenant_id) {
                        $user = $candidate;
                    }
                }
            }
            // Deliberately no global mobile/email lookup: invoice/sub-account
            // metadata is not proof of identity across resellers.

            if (!$user) {
                $user = User::create([
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'mobile' => $adminMobile,
                    'password' => Hash::make($rawPassword),
                    'tenant_id' => $tenant->id,
                ]);
            } else {
                if (!$user->tenant_id) {
                    $user->tenant_id = $tenant->id;
                    $user->save();
                }
            }

            // A tenant's admin is resolved through its sub-account / users.

            if ($subAccount) {
                if (!$subAccount->tenant_id) {
                    $subAccount->tenant_id = $tenant->id;
                }
                if (!$subAccount->user_id) {
                    $subAccount->user_id = $user->id;
                }
                if (!empty($settings['limits'])) {
                    $subAccount->limits = $settings['limits'];
                }
                $subAccount->save();
            } else {
                $newSub = ResellerSubAccount::create([
                    'reseller_id' => $reseller->id,
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'mobile' => $adminMobile,
                    'password' => Hash::make($rawPassword),
                    'company_name' => $tenantName,
                    'status' => ResellerSubAccount::STATUS_ACTIVE,
                    'limits' => $settings['limits'] ?? null,
                    'settings' => [
                        'auto_generated' => true,
                    ],
                ]);
                $tenant->update(['sub_account_id' => $newSub->id]);
            }

            $roleName = 'admin';
            $guard = config('auth.defaults.guard', 'web');
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                ['display_name' => 'مدیر سیستم']
            );
            if (!$user->hasRole($roleName)) {
                $user->assignRole($role);
            }

            WidgetSetting::firstOrCreate(
                ['role_id' => $role->id, 'widget_key' => 'calendar_widget'],
                ['is_active' => true]
            );

            // Assign specific feature permissions to the user if defined
            $featuresList = $settings['features'] ?? [];
            if (!empty($featuresList)) {
                foreach ($featuresList as $permName) {
                    try {
                        $user->givePermissionTo($permName);
                    } catch (Throwable) {
                        // Ignore permission if not registered in guard
                    }
                }
            }

            if (isset($data['modules'])) {
                $selectedModules = $data['modules'];
            } else {
                $selectedModules = Module::where('installed', true)
                    ->where('active', true)
                    ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])
                    ->pluck('slug')
                    ->toArray();
            }
            if (is_string($selectedModules)) {
                $selectedModules = array_filter(array_map('trim', explode(',', $selectedModules)));
            }

            foreach ($selectedModules as $modSlug) {
                $slug = strtolower(trim($modSlug));

                $isActiveModule = $data['modules_active'] ?? true;
                if ($reseller->canSellModule($slug)) {
                    TenantModule::create([
                        'tenant_id' => $tenant->id,
                        'module_slug' => $slug,
                        'is_active' => $isActiveModule,
                        'expires_at' => $expiresAt,
                    ]);
                }
            }

            // Auto-mark onboarding complete_profile step completed
            try {
                app(OnboardingStateService::class)->markCompleted(
                    $tenant->id,
                    'complete_profile',
                    ['source' => 'manual_provision', 'user_id' => $user->id]
                );
            } catch (Throwable) {
            }

            return $tenant;
        });
    }

    /**
     *
     * @param Reseller $reseller
     * @param array $tenantData
     * @param array $financialData
     * @param User|null $actor
     * @return array{tenant: Tenant, invoice: ?ResellerInvoice, order: ?ResellerOrder}
     * @throws Exception
     */
    public function provisionWithFinancials(
        Reseller $reseller,
        array    $tenantData,
        array    $financialData,
        ?User    $actor = null
    ): array
    {
        return DB::transaction(function () use ($reseller, $tenantData, $financialData, $actor) {
            $isExistingTenant = ($tenantData['tenant_mode'] ?? '') === 'existing';

            $shouldIssueInvoice = !empty($financialData['should_issue_invoice']);

            if ($isExistingTenant) {
                $tenant = Tenant::where('reseller_id', $reseller->id)->findOrFail($tenantData['existing_tenant_id']);
                if (!empty($tenantData['modules']) && is_array($tenantData['modules'])) {
                    $expiresInMonths = (int)($tenantData['expires_in_months'] ?? 12);
                    foreach ($tenantData['modules'] as $slug) {
                        TenantModule::updateOrCreate([
                            'tenant_id' => $tenant->id,
                            'module_slug' => $slug,
                        ], [
                            'is_active' => !$shouldIssueInvoice,
                            'installed_at' => now(),
                            'expires_at' => now()->addMonths($expiresInMonths),
                        ]);
                    }
                }
            } else {
                $lockedReseller = Reseller::where('id', $reseller->id)->lockForUpdate()->first();
                if (!$lockedReseller) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.reseller_not_found'));
                }
                if ($shouldIssueInvoice) {
                    $tenantData['status'] = Tenant::STATUS_ACTIVE;
                    $tenantData['modules_active'] = false;
                }
                $tenant = $this->provision($lockedReseller, $tenantData);
            }

            $invoice = null;
            $order = null;

            if (!empty($financialData['should_issue_invoice'])) {
                $items = $financialData['invoice_items'] ?? [];

                if (empty($items) && !empty($financialData['package_id'])) {
                    $pkg = ResellerPackage::where('reseller_id', $reseller->id)->where('is_active', true)->find($financialData['package_id']);
                    if (!$pkg) {
                        throw new ResellerHubException(__('resellerhub::messages.errors.package_invalid'));
                    }
                    $months = (int)($tenantData['expires_in_months'] ?? 12);
                    $unitPrice = $pkg->getPriceForMonths($months);
                    $durationLabel = reseller_duration_label($months);
                    $items[] = [
                        'item_type' => 'package',
                        'item_id' => 'package_' . $pkg->id,
                        'title' => "اشتراک {$durationLabel} پکیج «{$pkg->name}»",
                        'description' => "اشتراک {$durationLabel} سامانه CRM با پکیج {$pkg->name}",
                        'duration_months' => $months,
                        'quantity' => 1,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'tax_rate' => 0,
                    ];
                    if ((int)$pkg->setup_fee > 0) {
                        $items[] = [
                            'item_type' => 'setup_fee',
                            'item_id' => 'package_' . $pkg->id . '_setup',
                            'title' => "هزینه راه‌اندازی اولیه پکیج «{$pkg->name}»",
                            'description' => 'هزینه راه‌اندازی و کانفیگ اولیه سامانه',
                            'duration_months' => 0,
                            'quantity' => 1,
                            'unit_price' => (int)$pkg->setup_fee,
                            'discount_amount' => 0,
                            'tax_rate' => 0,
                        ];
                    }
                }

                $enableItemized = (bool)ResellerSetting::get($reseller->id, 'enable_module_itemized_pricing', false);
                $hasPackage = !empty($financialData['package_id']);

                if ($enableItemized || !$hasPackage) {
                    $resolvedModulePricings = null;
                    $resolvedCatalog = null;

                    foreach ($items as &$item) {
                        $itemType = $item['item_type'] ?? 'module';
                        $unitPrice = (int)($item['unit_price'] ?? 0);
                        $slug = $item['item_id'] ?? '';
                        $duration = (int)($item['duration_months'] ?? ($tenantData['expires_in_months'] ?? 12)) ?: 12;

                        if ($itemType === 'module' && $unitPrice <= 0 && !empty($slug)) {
                            if ($resolvedModulePricings === null) {
                                $resolvedModulePricings = ResellerModulePricing::allKeyedBySlug($reseller->id);
                                $resolvedCatalog = $this->catalogService->getCatalogForReseller($reseller)->keyBy(function ($c) {
                                    return Str::lower(data_get($c, 'slug', ''));
                                });
                            }
                            $sLower = Str::lower($slug);
                            $pricing = $resolvedModulePricings[$slug] ?? ($resolvedModulePricings[$sLower] ?? null);
                            $catMod = $resolvedCatalog[$sLower] ?? null;

                            $catMonthly = (int)data_get($catMod, 'base_monthly_price', 0);
                            $catQuarterly = (int)data_get($catMod, 'base_quarterly_price', 0);
                            $catSemi = (int)data_get($catMod, 'base_semi_annual_price', 0);
                            $catYearly = (int)data_get($catMod, 'base_yearly_price', 0);

                            $pricingMonthly = (int)data_get($pricing, 'monthly_price', 0);
                            $pricingQuarterly = (int)data_get($pricing, 'quarterly_price', 0);
                            $pricingSemi = (int)data_get($pricing, 'semi_annual_price', 0);
                            $pricingYearly = (int)data_get($pricing, 'yearly_price', 0);

                            $mP = $pricingMonthly > 0 ? $pricingMonthly : $catMonthly;
                            $qP = $pricingQuarterly > 0 ? $pricingQuarterly : ($catQuarterly ?: ($mP * 3));
                            $sP = $pricingSemi > 0 ? $pricingSemi : ($catSemi ?: ($mP * 6));
                            $yP = $pricingYearly > 0 ? $pricingYearly : ($catYearly ?: ($mP * 12));

                            $resolvedPrice = match ($duration) {
                                1 => $mP,
                                3 => $qP ?: ($mP * 3),
                                6 => $sP ?: ($mP * 6),
                                12 => $yP ?: ($mP * 12),
                                default => $duration > 12 ? (int)round((($yP ?: ($mP * 12)) / 12) * $duration) : ($mP * $duration),
                            };

                            if ($resolvedPrice > 0) {
                                $item['unit_price'] = $resolvedPrice;
                            }
                        }
                    }
                    unset($item);
                }

                $taxMode = $financialData['tax_mode'] ?? 'invoice';
                $taxRate = (float)($financialData['tax_rate'] ?? 0);

                $invoiceData = [
                    'tenant_id' => $tenant->id,
                    'package_id' => $financialData['package_id'] ?? null,
                    'invoice_number' => $financialData['invoice_number'] ?: null,
                    'sub_account_name' => $financialData['sub_account_name'] ?? ($tenantData['admin_name'] ?? ($tenant->subAccount?->name ?? $tenant->name)),
                    'sub_account_mobile' => $financialData['sub_account_mobile'] ?? ($tenantData['admin_mobile'] ?? ($tenant->subAccount?->mobile ?? ($tenant->subAccount?->user?->mobile ?? ''))),
                    'sub_account_email' => $financialData['sub_account_email'] ?? ($tenantData['admin_email'] ?? ($tenant->subAccount?->email ?? ($tenant->subAccount?->user?->email ?? ''))),
                    'issue_date' => $financialData['invoice_issue_date'] ?? null,
                    'due_date' => $financialData['invoice_due_date'] ?? null,
                    'type' => InvoiceType::OFFICIAL->value,
                    'tax_percent' => $taxRate,
                    'discount_type' => 'fixed',
                    'discount_value' => (int)($financialData['invoice_discount_amount'] ?? 0),
                    'status' => InvoiceStatus::UNPAID->value,
                    'terms' => $financialData['invoice_terms'] ?? null,
                    'notes' => $financialData['invoice_notes'] ?? null,
                    'items' => $items,
                ];

                $invoice = $this->billingService->createInvoice($reseller, $invoiceData, $actor);
                $invoice = $this->billingService->applyTaxModeToInvoice($invoice, $taxMode, $taxRate);
                $invoice->updatePaymentStatus(true);
                $order = $invoice->order ?: $this->billingService->convertInvoiceToOrder($invoice);
                $order = $order ?: $invoice->order;

                if (!$invoice->isPaid()) {
                    if ($order && $order->status !== ResellerOrder::STATUS_PENDING) {
                        $order->update(['status' => ResellerOrder::STATUS_PENDING]);
                    }

                    $invoiceModuleSlugs = collect($items)
                        ->where('item_type', 'module')
                        ->map(fn($it) => $it['meta']['module_slug'] ?? $it['item_key'] ?? null)
                        ->filter()
                        ->all();

                    if (!empty($invoiceModuleSlugs)) {
                        TenantModule::where('tenant_id', $tenant->id)
                            ->whereIn('module_slug', $invoiceModuleSlugs)
                            ->update(['is_active' => false]);
                    } elseif (!$tenant->plan_expires_at) {
                        TenantModule::where('tenant_id', $tenant->id)->update(['is_active' => false]);
                    }
                }
            }

            return [
                'tenant' => $tenant,
                'invoice' => $invoice,
                'order' => $order,
            ];
        });
    }

    public function validateSubdomain(string $subdomain): void
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        if (strlen($subdomain) < 3 || strlen($subdomain) > 63) {
            throw new ResellerHubException(__('resellerhub::messages.errors.subdomain_length'));
        }

        if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$/', $subdomain)) {
            throw new ResellerHubException(__('resellerhub::messages.errors.subdomain_format'));
        }

        if ($tenantManager->isReservedSubdomain($subdomain)) {
            throw new ResellerHubException(__('resellerhub::messages.errors.subdomain_reserved', ['subdomain' => $subdomain]));
        }

        if (Tenant::where('subdomain', $subdomain)->exists()) {
            throw new ResellerHubException(__('resellerhub::messages.errors.subdomain_taken', ['subdomain' => $subdomain]));
        }
    }

    /**
     * Dynamically resolve Persian title of a module slug (SSOT via ResellerHelper).
     */
    public static function resolveModuleTitle(string $slug): string
    {
        return ResellerHelper::getModuleTitle($slug);
    }

    /**
     * Generate a secure one-time SSO login URL to log into the tenant panel as its admin.
     *
     * @deprecated Use {@see createSsoLoginUrl()} instead. Kept as a thin alias so
     *             existing callers keep working; it no longer mutates the tenant model.
     */
    public function generateLoginUrl(Tenant $tenant, ?User $asUser = null, ?User $impersonator = null): string
    {
        return $this->createSsoLoginUrl($tenant, $asUser, $impersonator);
    }


    public function createSsoLoginUrl(Tenant $tenant, ?User $asUser = null, ?User $impersonator = null): string
    {
        $user = $this->ensureAdminUser($tenant, $asUser);
        $token = $this->issueSsoToken($tenant, $user, $impersonator);

        return app(TenantSubdomainUrl::class)->for(
            $tenant,
            'reseller/sso-login?token=' . $token
        );
    }


    public function ensureAdminUser(Tenant $tenant, ?User $asUser = null): User
    {
        if ($asUser) {
            return $asUser;
        }

        $subAccount = $tenant->subAccount;
        if ($subAccount?->user && (int)$subAccount->user->tenant_id === (int)$tenant->id) {
            return $subAccount->user;
        }

        $admin = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->first();

        if ($admin) {
            return $admin;
        }

        $adminName = $subAccount?->name ?: $tenant->name;
        $adminEmail = $subAccount?->email ?: ($tenant->subdomain . '@crm.local');
        $adminMobile = $subAccount?->mobile;

        $user = null;
        if (!empty($adminMobile)) {
            $user = User::where('tenant_id', $tenant->id)->where('mobile', $adminMobile)->first();
        }
        if (!$user && !empty($adminEmail)) {
            $user = User::where('tenant_id', $tenant->id)->where('email', $adminEmail)->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'mobile' => $adminMobile,
                'password' => Hash::make(Str::random(16)),
                'tenant_id' => $tenant->id,
            ]);
        } elseif (!$user->tenant_id) {
            $user->tenant_id = $tenant->id;
            $user->save();
        }

        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => config('auth.defaults.guard', 'web')],
            ['display_name' => 'مدیر سیستم']
        );
        $user->assignRole($role);

        return $user;
    }

    protected function issueSsoToken(Tenant $tenant, User $user, ?User $impersonator = null): string
    {
        $token = Str::random(64);

        cache()->put("tenant_sso_{$token}", [
            'tenant_id' => (int)$tenant->id,
            'user_id' => (int)$user->id,
            'impersonator_id' => $impersonator?->id ?? (auth()->check() ? auth()->id() : null),
            'ip_address' => request()->ip(),
            'user_agent_hash' => md5((string)request()->userAgent()),
            'created_at' => now()->timestamp,
        ], now()->addSeconds($this->ssoTtl()));

        return $token;
    }

    /**
     * SSO token lifetime in seconds.
     */
    protected function ssoTtl(): int
    {
        return max(30, (int)config('resellerhub.sso.token_ttl_seconds', 120));
    }
}

