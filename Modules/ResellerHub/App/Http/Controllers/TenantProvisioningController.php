<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Tenancy\TenantManager;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\StoreTenantRequest;
use Modules\ResellerHub\App\Http\Requests\UpdateTenantRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerModulePricing;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\DomainVerificationService;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;
use Modules\ResellerHub\App\Services\TenantProvisioningService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Morilog\Jalali\Jalalian;
use Throwable;

class TenantProvisioningController extends Controller
{
    use HasReseller;

    protected ResellerBillingService $billingService;
    protected ModuleCapabilityCatalogService $catalogService;
    protected ModuleTitleResolver $titleResolver;

    public function __construct(
        ResellerBillingService $billingService,
        ModuleCapabilityCatalogService $catalogService,
        ?ModuleTitleResolver $titleResolver = null,
    ) {
        $this->billingService = $billingService;
        $this->catalogService = $catalogService;
        $this->titleResolver = $titleResolver ?: app(ModuleTitleResolver::class);
    }

    /**
     * Display a paginated list of tenants for this reseller.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $reseller = $isSuperAdmin ? $this->getResellerOrNull() : $this->getReseller();

        $selectedResellerId = $request->input('reseller_id');
        $scopedResellerId = null;
        if ($isSuperAdmin) {
            if ($selectedResellerId && $selectedResellerId !== 'all') {
                $scopedResellerId = (int)$selectedResellerId;
            }
        } elseif ($reseller) {
            $scopedResellerId = (int)$reseller->id;
        }

        $query = Tenant::query()->when($scopedResellerId, function ($q) use ($scopedResellerId) {
            $q->where('reseller_id', $scopedResellerId);
        });

        // Filter by Search
        if ($search = $request->input('search', $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('custom_domain', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            if ($status === 'expired') {
                $query->whereNotNull('plan_expires_at')->where('plan_expires_at', '<', now());
            } elseif (in_array($status, [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_PENDING_PAYMENT], true)) {
                $query->where('status', $status);
            }
        }

        $tenants = $query->with(['modules', 'subAccount', 'reseller', 'package'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $allResellers = $isSuperAdmin ? Reseller::select('id', 'company_name')->get() : collect();
        $currencyLabel = $reseller ? $reseller->getCurrencyLabel() : reseller_currency_label('toman');

        return view('resellerhub::user.tenants.index', compact(
            'reseller',
            'tenants',
            'isSuperAdmin',
            'allResellers',
            'currencyLabel'
        ));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $reseller = $this->getReseller();

        $systemModules = Module::where('installed', true)
            ->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])
            ->get();

        if ($systemModules->isEmpty() && class_exists(\Nwidart\Modules\Facades\Module::class)) {
            $systemModules = collect(\Nwidart\Modules\Facades\Module::allEnabled())->map(static function ($pMod) {
                return (object)[
                    'name' => $pMod->getName(),
                    'slug' => Str::lower($pMod->getName()),
                    'description' => $pMod->get('description'),
                ];
            })->filter(static fn($m) => !in_array($m->slug, ['resellerhub', 'settings', 'admin', 'core'], true));
        }

        $availableModules = $systemModules->map(function ($mod) use ($reseller) {

            $title = $this->titleResolver->resolve($mod->slug) ?: $mod->name;

            $isAllowed = $reseller->canSellModule($mod->slug) || $reseller->canSellModule($mod->name);

            return (object)[
                'name' => $mod->name,
                'slug' => $mod->slug,
                'title' => $title ?: $mod->name,
                'description' => $mod->description ?? null,
                'is_allowed' => $isAllowed,
                'is_default' => $isAllowed,
            ];
        })->values();

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

        $allModulesJson = $availableModules->map(static function ($m) {
            return [
                'name' => $m->name,
                'slug' => $m->slug,
                'title' => $m->title,
                'description' => $m->description ?? '',
            ];
        })->values();

        $financialSettings = ResellerSetting::allForReseller($reseller->id);
        $currency = $financialSettings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);
        $defaultTaxRate = (float)($financialSettings['default_tax_rate'] ?? 10);
        $defaultTaxMode = $financialSettings['tax_mode'] ?? 'invoice';

        $modulePricings = ResellerModulePricing::allKeyedBySlug($reseller->id);
        $catalog = $this->catalogService->getCatalogForReseller($reseller);

        $defaultDuration = (int)ResellerSetting::get($reseller->id, 'default_plan_duration_months', 12);

        $baseDomain = ResellerHelper::getBaseDomain();

        $packages = ResellerPackage::forReseller($reseller->id)->active()->get();

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
                    'label' => $sa->name . ' (' . $sa->mobile . ')' . ($sa->company_name ? (' - ' . $sa->company_name) : ''),
                ];
            });

        return view('resellerhub::user.tenants.create', compact(
            'reseller',
            'availableModules',
            'allModulesJson',
            'baseDomain',
            'resellersList',
            'defaultDuration',
            'isSuperAdmin',
            'packages',
            'subAccounts',
            'financialSettings',
            'currency',
            'currencyLabel',
            'defaultTaxRate',
            'defaultTaxMode',
            'modulePricings',
            'catalog',
        ));
    }

    public function checkSubdomain(Request $request): JsonResponse
    {
        $subdomain = strtolower(trim((string)$request->input('subdomain', '')));

        if (empty($subdomain)) {
            return response()->json(['available' => false, 'message' => __('resellerhub::messages.tenants.subdomain_empty')]);
        }

        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $subdomain)) {
            return response()->json(['available' => false, 'message' => __('resellerhub::messages.tenants.subdomain_invalid')]);
        }

        if (strlen($subdomain) < 3 || strlen($subdomain) > 63) {
            return response()->json(['available' => false, 'message' => __('resellerhub::messages.tenants.subdomain_length')]);
        }

        $reserved = app(TenantManager::class)->getReservedSubdomains();
        if (in_array($subdomain, $reserved, true)) {
            return response()->json(['available' => false, 'message' => __('resellerhub::messages.tenants.subdomain_reserved')]);
        }

        $exists = Tenant::where('subdomain', $subdomain)->exists();
        if ($exists) {
            return response()->json(['available' => false, 'message' => __('resellerhub::messages.tenants.subdomain_taken')]);
        }

        return response()->json(['available' => true, 'message' => __('resellerhub::messages.tenants.subdomain_available')]);
    }

    public function checkDomain(Request $request, DomainVerificationService $domainService): JsonResponse
    {
        $domain = $request->input('domain', $request->input('custom_domain', ''));
        $ignoreTenantId = $request->filled('tenant_id') ? (int)$request->input('tenant_id') : null;

        $reseller = $this->getReseller();
        if ($reseller && method_exists($reseller, 'allowsCustomDomains') && !$reseller->allowsCustomDomains()) {
            return response()->json([
                'valid' => false,
                'available' => false,
                'allowed' => false,
                'message' => __('resellerhub::messages.tenants.domain_disabled'),
            ]);
        }

        $result = $domainService->fullCheck((string)$domain, $ignoreTenantId);
        $result['allowed'] = true;

        return response()->json($result);
    }

    public function store(StoreTenantRequest $request, TenantProvisioningService $service): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        $resellerSettings = ResellerSetting::allForReseller($reseller->id);

        $taxFromSettings = $request->boolean('tax_from_settings', true);
        $taxMode = $resellerSettings['tax_mode'] ?? 'invoice';
        $taxRate = (float)($resellerSettings['default_tax_rate'] ?? 10);
        if (!$taxFromSettings) {
            $taxRate = (float)$request->input('invoice_tax_rate', $taxRate);
            $taxMode = (string)$request->input('invoice_tax_mode', $taxMode);
        }
        if (!in_array($taxMode, ['invoice', 'item'], true)) {
            $taxMode = 'invoice';
        }
        $taxRate = max(0, min(100, $taxRate));

        $invoiceAuto = $request->boolean('invoice_auto', true);
        $manualInvoiceNumber = '';
        if (!$invoiceAuto) {
            $manualInvoiceNumber = trim((string)$request->input('invoice_number', ''));
            if ($manualInvoiceNumber !== '') {
                $exists = ResellerInvoice::withTrashed()
                    ->where('reseller_id', $reseller->id)
                    ->where('invoice_number', $manualInvoiceNumber)
                    ->exists();
                if ($exists) {
                    $msg = __('resellerhub::messages.invoices.number_duplicate');
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $msg,
                            'errors' => ['invoice_number' => [$msg]],
                        ], 422);
                    }
                    return back()->withInput()->with('error', $msg);
                }
            }
        }

        try {
            $validatedData = $request->validated();

            if ($request->hasFile('tenant_brand_logo')) {
                $validatedData['uploaded_brand_logo'] = $request->file('tenant_brand_logo')->store('branding/tenants/logos', 'public');
            }
            if ($request->hasFile('tenant_brand_favicon')) {
                $validatedData['uploaded_brand_favicon'] = $request->file('tenant_brand_favicon')->store('branding/tenants/favicons', 'public');
            }

            $shouldIssueInvoice = $request->boolean('issue_invoice')
                || $request->filled('invoice_items')
                || ($request->filled('package_id') && $request->input('issue_invoice', '1') !== '0');

            $financialData = [
                'should_issue_invoice' => $shouldIssueInvoice,
                'package_id' => $request->input('package_id'),
                'invoice_items' => $request->input('invoice_items', []),
                'invoice_number' => $manualInvoiceNumber,
                'sub_account_name' => $request->input('admin_name'),
                'sub_account_mobile' => $request->input('admin_mobile'),
                'sub_account_email' => $request->input('admin_email'),
                'invoice_issue_date' => $request->input('invoice_issue_date'),
                'invoice_due_date' => $request->input('invoice_due_date'),
                'invoice_discount_amount' => $request->input('invoice_discount_amount', 0),
                'invoice_terms' => $request->input('invoice_terms'),
                'invoice_notes' => $request->input('invoice_notes'),
                'tax_mode' => $taxMode,
                'tax_rate' => $taxRate,
            ];

            $result = $service->provisionWithFinancials($reseller, $validatedData, $financialData, Auth::user());
            $tenant = $result['tenant'];
            $invoice = $result['invoice'];
            $order = $result['order'];

            $isExistingTenant = ($validatedData['tenant_mode'] ?? '') === 'existing';

            if ($request->expectsJson() || $request->ajax()) {
                $loginUrl = $service->createSsoLoginUrl($tenant, null, Auth::user());
                $directUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');

                return response()->json([
                    'success' => true,
                    'message' => $isExistingTenant
                        ? __('resellerhub::messages.tenants.invoice_created_success', ['name' => $tenant->name])
                        : __('resellerhub::messages.tenants.created_success', ['name' => $tenant->name]),
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'url' => $loginUrl,
                        'direct_url' => $directUrl,
                        'sso_login_url' => $loginUrl,
                    ],
                    'invoice' => $invoice ? [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total' => $invoice->total,
                        'formatted_total' => number_format($invoice->total),
                        'status' => $invoice->status,
                        'status_label' => $invoice->status_label,
                        'show_url' => route('reseller.invoices.show', $invoice->id),
                        'print_url' => route('reseller.invoices.print', $invoice->id),
                    ] : null,
                    'order' => $order ? [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'show_url' => route('reseller.orders.show', $order->id),
                    ] : null,
                    'redirect_url' => $invoice ? route('reseller.invoices.show', $invoice->id) : route('reseller.tenants.show', $tenant->id),
                ]);
            }

            return redirect()->route('reseller.tenants.show', $tenant->id)
                ->with('success', __('resellerhub::messages.tenants.created_success', ['name' => $tenant->name]));
        } catch (Throwable $e) {
            Log::error('[TenantProvisioningController@store] ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = $this->userFacingExceptionMessage($e);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withInput()->with('error', $message);
        }
    }

    public function edit(Tenant $tenant): View
    {
        $currentUser = Auth::user();
        $currentUserReseller = $this->getReseller();

        $isSuperAdmin = method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');
        if (!$isSuperAdmin && (int)$tenant->reseller_id !== (int)$currentUserReseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $reseller = $tenant->reseller ?: $currentUserReseller;

        $tenant->load(['modules', 'owner', 'subAccount', 'package']);

        $tenantSettings = is_array($tenant->settings) ? $tenant->settings : [];
        $packageLimits = is_array($tenant->package?->limits) ? $tenant->package->limits : [];
        $tenantLimits = array_merge($packageLimits, (array)($tenantSettings['limits'] ?? []));

        $rawFeatures = $tenantSettings['features'] ?? ($tenant->package?->features ?? []);
        $tenantFeatures = [];
        foreach ((array)$rawFeatures as $f) {
            if (is_array($f)) {
                foreach ($f as $subF) {
                    if (is_string($subF) && $subF !== '') {
                        $tenantFeatures[] = $subF;
                    }
                }
            } elseif (is_string($f) && $f !== '') {
                $tenantFeatures[] = $f;
            }
        }
        $tenantFeatures = array_values(array_unique($tenantFeatures));

        $systemModules = Module::where('installed', true)
            ->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])
            ->get();

        if ($systemModules->isEmpty() && class_exists(\Nwidart\Modules\Facades\Module::class)) {
            $systemModules = collect(\Nwidart\Modules\Facades\Module::allEnabled())->map(static function ($pMod) {
                return (object)[
                    'name' => $pMod->getName(),
                    'slug' => Str::lower($pMod->getName()),
                    'description' => $pMod->get('description'),
                ];
            })->filter(static fn($m) => !in_array($m->slug, ['resellerhub', 'settings', 'admin', 'core'], true));
        }

        $activeModuleSlugs = $tenant->modules->pluck('module_slug')->toArray();

        $availableModules = $systemModules->map(function ($mod) use ($reseller, $activeModuleSlugs) {
            // Cached title resolution — see the note in create().
            $title = $this->titleResolver->resolve($mod->slug) ?: $mod->name;

            return (object)[
                'name' => $mod->name,
                'slug' => $mod->slug,
                'title' => $title ?: $mod->name,
                'description' => $mod->description ?? null,
                'is_allowed' => $reseller->canSellModule($mod->slug) || $reseller->canSellModule($mod->name),
                'is_active' => in_array($mod->slug, $activeModuleSlugs, true),
            ];
        })->values();

        $baseDomain = ResellerHelper::getBaseDomain();
        $resellerPrimaryColor = ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5');

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
                    'label' => $sa->name . ' (' . $sa->mobile . ')' . ($sa->company_name ? (' - ' . $sa->company_name) : ''),
                ];
            });
        $resellerSettings = ResellerSetting::allForReseller($reseller->id);
        $currency = $resellerSettings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);
        $modulePricings = ResellerModulePricing::allKeyedBySlug($reseller->id);
        $catalog = $this->catalogService->getCatalogForReseller($reseller);
        $invoiceNumbering = [
            'prefix' => (string)($resellerSettings['invoice_prefix'] ?? 'INV-'),
            'middle' => (string)($resellerSettings['invoice_middle_prefix'] ?? reseller_jalali_year()),
            'suffix' => (string)($resellerSettings['invoice_suffix'] ?? ''),
            'padding' => (int)($resellerSettings['invoice_padding'] ?? 4),
            'auto' => !empty($resellerSettings['invoice_auto']),
            'tax_mode' => (string)($resellerSettings['tax_mode'] ?? 'invoice'),
            'tax_rate' => (float)($resellerSettings['default_tax_rate'] ?? 10),
            'terms' => (string)($resellerSettings['invoice_terms'] ?? ''),
            'footer' => (string)($resellerSettings['invoice_footer_note'] ?? ''),
        ];

        return view('resellerhub::user.tenants.edit', compact(
            'reseller',
            'tenant',
            'availableModules',
            'baseDomain',
            'resellerPrimaryColor',
            'subAccounts',
            'currency',
            'currencyLabel',
            'modulePricings',
            'catalog',
            'invoiceNumbering',
            'tenantLimits',
            'tenantFeatures'
        ));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse|RedirectResponse
    {
        $currentUser = Auth::user();
        $currentUserReseller = $this->getReseller();

        $isSuperAdmin = method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');
        if (!$isSuperAdmin && (int)$tenant->reseller_id !== (int)$currentUserReseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $reseller = $tenant->reseller ?: $currentUserReseller;

        $data = $request->validated();

        // A missing "modules" key means "leave modules untouched" — never a
        // signal to wipe every module the tenant currently has. The form sends
        // `modules_present` so an explicitly emptied selection still applies.
        $modulesProvided = $request->boolean('modules_present') || $request->has('modules');
        $selectedModules = $modulesProvided ? ($data['modules'] ?? []) : [];
        if (is_string($selectedModules)) {
            $selectedModules = array_filter(array_map('trim', explode(',', $selectedModules)));
        }
        $selectedModules = is_array($selectedModules) ? $selectedModules : [];

        $createdInvoice = null;
        try {
            DB::transaction(function () use ($tenant, $data, $reseller, $selectedModules, $modulesProvided, $request, &$createdInvoice) {
                $tenantUpdate = [
                    'name' => trim($data['name']),
                    'subdomain' => strtolower(trim($data['subdomain'])),
                    'custom_domain' => !empty($data['custom_domain']) ? strtolower(trim($data['custom_domain'])) : null,
                    'status' => $data['status'] ?? $tenant->status ?? Tenant::STATUS_ACTIVE,
                ];

                // A tenant's admin is resolved through its sub-account / users.

                // Handle White-Label branding updates
                $whiteLabelType = $request->input('white_label_type');
                if ($whiteLabelType === 'custom') {
                    $existingBranding = is_array($tenant->branding) ? $tenant->branding : [];
                    $customBranding = $request->input('branding', []);
                    $logoPath = $existingBranding['brand_logo'] ?? null;
                    $faviconPath = $existingBranding['brand_favicon'] ?? null;

                    if ($request->boolean('remove_tenant_brand_logo')) {
                        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                            Storage::disk('public')->delete($logoPath);
                        }
                        $logoPath = null;
                    }
                    if ($request->hasFile('tenant_brand_logo')) {
                        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                            Storage::disk('public')->delete($logoPath);
                        }
                        $logoPath = $request->file('tenant_brand_logo')->store('branding/tenants/logos', 'public');
                    }

                    if ($request->boolean('remove_tenant_brand_favicon')) {
                        if ($faviconPath && Storage::disk('public')->exists($faviconPath)) {
                            Storage::disk('public')->delete($faviconPath);
                        }
                        $faviconPath = null;
                    }
                    if ($request->hasFile('tenant_brand_favicon')) {
                        if ($faviconPath && Storage::disk('public')->exists($faviconPath)) {
                            Storage::disk('public')->delete($faviconPath);
                        }
                        $faviconPath = $request->file('tenant_brand_favicon')->store('branding/tenants/favicons', 'public');
                    }

                    $tenantUpdate['branding'] = [
                        'is_custom' => true,
                        'brand_title' => $customBranding['brand_title'] ?? $tenantUpdate['name'],
                        'brand_primary_color' => $customBranding['brand_primary_color'] ?? '#4f46e5',
                        'brand_logo' => $logoPath,
                        'brand_favicon' => $faviconPath,
                        'brand_footer_text' => $customBranding['brand_footer_text'] ?? null,
                        'support_phone' => $customBranding['support_phone'] ?? null,
                        'support_email' => $customBranding['support_email'] ?? null,
                    ];
                } elseif ($whiteLabelType === 'default') {
                    $tenantUpdate['branding'] = [
                        'is_custom' => false,
                        'brand_title' => $reseller->brand_title ?: $reseller->company_name,
                        'brand_primary_color' => ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5'),
                        'brand_logo' => $reseller->brand_logo,
                        'brand_favicon' => $reseller->brand_favicon,
                        'brand_footer_text' => $reseller->brand_footer_text,
                        'support_phone' => $reseller->support_phone,
                        'support_email' => $reseller->support_email,
                    ];
                }

                if (!empty($data['sub_account_id'])) {
                    $newSub = ResellerSubAccount::where('reseller_id', $tenant->reseller_id ?: $reseller->id)
                        ->find($data['sub_account_id']);

                    if ($newSub) {
                        $tenantUpdate['sub_account_id'] = $newSub->id;
                    }
                }

                $currentSettings = is_array($tenant->settings) ? $tenant->settings : [];

                if ($request->has('features')) {
                    $rawFeatures = $request->input('features', []);
                    $flattenFeatures = [];
                    foreach ((array)$rawFeatures as $val) {
                        if (is_array($val)) {
                            foreach ($val as $subVal) {
                                if (is_string($subVal) && $subVal !== '') {
                                    $flattenFeatures[] = $subVal;
                                }
                            }
                        } elseif (is_string($val) && $val !== '') {
                            $flattenFeatures[] = $val;
                        }
                    }
                    $currentSettings['features'] = array_values(array_unique($flattenFeatures));
                }

                $cleanLimits = null;
                if ($request->has('limits')) {
                    $rawLimits = (array)$request->input('limits', []);
                    $cleanLimits = [];
                    foreach ($rawLimits as $k => $v) {
                        if ($v !== null && $v !== '') {
                            $cleanLimits[$k] = is_numeric($v) ? (int)$v : $v;
                        } else {
                            $cleanLimits[$k] = null;
                        }
                    }
                    $currentSettings['limits'] = $cleanLimits;
                }

                $tenantUpdate['settings'] = $currentSettings;

                $tenant->update($tenantUpdate);

                $subAccount = $tenant->subAccount ?: ResellerSubAccount::where('tenant_id', $tenant->id)->first();
                if ($subAccount) {
                    $subAccountUpdate = [];
                    if (!empty($data['admin_name'])) {
                        $subAccountUpdate['name'] = trim($data['admin_name']);
                    }
                    if (!empty($data['admin_email'])) {
                        $subAccountUpdate['email'] = trim($data['admin_email']);
                    }
                    if (isset($data['admin_mobile'])) {
                        $subAccountUpdate['mobile'] = trim($data['admin_mobile']);
                    }
                    if (!empty($data['name'])) {
                        $subAccountUpdate['company_name'] = trim($data['name']);
                    }
                    if (!empty($data['status'])) {
                        $subAccountUpdate['status'] = $data['status'];
                    }
                    if (!empty($data['password'])) {
                        $subAccountUpdate['password'] = Hash::make($data['password']);
                    }
                    if ($cleanLimits !== null) {
                        $subAccountUpdate['limits'] = $cleanLimits;
                    }
                    if (!empty($subAccountUpdate)) {
                        $subAccount->update($subAccountUpdate);
                    }
                } elseif (!empty($data['admin_name']) && !empty($data['admin_email'])) {
                    $ownerUserId = $tenant->subAccount?->user_id ?: $tenant->users()->first()?->id;
                    ResellerSubAccount::create([
                        'reseller_id' => $tenant->reseller_id ?? $reseller->id,
                        'tenant_id' => $tenant->id,
                        'user_id' => $ownerUserId,
                        'name' => trim($data['admin_name']),
                        'email' => trim($data['admin_email']),
                        'mobile' => !empty($data['admin_mobile']) ? trim($data['admin_mobile']) : null,
                        'password' => !empty($data['password']) ? Hash::make($data['password']) : Hash::make(Str::random(12)),
                        'company_name' => trim($data['name']),
                        'status' => $data['status'] ?? 'active',
                        'limits' => $cleanLimits,
                    ]);
                }

                // Update tenant admin user if present
                $adminUser = $tenant->subAccount?->user ?? $tenant->users()->first();
                if ($adminUser) {
                    $adminUpdate = [];
                    if (!empty($data['admin_name'])) {
                        $adminUpdate['name'] = trim($data['admin_name']);
                    }
                    if (!empty($data['admin_email'])) {
                        $adminUpdate['email'] = trim($data['admin_email']);
                    }
                    if (isset($data['admin_mobile'])) {
                        $adminUpdate['mobile'] = trim($data['admin_mobile']);
                    }
                    if (!empty($data['password'])) {
                        $adminUpdate['password'] = Hash::make($data['password']);
                    }
                    if (!empty($adminUpdate)) {
                        $adminUser->update($adminUpdate);
                    }
                }

                if ($modulesProvided) {
                    TenantModule::where('tenant_id', $tenant->id)
                        ->whereNotIn('module_slug', $selectedModules)
                        ->delete();

                    foreach ($selectedModules as $modSlug) {
                        $slug = strtolower(trim($modSlug));
                        if ($reseller->canSellModule($slug)) {
                            TenantModule::updateOrCreate(
                                ['tenant_id' => $tenant->id, 'module_slug' => $slug],
                                [
                                    'is_active' => true,
                                    'expires_at' => $tenant->plan_expires_at,
                                ]
                            );
                        }
                    }
                }

                if ($request->boolean('issue_invoice')) {
                    $items = array_values(array_filter((array)$request->input('invoice_items', []), static function ($it) {
                        $isCustom = ($it['item_type'] ?? '') === 'custom';
                        return $isCustom ? trim((string)($it['description'] ?? '')) !== '' : true;
                    }));

                    if (!empty($items)) {
                        $resellerSettings = ResellerSetting::allForReseller($reseller->id);

                        $taxMode = $resellerSettings['tax_mode'] ?? 'invoice';
                        $taxRate = (float)($resellerSettings['default_tax_rate'] ?? 10);
                        if (!$request->boolean('tax_from_settings', true)) {
                            $taxRate = (float)$request->input('invoice_tax_rate', $taxRate);
                            $taxMode = (string)$request->input('invoice_tax_mode', $taxMode);
                        }
                        if (!in_array($taxMode, ['invoice', 'item'], true)) {
                            $taxMode = 'invoice';
                        }
                        $taxRate = max(0, min(100, $taxRate));

                        $invoiceNumber = trim((string)$request->input('invoice_number', ''));
                        if ($request->boolean('invoice_auto', true) || $invoiceNumber === '') {
                            $invoiceNumber = ResellerInvoice::generateNextNumber($reseller->id, ResellerInvoice::TYPE_OFFICIAL);
                        } elseif (ResellerInvoice::withTrashed()->where('reseller_id', $reseller->id)
                            ->where('invoice_number', $invoiceNumber)->exists()) {
                            throw new ResellerHubException(__('resellerhub::messages.invoices.number_duplicate'));
                        }

                        $issueDate = $request->filled('invoice_issue_date')
                            ? (reseller_to_carbon(trim((string)$request->input('invoice_issue_date'))) ?? now())
                            : now();

                        $dueDate = $request->filled('invoice_due_date')
                            ? reseller_to_carbon(trim((string)$request->input('invoice_due_date')))
                            : null;

                        $invoice = $this->billingService->createInvoice($reseller, [
                            'tenant_id' => $tenant->id,
                            'invoice_number' => $invoiceNumber,
                            'type' => ResellerInvoice::TYPE_OFFICIAL,
                            'status' => ResellerInvoice::STATUS_UNPAID,
                            'sub_account_name' => $tenant->subAccount?->name ?: ($tenant->subAccount?->user?->name ?: $tenant->name),
                            'sub_account_mobile' => $tenant->subAccount?->mobile ?: ($tenant->subAccount?->user?->mobile ?? ''),
                            'sub_account_email' => $tenant->subAccount?->email ?: ($tenant->subAccount?->user?->email ?? ''),
                            'issue_date' => $issueDate,
                            'due_date' => $dueDate,
                            'tax_percent' => $taxRate,
                            'discount_type' => 'fixed',
                            'discount_value' => (int)$request->input('invoice_discount_amount', 0),
                            'terms' => (string)$request->input('invoice_terms', ''),
                            'notes' => (string)$request->input('invoice_notes', ''),
                            'items' => $items,
                        ], $request->user());

                        $createdInvoice = $this->billingService->applyTaxModeToInvoice($invoice, $taxMode, $taxRate, 'tenant_edit_form');
                    }
                }
            }, 3);

            $msg = __('resellerhub::messages.tenants.updated_success', ['name' => $tenant->name]);
            $lastInvoice = $createdInvoice;

            if ($request->expectsJson() || $request->ajax()) {
                $url = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');

                return response()->json([
                    'success' => true,
                    'invoice' => $lastInvoice ? [
                        'id' => $lastInvoice->id,
                        'invoice_number' => $lastInvoice->invoice_number,
                        'total' => $lastInvoice->total,
                        'show_url' => route('reseller.invoices.show', $lastInvoice->id),
                    ] : null,
                    'message' => $msg,
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'status' => $tenant->status,
                        'url' => $url,
                    ],
                    'redirect_url' => route('reseller.tenants.show', $tenant->id),
                ]);
            }

            return redirect()->route('reseller.tenants.show', $tenant->id)->with('success', $msg);
        } catch (Throwable $e) {
            Log::error('[TenantProvisioningController@update] ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = $this->userFacingExceptionMessage($e);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withInput()->with('error', $message);
        }
    }

    /**
     * Only domain failures are safe to show. Anything else (PDO, TypeError, a bare
     * masked every meaningful subclass while letting arbitrary vendor `Exception`
     * messages through. See {@see ResellerHubException}.
     */
    protected function userFacingExceptionMessage(Throwable $e): string
    {
        if ($e instanceof ResellerHubException) {
            return $e->getMessage();
        }

        Log::error('resellerhub.provisioning.unexpected_exception', [
            'exception' => $e::class,
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'reseller'  => auth()->user()?->id,
        ]);

        return __('resellerhub::messages.common.unexpected_error');
    }

    /**
     * Direct login as tenant admin from the reseller / super-admin portal.
     */
    public function loginAs(Tenant $tenant, TenantProvisioningService $service): RedirectResponse
    {
        $currentUser = Auth::user();
        $isSuperAdmin = method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');

        if (!$isSuperAdmin) {
            $reseller = $this->getReseller();
            if ((int)$tenant->reseller_id !== (int)$reseller->id) {
                abort(403, __('resellerhub::messages.common.unauthorized'));
            }
        }

        $loginUrl = $service->createSsoLoginUrl($tenant, null, $currentUser);

        return redirect()->away($loginUrl);
    }
}
