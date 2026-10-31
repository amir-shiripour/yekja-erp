<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Services\Modules\ModuleMenuService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\QuickStoreResellerSubAccountRequest;
use Modules\ResellerHub\App\Http\Requests\StoreResellerSubAccountRequest;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerSubAccountRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerPagination;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Modules\ResellerHub\App\Traits\HasSubAccountSearch;


class ResellerSubAccountController extends Controller
{
    use HasReseller, HasSubAccountSearch, HasResellerSorting, HasResellerPagination;

    /**
     * Display a listing of sub-accounts.
     */
    public function index(Request $request): View
    {
        $reseller = $this->getReseller();
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        $baseQuery = ResellerSubAccount::query();

        if ($isSuperAdmin) {
            $selectedResellerId = $request->input('reseller_id');
            if ($selectedResellerId && $selectedResellerId !== 'all') {
                $baseQuery->where('reseller_id', $selectedResellerId);
            }
        } else {
            $baseQuery->where('reseller_id', $reseller->id);
        }

        // Calculate summary counters before applying search and status filters
        $totalSubAccounts = (clone $baseQuery)->count();
        $activeSubAccounts = (clone $baseQuery)->where('status', ResellerSubAccount::STATUS_ACTIVE)->count();
        $suspendedSubAccounts = (clone $baseQuery)->where('status', ResellerSubAccount::STATUS_SUSPENDED)->count();
        $connectedTenants = (clone $baseQuery)->whereNotNull('tenant_id')->distinct('tenant_id')->count('tenant_id');

        // Apply Search
        $this->applySubAccountSearch($baseQuery, $request->input('search'));

        // Apply Status filter
        $statusFilter = $request->input('status');
        if ($statusFilter && in_array($statusFilter, [ResellerSubAccount::STATUS_ACTIVE, ResellerSubAccount::STATUS_SUSPENDED], true)) {
            $baseQuery->where('status', $statusFilter);
        }

        // Apply Source filter (online vs manual)
        $this->applySourceFilter($baseQuery, $request->input('source'));

        $this->applySorting($baseQuery, $request, [
            'name' => 'reseller_sub_accounts.name',
            'subaccount' => 'reseller_sub_accounts.name',
            'subaccount_name' => 'reseller_sub_accounts.name',
            'company' => 'reseller_sub_accounts.company_name',
            'company_name' => 'reseller_sub_accounts.company_name',
            'tenant' => fn($q, $dir) => $q->orderByRaw("reseller_sub_accounts.tenant_id IS NULL, reseller_sub_accounts.tenant_id {$dir}"),
            'tenant_id' => fn($q, $dir) => $q->orderByRaw("reseller_sub_accounts.tenant_id IS NULL, reseller_sub_accounts.tenant_id {$dir}"),
            'reseller' => 'reseller_sub_accounts.reseller_id',
            'reseller_id' => 'reseller_sub_accounts.reseller_id',
            'status' => 'reseller_sub_accounts.status',
            'date' => 'reseller_sub_accounts.created_at',
            'created_at' => 'reseller_sub_accounts.created_at',
        ], defaultColumn: 'reseller_sub_accounts.id');

        $perPage = $this->resolvePerPage($request, default: 10);

        $subAccounts = $baseQuery->with([
            'reseller.user',
            'tenant' => static function ($q) {
                $q->with('modules');
            },
            'user'
        ])
            ->paginate($perPage)
            ->withQueryString();

        $resellersList = collect([]);
        if ($isSuperAdmin) {
            $resellersList = Reseller::with('user')->orderBy('company_name')->get();
        }

        return view('resellerhub::user.subaccounts.index', compact(
            'reseller',
            'subAccounts',
            'totalSubAccounts',
            'activeSubAccounts',
            'suspendedSubAccounts',
            'connectedTenants',
            'isSuperAdmin',
            'resellersList'
        ));
    }

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
                'is_allowed' => $reseller->canSellModule($mod->slug) || $reseller->canSellModule($mod->name),
            ];
        })->values();
    }


    public function create(): View
    {
        $reseller = $this->getReseller();
        $availableModules = $this->getAvailableModules($reseller);
        $packages = ResellerPackage::forReseller($reseller->id)->active()->get();
        return view('resellerhub::user.subaccounts.create', compact('reseller',
            'availableModules', 'packages'));
    }

    /**
     * Helper to normalize Iranian mobile phone numbers.
     */
    protected function normalizeMobile(?string $mobile): string
    {
        return ResellerHelper::normalizeMobile($mobile);
    }

    /**
     * Auto-generate a unique system email for the sub-account.
     */
    protected function generateUniqueEmail(string $mobile): string
    {
        $cleanDigits = preg_replace('/\D/', '', $mobile);
        $base = $cleanDigits ?: ('sub_' . Str::lower(Str::random(6)));
        $email = $base . '@sub.crm';

        $counter = 1;
        while (ResellerSubAccount::where('email', $email)->exists() || User::where('email', $email)->exists()) {
            $email = $base . '_' . $counter . '@sub.crm';
            $counter++;
        }

        return $email;
    }

    public function store(StoreResellerSubAccountRequest $request): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();
        $validated = $request->validated();

        $generatedEmail = $this->generateUniqueEmail($validated['mobile']);
        $rawPassword = Str::random(10);

        $subAccount = new ResellerSubAccount();
        $subAccount->reseller_id = $reseller->id;
        $subAccount->name = $validated['name'];
        $subAccount->email = $generatedEmail;
        $subAccount->mobile = $validated['mobile'];
        $subAccount->company_name = $validated['company_name'] ?? null;
        $subAccount->password = bcrypt($rawPassword);
        $subAccount->status = ResellerSubAccount::STATUS_ACTIVE;
        $subAccount->settings = [
            'auto_generated' => true,
        ];
        $limits = $request->input('limits', []);
        if (is_array($limits)) {
            $limits = array_filter($limits, static fn($v) => $v !== null && $v !== '');
            $limits = array_map(static fn($v) => max(0, (int)$v), $limits);
            // سقف تعداد سامانه/فروش برای زیرمجموعه حذف شده است؛ کلید قدیمی هرگز ذخیره نشود.
            unset($limits['tenants']);
        } else {
            $limits = null;
        }

        $subAccount->limits = !empty($limits) ? $limits : null;
        $subAccount->max_invoices = isset($limits[QuotaKey::Invoices->value]) ? $limits[QuotaKey::Invoices->value] : ($validated['max_invoices'] ?? null);
        $subAccount->save();

        $msg = __('resellerhub::messages.subaccounts.created_success', ['name' => $subAccount->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'sub_account' => [
                    'id' => $subAccount->id,
                    'name' => $subAccount->name,
                    'mobile' => $subAccount->mobile,
                    'email' => $subAccount->email,
                    'company_name' => $subAccount->company_name,
                    'initial_password' => $rawPassword,
                    'tenants_count' => 0,
                    'label' => $subAccount->name . ' (' . $subAccount->mobile . ')' . ($subAccount->company_name ? (' - ' . $subAccount->company_name) : ''),
                ],
                'redirect_url' => route('reseller.sub-accounts.index'),
            ]);
        }

        return redirect()->route('reseller.sub-accounts.index')
            ->with('success', $msg);
    }


    public function quickStore(QuickStoreResellerSubAccountRequest $request): JsonResponse
    {
        $reseller = $this->getReseller();
        $validated = $request->validated();

        $generatedEmail = $this->generateUniqueEmail($validated['mobile']);
        $rawPassword = Str::random(10);

        $subAccount = new ResellerSubAccount();
        $subAccount->reseller_id = $reseller->id;
        $subAccount->name = $validated['name'];
        $subAccount->email = $generatedEmail;
        $subAccount->mobile = $validated['mobile'];
        $subAccount->company_name = $validated['company_name'] ?? null;
        $subAccount->password = bcrypt($rawPassword);
        $subAccount->status = ResellerSubAccount::STATUS_ACTIVE;
        $subAccount->settings = [
            'auto_generated' => true,
        ];
        $subAccount->save();

        return response()->json([
            'success' => true,
            'message' => __('resellerhub::messages.subaccounts.created_success', ['name' => $subAccount->name]),
            'sub_account' => [
                'id' => $subAccount->id,
                'name' => $subAccount->name,
                'mobile' => $subAccount->mobile,
                'email' => $subAccount->email,
                'company_name' => $subAccount->company_name,
                'initial_password' => $rawPassword,
                'tenants_count' => 0,
                'label' => $subAccount->name . ' (' . $subAccount->mobile . ')' . ($subAccount->company_name ? (' - ' . $subAccount->company_name) : ''),
            ]
        ]);
    }

    /**
     * Show the form for editing a sub-account.
     */
    public function edit(ResellerSubAccount $subAccount): View
    {
        $this->authorizeSubAccountAccess($subAccount);
        $reseller = $this->getReseller();

        $availableModules = $this->getAvailableModules($reseller);
        $packages = ResellerPackage::forReseller($reseller->id)->get();
        return view('resellerhub::user.subaccounts.edit', compact('reseller', 'subAccount', 'availableModules', 'packages'));
    }

    public function update(UpdateResellerSubAccountRequest $request, ResellerSubAccount $subAccount): JsonResponse|RedirectResponse
    {
        $this->authorizeSubAccountAccess($subAccount);

        $validated = $request->validated();

        $limits = $request->input('limits', []);
        if (is_array($limits)) {
            $limits = array_filter($limits, static fn($v) => $v !== null && $v !== '');
            $limits = array_map(static fn($v) => max(0, (int)$v), $limits);
            // سقف تعداد سامانه/فروش برای زیرمجموعه حذف شده است؛ کلید قدیمی هرگز ذخیره نشود.
            unset($limits['tenants']);
        } else {
            $limits = null;
        }

        $subAccount->name = $validated['name'];
        $subAccount->mobile = $validated['mobile'];
        $subAccount->company_name = $validated['company_name'] ?? null;
        $subAccount->limits = !empty($limits) ? $limits : null;
        $subAccount->max_invoices = isset($limits[QuotaKey::Invoices->value]) ? $limits[QuotaKey::Invoices->value] : ($validated['max_invoices'] ?? null);
        $subAccount->save();

        $msg = __('resellerhub::messages.subaccounts.updated_success', ['name' => $subAccount->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'sub_account' => $subAccount,
                'redirect_url' => route('reseller.sub-accounts.index'),
            ]);
        }

        return redirect()->route('reseller.sub-accounts.index')
            ->with('success', $msg);
    }

    /**
     * Search for sub-accounts for the tenant creation dropdown.
     */
    public function search(Request $request): JsonResponse
    {
        $reseller = $this->getReseller();
        $term = trim((string)$request->input('q', ''));

        $subAccounts = ResellerSubAccount::query()
            ->where('reseller_id', $reseller->id)
            ->where('status', ResellerSubAccount::STATUS_ACTIVE)
            ->search($term)
            ->withCount('tenants')
            ->select('id', 'name', 'email', 'mobile', 'company_name', 'created_at')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $subAccounts->map(static fn($sa) => [
                'id' => $sa->id,
                'name' => $sa->name,
                'email' => $sa->email,
                'mobile' => $sa->mobile,
                'company_name' => $sa->company_name,
                'tenants_count' => $sa->tenants_count,
                'label' => $sa->name . ' (' . $sa->mobile . ')' . ($sa->company_name ? (' - ' . $sa->company_name) : ''),
                'value' => $sa->id,
            ])
        ]);
    }


    public function show(Request $request, ResellerSubAccount $subAccount): JsonResponse|View
    {
        $this->authorizeSubAccountAccess($subAccount);
        $reseller = $this->getReseller();

        $subAccount->load([
            'reseller.user',
            'tenants.modules',
            'tenant.modules',
            'user'
        ]);

        $allTenants = $subAccount->tenants->isNotEmpty()
            ? $subAccount->tenants
            : ($subAccount->tenant ? collect([$subAccount->tenant]) : collect([]));

        $settings = ResellerSetting::allForReseller($reseller->id);
        $currency = $settings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);

        $tenantDisplayUrl = $subAccount->tenant ? ($subAccount->tenant->custom_domain ?: (request()->getHttpHost() . '/' . $subAccount->tenant->subdomain)) : null;
        $tenantLoginUrl = $subAccount->tenant ? route('reseller.tenants.login-as', $subAccount->tenant->id) : null;

        $planExpiresAtJalali = null;
        $planDaysRemaining = null;
        if ($subAccount->tenant && $subAccount->tenant->plan_expires_at) {
            $planExpiresAtJalali = reseller_format_jalali($subAccount->tenant->plan_expires_at);
            $planDaysRemaining = (int)now()->diffInDays($subAccount->tenant->plan_expires_at, false);
        }

        $tenantModules = [];
        if ($subAccount->tenant) {
            $activeModules = $subAccount->tenant->modules->filter(static function ($mod) {
                return (bool)$mod->is_active && (!$mod->expires_at || !Carbon::now()->greaterThan($mod->expires_at));
            });

            $tenantModules = $activeModules->map(static function ($mod) {
                $slug = strtolower(trim((string)$mod->module_slug));
                return [
                    'slug' => $slug,
                    'title' => ResellerHelper::getModuleTitle($slug),
                    'is_active' => (bool)$mod->is_active,
                ];
            })->values()->toArray();
        }

        // Fetch related Invoices
        $invoices = ResellerInvoice::where('reseller_id', $subAccount->reseller_id)
            ->where(static function ($q) use ($subAccount) {
                if ($subAccount->tenant_id) {
                    $q->where('tenant_id', $subAccount->tenant_id);
                }
                if ($subAccount->email) {
                    $q->orWhere('sub_account_email', $subAccount->email);
                }
                if ($subAccount->mobile) {
                    $q->orWhere('sub_account_mobile', $subAccount->mobile);
                }
            })
            ->latest('issue_date')
            ->take(10)
            ->get();

        // Fetch related Orders
        $orders = ResellerOrder::where('reseller_id', $subAccount->reseller_id)
            ->where(static function ($q) use ($subAccount) {
                if ($subAccount->tenant_id) {
                    $q->where('tenant_id', $subAccount->tenant_id);
                }
                if ($subAccount->email) {
                    $q->orWhere('sub_account_email', $subAccount->email);
                }
                if ($subAccount->mobile) {
                    $q->orWhere('sub_account_mobile', $subAccount->mobile);
                }
            })
            ->latest('created_at')
            ->take(10)
            ->get();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'sub_account' => [
                    'id' => $subAccount->id,
                    'name' => $subAccount->name,
                    'email' => $subAccount->email,
                    'mobile' => $subAccount->mobile,
                    'company_name' => $subAccount->company_name,
                    'status' => $subAccount->status,
                    'status_label' => $subAccount->status_label,
                    'status_badge' => $subAccount->status_badge,
                    'jalali_created_at' => $subAccount->jalali_created_at,
                    'time_ago' => $subAccount->time_ago,
                    'reseller' => $subAccount->reseller ? [
                        'id' => $subAccount->reseller->id,
                        'name' => $subAccount->reseller->company_name ?: ($subAccount->reseller->user?->name ?? __('resellerhub::messages.common.reseller_id', ['id' => $subAccount->reseller->id])),
                        'brand_title' => $subAccount->reseller->brand_title,
                    ] : null,
                    'tenant' => $subAccount->tenant ? [
                        'id' => $subAccount->tenant->id,
                        'name' => $subAccount->tenant->name,
                        'subdomain' => $subAccount->tenant->subdomain,
                        'status' => $subAccount->tenant->status,
                        'plan_expires_at' => $planExpiresAtJalali,
                        'plan_expires_at_jalali' => $planExpiresAtJalali,
                        'show_url' => route('reseller.tenants.show', $subAccount->tenant->id),
                        'login_url' => $tenantLoginUrl,
                        'modules' => $tenantModules,
                    ] : null,
                ]
            ]);
        }

        $packages = ResellerPackage::forReseller($reseller->id)->active()->get();
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);
        $baseDomain = ResellerHelper::getBaseDomain();
        $defaultDuration = (int)ResellerSetting::get($reseller->id, 'default_plan_duration_months', 12);

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

        $systemModules = Module::where('installed', true)->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])->get();
        if ($systemModules->isEmpty() && class_exists(\Nwidart\Modules\Facades\Module::class)) {
            $systemModules = collect(\Nwidart\Modules\Facades\Module::allEnabled())->map(static function ($pMod) {
                return (object)[
                    'name' => $pMod->getName(),
                    'slug' => Str::lower($pMod->getName()),
                    'description' => $pMod->get('description'),
                ];
            })->filter(static fn($m) => !in_array($m->slug, ['resellerhub', 'settings', 'admin', 'core'], true));
        }
        $availableModules = $systemModules->map(static function ($mod) use ($reseller) {
            $title = ResellerHelper::getModuleTitle($mod->slug);
            return (object)[
                'name' => $mod->name,
                'slug' => $mod->slug,
                'title' => $title ?: $mod->name,
                'is_allowed' => $reseller->canSellModule($mod->slug) || $reseller->canSellModule($mod->name),
            ];
        })->values();

        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        return view('resellerhub::user.subaccounts.show', compact(
            'reseller',
            'subAccount',
            'allTenants',
            'tenantModules',
            'tenantDisplayUrl',
            'tenantLoginUrl',
            'planExpiresAtJalali',
            'planDaysRemaining',
            'invoices',
            'orders',
            'settings',
            'currency',
            'currencyLabel',
            'isSuperAdmin',
            'packages',
            'availableModules',
            'catalog',
            'baseDomain',
            'defaultDuration',
            'financialSettings',
            'invoiceNumbering'
        ));
    }

    public function toggleStatus(Request $request, ResellerSubAccount $subAccount): JsonResponse|RedirectResponse
    {
        $this->authorizeSubAccountAccess($subAccount);

        $oldStatus = $subAccount->status;
        $newStatus = $oldStatus === ResellerSubAccount::STATUS_ACTIVE
            ? ResellerSubAccount::STATUS_SUSPENDED
            : ResellerSubAccount::STATUS_ACTIVE;

        $subAccount->update(['status' => $newStatus]);

        $msg = $newStatus === ResellerSubAccount::STATUS_ACTIVE
            ? __('resellerhub::messages.subaccounts.activated_success', ['name' => $subAccount->name])
            : __('resellerhub::messages.subaccounts.suspended_success', ['name' => $subAccount->name]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'status_label' => $subAccount->status_label,
                'status_badge' => $subAccount->status_badge,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    protected function authorizeSubAccountAccess(ResellerSubAccount $subAccount): void
    {
        $reseller = $this->getReseller();
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if (!$isSuperAdmin && (int)$subAccount->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }
    }
}
