<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Tenant;
use App\Services\Modules\ModuleMenuService;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\StorePackageRequest;
use Modules\ResellerHub\App\Http\Requests\UpdatePackageRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Morilog\Jalali\Jalalian;

class ResellerPackageController extends Controller
{
    use HasReseller, HasResellerSorting;

    protected function getAvailableModules(Reseller $reseller): Collection
    {
        $menuService = app(ModuleMenuService::class);
        $systemModules = Module::where('installed', true)
            ->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])
            ->get();

        return $systemModules->map(static function ($mod) use ($menuService, $reseller) {
            $title = $menuService->resolveModuleGroupTitle($mod->slug, $mod->name);
            return (object)[
                'name' => $mod->name,
                'slug' => $mod->slug,
                'title' => $title ?: $mod->name,
                'description' => $mod->description ?? null,
                'is_allowed' => $reseller->canSellModule($mod->slug) || $reseller->canSellModule($mod->name),
            ];
        })->values();
    }

    protected function resolvePricingData(array $validated): array
    {
        $priceType = $validated['price_type'] ?? 'subscription';

        $pricing = [
            'price_type' => $priceType,
            'active_periods' => [],
            'period_prices' => null,
            'monthly_price' => 0,
            'quarterly_price' => 0,
            'semi_annual_price' => 0,
            'yearly_price' => 0,
            'one_time_price' => 0,
            'setup_fee' => 0,
            'discount_type' => 'amount',
            'discount_value' => 0,
            'discount_starts_at' => null,
            'discount_expires_at' => null,
        ];

        // A package can be free in its recurring price and still have a
        // one-time setup fee. Setup fees are independent of price_type.
        $pricing['setup_fee'] = max(0, (int)($validated['setup_fee'] ?? 0));

        if ($priceType === 'free') {
            return $pricing;
        }

        $pricing['discount_type'] = 'amount';
        $pricing['discount_value'] = max(0, (int)($validated['discount_value'] ?? 0));
        $pricing['discount_starts_at'] = $validated['discount_starts_at'] ?? null;
        $pricing['discount_expires_at'] = $validated['discount_expires_at'] ?? null;

        if ($priceType === 'subscription') {
            $pricing['period_discounts'] = !empty($validated['period_discounts']) ? $validated['period_discounts'] : null;

            $rawActive = $validated['active_periods'] ?? [];
            $activePeriods = [];
            foreach ($rawActive as $p) {
                $m = ResellerPackage::resolveMonthFromPeriodKey($p);
                if ($m >= 1) {
                    $activePeriods[] = ResellerPackage::resolvePeriodKeyFromMonth($m);
                }
            }
            $pricing['active_periods'] = array_values(array_unique($activePeriods));

            $periodPrices = $validated['period_prices'] ?? [];
            if (!is_array($periodPrices)) {
                $periodPrices = [];
            }

            // Sync with all active periods
            $cleanPeriodPrices = [];
            foreach ($pricing['active_periods'] as $periodKey) {
                $m = ResellerPackage::resolveMonthFromPeriodKey($periodKey);
                $pVal = (int)($periodPrices[(string)$m] ?? ($periodPrices[$periodKey] ?? ($validated[$periodKey] ?? 0)));
                if ($pVal > 0) {
                    $cleanPeriodPrices[(string)$m] = $pVal;
                }
            }
            $pricing['period_prices'] = !empty($cleanPeriodPrices) ? $cleanPeriodPrices : null;

            // Legacy backward-compatibility columns
            $pricing['monthly_price'] = $cleanPeriodPrices['1'] ?? 0;
            $pricing['quarterly_price'] = $cleanPeriodPrices['3'] ?? 0;
            $pricing['semi_annual_price'] = $cleanPeriodPrices['6'] ?? 0;
            $pricing['yearly_price'] = $cleanPeriodPrices['12'] ?? 0;
        }

        if ($priceType === 'one_time') {
            $pricing['one_time_price'] = max(0, (int)($validated['one_time_price'] ?? 0));
        }

        return $pricing;
    }

    public function index(Request $request): View
    {
        $reseller = $this->getReseller();

        $packageQuery = ResellerPackage::forReseller($reseller->id)->withCount('tenants');

        if ($search = trim((string)$request->input('search', ''))) {
            $packageQuery->where(static function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $this->applySorting($packageQuery, $request, [
            'name' => 'name',
            'price' => 'monthly_price',
            'monthly_price' => 'monthly_price',
        ], defaultColumn: 'id');

        $statusFilter = (string)$request->input('status', '');
        if (in_array($statusFilter, ['active', 'inactive'], true)) {
            if ($statusFilter === 'active') {
                $packageQuery->active();
            } else {
                $packageQuery->inactive();
            }
        }

        $priceTypeFilter = (string)$request->input('price_type', '');
        if ($priceTypeFilter === 'free') {
            $packageQuery->where('is_free', true);
        } elseif ($priceTypeFilter === 'subscription') {
            $packageQuery->where('is_free', false)->where('price_type', 'subscription');
        } elseif ($priceTypeFilter === 'one_time') {
            $packageQuery->where('is_free', false)->where('price_type', 'one_time');
        }

        $packages = $packageQuery->get();
        $totalCount = ResellerPackage::forReseller($reseller->id)->count();
        $activeCount = ResellerPackage::forReseller($reseller->id)->active()->count();
        $inactiveCount = ResellerPackage::forReseller($reseller->id)->inactive()->count();
        $connectedTenantsCount = Tenant::whereNotNull('package_id')
            ->whereIn('package_id', ResellerPackage::forReseller($reseller->id)->select('id'))
            ->count();
        $availableModules = $this->getAvailableModules($reseller);
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);

        $defaultDuration = (int)ResellerSetting::get($reseller->id, 'default_plan_duration_months', 12);
        $baseDomain = ResellerHelper::getBaseDomain();

        $currentUser = Auth::user();
        $isSuperAdmin = method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');

        $resellersList = collect([]);
        if ($isSuperAdmin) {
            $resellersList = Reseller::with('user')->orderBy('company_name')->get()->map(static function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->company_name ?: ($r->user?->name ?? __('resellerhub::messages.common.reseller_id', ['id' => $r->id])),
                    'brand_title' => $r->brand_title ?: ($r->user?->name ?? ''),
                    'user_email' => $r->user?->email ?? '',
                    'user_mobile' => $r->user?->mobile ?? '',
                    'allowed_slugs' => $r->allowedModuleSlugs(),
                ];
            })->values();
        }

        $currency = ResellerSetting::getCurrencyForReseller($reseller->id);
        $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($reseller->id);

        $financialSettings = ResellerSetting::allForReseller($reseller->id);
        $invoiceNumbering = [
            'prefix' => (string)($financialSettings['invoice_prefix'] ?? 'INV-'),
            'middle' => (string)($financialSettings['invoice_middle_prefix'] ?? reseller_jalali_year()),
            'suffix' => (string)($financialSettings['invoice_suffix'] ?? ''),
            'padding' => (int)($financialSettings['invoice_padding'] ?? 4),
            'auto' => !empty($financialSettings['invoice_auto']),
            'tax_mode' => (string)($financialSettings['tax_mode'] ?? 'invoice'),
            'tax_rate' => (float)($financialSettings['default_tax_rate'] ?? 10),
            'terms' => (string)($financialSettings['invoice_terms'] ?? ''),
            'footer' => (string)($financialSettings['invoice_footer_note'] ?? ''),
        ];

        $subAccounts = ResellerSubAccount::where('reseller_id', $reseller->id)
            ->where('status', ResellerSubAccount::STATUS_ACTIVE)
            ->withCount('tenants')
            ->latest()
            ->get()
            ->map(static function ($sa) {
                return [
                    'id' => $sa->id,
                    'name' => $sa->name,
                    'email' => $sa->email,
                    'mobile' => $sa->mobile,
                    'company_name' => $sa->company_name,
                    'tenants_count' => $sa->tenants_count,
                    'label' => $sa->name . ' (' . $sa->mobile . ')' . ($sa->company_name ? ' - ' . $sa->company_name : ''),
                ];
            });

        return view('resellerhub::user.packages.index', compact(
            'reseller',
            'packages',
            'availableModules',
            'catalog',
            'baseDomain',
            'defaultDuration',
            'isSuperAdmin',
            'resellersList',
            'currency',
            'currencyLabel',
            'subAccounts',
            'financialSettings',
            'invoiceNumbering',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'connectedTenantsCount',
            'statusFilter'
        ));
    }

    public function create(): View
    {
        $reseller = $this->getReseller();
        $availableModules = $this->getAvailableModules($reseller);
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);
        $currency = ResellerSetting::getCurrencyForReseller($reseller->id);
        $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($reseller->id);

        return view('resellerhub::user.packages.create', compact('reseller', 'availableModules', 'catalog', 'currency', 'currencyLabel'));
    }

    public function store(StorePackageRequest $request): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();
        $validated = $request->validated();

        $allowedModules = array_values(array_filter($validated['modules'], static function ($slug) use ($reseller) {
            return $reseller->canSellModule($slug);
        }));

        if (empty($allowedModules)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('resellerhub::messages.packages.no_allowed_modules'),
                ], 422);
            }
            return back()->withInput()->with('error', __('resellerhub::messages.packages.no_allowed_modules'));
        }

        $features = $validated['features'] ?? [];
        if (is_string($features)) {
            $features = json_decode($features, true) ?: [];
        }
        $features = array_values(array_unique(array_filter((array)$features)));

        $limits = $validated['limits'] ?? [];
        if (is_array($limits)) {
            $limits = array_filter($limits, static fn($v) => $v !== null && $v !== '');
            $limits = array_map(static fn($v) => max(0, (int)$v), $limits);
        } else {
            $limits = null;
        }

        $pricing = $this->resolvePricingData($validated);

        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $package = ResellerPackage::create(array_merge([
            'reseller_id' => $reseller->id,
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'icon' => $validated['icon'] ?? 'cube',
            'color' => $validated['color'] ?? 'indigo',
            'is_active' => $isActive,
            'modules' => $allowedModules,
            'features' => $features,
            'limits' => !empty($limits) ? $limits : null,
        ], $pricing));

        $msg = __('resellerhub::messages.packages.created_success', ['name' => $package->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'package' => $package,
            ]);
        }

        return redirect()->route('reseller.packages.show', $package->id)
            ->with('success', $msg);
    }

    public function show(ResellerPackage $package): View
    {
        $reseller = $this->getReseller();

        if ((int)$package->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $availableModules = $this->getAvailableModules($reseller);
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);
        $modulesMap = $availableModules->keyBy('slug');

        $defaultDuration = (int)ResellerSetting::get($reseller->id, 'default_plan_duration_months', 12);
        $baseDomain = ResellerHelper::getBaseDomain();

        $currentUser = Auth::user();
        $isSuperAdmin = method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');

        $resellersList = collect([]);
        if ($isSuperAdmin) {
            $resellersList = Reseller::with('user')->orderBy('company_name')->get()->map(static function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->company_name ?: ($r->user?->name ?? __('resellerhub::messages.common.reseller_id', ['id' => $r->id])),
                    'brand_title' => $r->brand_title ?: ($r->user?->name ?? ''),
                    'user_email' => $r->user?->email ?? '',
                    'user_mobile' => $r->user?->mobile ?? '',
                    'allowed_slugs' => $r->allowedModuleSlugs(),
                ];
            })->values();
        }

        $package->loadCount('tenants');

        $tenants = Tenant::where('package_id', $package->id)
            ->with(['subAccount', 'owner', 'modules'])
            ->latest()
            ->get();

        $currency = ResellerSetting::getCurrencyForReseller($reseller->id);
        $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($reseller->id);

        $financialSettings = ResellerSetting::allForReseller($reseller->id);
        $invoiceNumbering = [
            'prefix' => (string)($financialSettings['invoice_prefix'] ?? 'INV-'),
            'middle' => (string)($financialSettings['invoice_middle_prefix'] ?? reseller_jalali_year()),
            'suffix' => (string)($financialSettings['invoice_suffix'] ?? ''),
            'padding' => (int)($financialSettings['invoice_padding'] ?? 4),
            'auto' => !empty($financialSettings['invoice_auto']),
            'tax_mode' => (string)($financialSettings['tax_mode'] ?? 'invoice'),
            'tax_rate' => (float)($financialSettings['default_tax_rate'] ?? 10),
            'terms' => (string)($financialSettings['invoice_terms'] ?? ''),
            'footer' => (string)($financialSettings['invoice_footer_note'] ?? ''),
        ];

        $subAccounts = ResellerSubAccount::where('reseller_id', $reseller->id)
            ->where('status', ResellerSubAccount::STATUS_ACTIVE)
            ->withCount('tenants')
            ->latest()
            ->get()
            ->map(static function ($sa) {
                return [
                    'id' => $sa->id,
                    'name' => $sa->name,
                    'email' => $sa->email,
                    'mobile' => $sa->mobile,
                    'company_name' => $sa->company_name,
                    'tenants_count' => $sa->tenants_count,
                    'label' => $sa->name . ' (' . $sa->mobile . ')' . ($sa->company_name ? ' - ' . $sa->company_name : ''),
                ];
            });

        return view('resellerhub::user.packages.show', compact(
            'reseller',
            'package',
            'modulesMap',
            'availableModules',
            'catalog',
            'baseDomain',
            'defaultDuration',
            'isSuperAdmin',
            'resellersList',
            'tenants',
            'currency',
            'currencyLabel',
            'subAccounts',
            'financialSettings',
            'invoiceNumbering'
        ));
    }

    public function edit(ResellerPackage $package): View
    {
        $reseller = $this->getReseller();

        if ((int)$package->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $availableModules = $this->getAvailableModules($reseller);
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);
        $currency = ResellerSetting::getCurrencyForReseller($reseller->id);
        $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($reseller->id);

        return view('resellerhub::user.packages.edit', compact('reseller', 'package', 'availableModules', 'catalog', 'currency', 'currencyLabel'));
    }

    public function update(UpdatePackageRequest $request, ResellerPackage $package): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        if ((int)$package->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $validated = $request->validated();

        $allowedModules = array_values(array_filter($validated['modules'], static function ($slug) use ($reseller) {
            return $reseller->canSellModule($slug);
        }));

        if (empty($allowedModules)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('resellerhub::messages.packages.no_allowed_modules'),
                ], 422);
            }
            return back()->withInput()->with('error', __('resellerhub::messages.packages.no_allowed_modules'));
        }

        $features = $validated['features'] ?? [];
        if (is_string($features)) {
            $features = json_decode($features, true) ?: [];
        }
        $features = array_values(array_unique(array_filter((array)$features)));

        $limits = $validated['limits'] ?? [];
        if (is_array($limits)) {
            $limits = array_filter($limits, static fn($v) => $v !== null && $v !== '');
            $limits = array_map(static fn($v) => max(0, (int)$v), $limits);
        } else {
            $limits = null;
        }

        $pricing = $this->resolvePricingData($validated);

        $isActive = $request->has('is_active') ? $request->boolean('is_active') : (bool)$package->is_active;

        $package->update(array_merge([
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'icon' => $validated['icon'] ?? $package->icon,
            'color' => $validated['color'] ?? $package->color,
            'is_active' => $isActive,
            'modules' => $allowedModules,
            'features' => $features,
            'limits' => !empty($limits) ? $limits : null,
        ], $pricing));

        $msg = __('resellerhub::messages.packages.updated_success', ['name' => $package->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'package' => $package,
            ]);
        }

        return redirect()->route('reseller.packages.show', $package->id)
            ->with('success', $msg);
    }

    public function toggleStatus(Request $request, ResellerPackage $package): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        if ((int)$package->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $newStatus = !(bool)$package->is_active;
        $package->is_active = $newStatus;
        $package->save();

        $msg = $newStatus
            ? __('resellerhub::messages.packages.activated_success', ['name' => $package->name])
            : __('resellerhub::messages.packages.deactivated_success', ['name' => $package->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => $msg,
                'package' => $package,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function destroy(Request $request, ResellerPackage $package): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        if ((int)$package->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $activeTenantsCount = $package->tenants()->count();
        if ($activeTenantsCount > 0) {
            $msg = __('resellerhub::messages.packages.cannot_delete_has_tenants', [
                'name' => $package->name,
                'count' => reseller_fa_num($activeTenantsCount),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Check if package is default registration package
        $isDefault = (int)ResellerSetting::get($reseller->id, 'default_package_id') === (int)$package->id;
        if ($isDefault) {
            $msg = __('resellerhub::messages.packages.cannot_delete_default');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $name = $package->name;
        $package->delete();

        $msg = __('resellerhub::messages.packages.deleted_success', ['name' => $name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('reseller.packages.index')
            ->with('success', $msg);
    }
}
