<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\RenewTenantRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\DashboardMetricsService;
use Modules\ResellerHub\App\Services\DomainVerificationService;
use Modules\ResellerHub\App\Services\RenewalInvoiceService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Services\TenantQuotaService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerPagination;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Modules\ResellerHub\App\Traits\HasSubAccountSearch;
use Throwable;

class ResellerDashboardController extends Controller
{
    use HasReseller, HasSubAccountSearch, HasResellerSorting, HasResellerPagination;

    protected DashboardMetricsService $metricsService;

    public function __construct(?DashboardMetricsService $metricsService = null)
    {
        $this->metricsService = $metricsService ?: app(DashboardMetricsService::class);
    }

    /**
     * Display the reseller dashboard and tenant list.
     */
    public function index(): View|JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $reseller = $isSuperAdmin ? $this->getResellerOrNull() : $this->getReseller();

        $selectedResellerId = request('reseller_id');

        $scopedResellerId = null;
        if ($isSuperAdmin) {
            if ($selectedResellerId && $selectedResellerId !== 'all') {
                $scopedResellerId = (int)$selectedResellerId;
            }
        } elseif ($reseller) {
            $scopedResellerId = (int)$reseller->id;
        }

        $selectedPeriod = request('period', '6m');
        $validPeriods = ['30d', '3m', '6m', '12m', 'ytd'];
        if (!in_array($selectedPeriod, $validPeriods, true)) {
            $selectedPeriod = '6m';
        }

        $metrics = $this->metricsService->getDashboardData(
            $scopedResellerId,
            $selectedPeriod,
            (bool)request('refresh')
        );

        $currencyLabel = $reseller
            ? $reseller->getCurrencyLabel()
            : reseller_currency_label('toman');

        $tenantQuery = Tenant::query()->when($scopedResellerId, function ($q) use ($scopedResellerId) {
            $q->where('reseller_id', $scopedResellerId);
        });

        // Filter by Search
        $query = clone $tenantQuery;
        $this->applyTenantSearch($query, request('search', request('q')));

        // Filter by Status
        if ($status = request('status')) {
            if ($status === 'expired') {
                $query->whereNotNull('plan_expires_at')->where('plan_expires_at', '<', now());
            } elseif (in_array($status, [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_PENDING_PAYMENT], true)) {
                $query->where('status', $status);
            }
        }

        $this->applySorting($query, request(), [
            'name' => 'tenants.name',
            'subdomain' => 'tenants.subdomain',
            'status' => 'tenants.status',
            'expires_at' => fn($q, $dir) => $q->orderByRaw("tenants.plan_expires_at IS NULL, tenants.plan_expires_at {$dir}"),
            'plan_expires_at' => fn($q, $dir) => $q->orderByRaw("tenants.plan_expires_at IS NULL, tenants.plan_expires_at {$dir}"),
            'date' => 'tenants.created_at',
            'created_at' => 'tenants.created_at',
        ], defaultColumn: 'tenants.id');

        $perPage = $this->resolvePerPage();

        $tenants = $query
            ->with(['modules', 'subAccount', 'reseller', 'package'])
            ->paginate($perPage)
            ->withQueryString();

        $allResellers = $isSuperAdmin ? Reseller::select('id', 'company_name')->get() : collect();

        if (request()->ajax() || request()->wantsJson() || request('format') === 'json') {
            $periodRevenue = (int)($metrics['periodRevenue'] ?? 0);
            $pipelineRevenue = (int)($metrics['totalPipelineRevenue'] ?? 0);
            $volume = (int)($metrics['totalFinancialVolume'] ?? 0);
            $tenantsCreated = $metrics['tenantsCreatedSeries'] ?? collect();
            $revenueSeries = $metrics['revenueSeries'] ?? collect();
            $tenantsValues = $tenantsCreated instanceof Collection ? $tenantsCreated->values() : collect($tenantsCreated)->values();
            $revenueValues = $revenueSeries instanceof Collection ? $revenueSeries->values() : collect($revenueSeries)->values();

            return response()->json([
                'success' => true,
                'period' => $selectedPeriod,
                'periodLabel' => $metrics['selectedPeriodLabel'] ?? '',
                'selectedPeriodLabel' => $metrics['selectedPeriodLabel'] ?? '',
                'labels' => $metrics['chartLabels'] ?? [],
                'chartLabels' => $metrics['chartLabels'] ?? [],
                'tenants' => $tenantsValues,
                'tenantsCreatedSeries' => $tenantsValues,
                'revenue' => $revenueValues,
                'revenueSeries' => $revenueValues,
                'periodRevenue' => $periodRevenue,
                'periodRevenueFormatted' => reseller_price($periodRevenue),
                'periodTenantsCreated' => $metrics['periodTenantsCreated'] ?? 0,
                'revenueGrowthPercent' => $metrics['revenueGrowthPercent'] ?? 0,
                'tenantsGrowthPercent' => $metrics['tenantsGrowthPercent'] ?? 0,
                'pipelineData' => $metrics['pipelineData'] ?? [],
                'totalPipelineRevenue' => $pipelineRevenue,
                'totalPipelineRevenueFormatted' => reseller_price($pipelineRevenue),
                'totalPipelineCount' => $metrics['totalPipelineCount'] ?? 0,
                'revenueComposition' => $metrics['revenueComposition'] ?? [],
                'totalFinancialVolume' => $volume,
                'totalFinancialVolumeFormatted' => reseller_price($volume),
                'collectionRate' => $metrics['collectionRate'] ?? 0,
                'totalOrdersCount' => $metrics['totalOrdersCount'] ?? 0,
                'onlineOrdersCount' => $metrics['onlineOrdersCount'] ?? 0,
                'manualOrdersCount' => $metrics['manualOrdersCount'] ?? 0,
                'onlineOrdersRevenue' => $metrics['onlineOrdersRevenue'] ?? 0,
                'manualOrdersRevenue' => $metrics['manualOrdersRevenue'] ?? 0,
                'onlineOrdersRatio' => $metrics['onlineOrdersRatio'] ?? 0,
                'manualOrdersRatio' => $metrics['manualOrdersRatio'] ?? 0,
                'topModules' => $metrics['topModules'] ?? collect(),
                'avgModulesPerTenant' => $metrics['avgModulesPerTenant'] ?? 0,
                'featuresStats' => $metrics['featuresStats'] ?? collect(),
                'limitsStats' => $metrics['limitsStats'] ?? collect(),
                'totalFeaturesActiveCount' => $metrics['totalFeaturesActiveCount'] ?? 0,
                'avgFeaturesPerTenant' => $metrics['avgFeaturesPerTenant'] ?? 0,
                'limitedTenantsRatio' => $metrics['limitedTenantsRatio'] ?? 0,
                'packageDistribution' => $metrics['packageDistribution'] ?? collect(),
                'topCommercialPackage' => $metrics['topCommercialPackage'] ?? null,
                'retentionCohorts' => $metrics['retentionCohorts'] ?? collect(),
                'avgFleetAgeDays' => $metrics['avgFleetAgeDays'] ?? 0,
                'avgFleetAgeMonths' => $metrics['avgFleetAgeMonths'] ?? 0,
                'totalActiveModulesCount' => $metrics['totalActiveModulesCount'] ?? 0,
            ]);
        }

        return view('resellerhub::user.dashboard.index', array_merge($metrics, compact(
            'reseller',
            'tenants',
            'isSuperAdmin',
            'allResellers',
            'selectedResellerId',
            'currencyLabel',
            'selectedPeriod'
        )));
    }

    /**
     * Display detailed tenant information.
     */
    public function showTenant(Tenant $tenant): View
    {
        $isSuperAdmin = method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('super-admin');

        $reseller = $isSuperAdmin
            ? ($tenant->reseller ?: $this->getResellerOrNull())
            : $this->getReseller();

        $this->authorizeTenantAccess($tenant);

        $tenant->load(['modules', 'owner', 'subAccount', 'reseller.user', 'package', 'orders.invoice']);

        $effectiveReseller = ($isSuperAdmin && $tenant->reseller) ? $tenant->reseller : $reseller;
        $resellerSettings = $effectiveReseller ? ResellerSetting::allForReseller($effectiveReseller->id) : [];
        $currencyLabel = reseller_currency_label($resellerSettings['currency'] ?? 'toman');
        $availablePeriods = reseller_tenant_renewal_periods($tenant);
        $tenantInvoices = ResellerInvoice::where('tenant_id', $tenant->id)
            ->latest('issue_date')
            ->limit(5)
            ->get();

        $quotasSummary = app(TenantQuotaService::class)->getQuotasSummary($tenant);

        return view('resellerhub::user.tenants.show', compact(
            'reseller',
            'tenant',
            'resellerSettings',
            'currencyLabel',
            'availablePeriods',
            'tenantInvoices',
            'quotasSummary'
        ));
    }

    /**
     * Toggle tenant active/suspended status.
     */
    public function toggleStatus(Tenant $tenant): JsonResponse|RedirectResponse
    {
        $reseller = $this->getResellerOrNull();
        $this->authorizeTenantAccess($tenant);

        if ($tenant->isPendingPayment()) {
            $msg = __('resellerhub::messages.tenants.pending_payment_locked');
            return request()->expectsJson() || request()->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($tenant->status === Tenant::STATUS_ACTIVE) {
            $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);
            $msg = __('resellerhub::messages.tenants.status_suspended', ['name' => $tenant->name]);
            $newStatus = 'suspended';
        } else {
            $tenant->update(['status' => Tenant::STATUS_ACTIVE]);
            $msg = __('resellerhub::messages.tenants.status_reactivated', ['name' => $tenant->name]);
            $newStatus = 'active';
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => $msg,
                'active_count' => $reseller ? $reseller->activeTenantsCount() : null,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Renew tenant subscription manually or by issuing an invoice.
     */
    public function renewTenant(RenewTenantRequest $request, Tenant $tenant): JsonResponse|RedirectResponse
    {
        $reseller = $this->getResellerOrNull();
        $this->authorizeTenantAccess($tenant);

        if ($tenant->isPendingPayment()) {
            $msg = __('resellerhub::messages.tenants.pending_payment_renew_locked');
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $resellerId = (int)($tenant->reseller_id ?: $reseller?->id);

        $validated = $request->validated();
        $months = (int)$validated['months'];
        $issueInvoice = $request->boolean('issue_invoice');
        $notes = $validated['notes'] ?? null;

        $tenant->loadMissing(['package', 'orders.invoice', 'subAccount', 'owner']);

        if ($tenant->package && !$tenant->package->getPriceTypeEnum()->hasRenewal()) {
            $msg = 'این سامانه دارای پکیج ' . $tenant->package->getPriceTypeEnum()->label() . ' بوده و نیازی به تمدید ندارد.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $latestOrder = $tenant->orders()->latest('id')->first();

        $billingService = app(ResellerBillingService::class);
        $limitCheck = $billingService->validateAdvanceRenewalLimit($tenant, $months, $resellerId ?: null);
        if (!$limitCheck['allowed']) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $limitCheck['message']], 422);
            }
            return back()->with('error', $limitCheck['message']);
        }

        // 1. If issue invoice is requested
        if ($issueInvoice) {
            try {
                $renewalInvoiceService = app(RenewalInvoiceService::class);
                $renewalInvoice = $renewalInvoiceService->createRenewalInvoice(
                    tenant: $tenant,
                    months: $months,
                    resellerId: $resellerId,
                    notes: $notes,
                    createdBy: (int)auth()->id()
                );

                $msg = __('resellerhub::messages.invoices.renewal_created', ['invoice' => $renewalInvoice->invoice_number]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $msg,
                        'issued_invoice' => true,
                        'invoice' => $renewalInvoice,
                        'redirect_url' => route('reseller.invoices.show', $renewalInvoice->id),
                    ]);
                }

                return redirect()->route('reseller.invoices.show', $renewalInvoice->id)->with('success', $msg);
            } catch (Throwable $e) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('resellerhub::messages.invoices.renewal_error', ['error' => $e->getMessage()])], 422);
                }
                return back()->with('error', __('resellerhub::messages.invoices.renewal_error', ['error' => $e->getMessage()]));
            }
        }

        // 2. Direct Renewal (without invoice)
        $baseDate = ($tenant->plan_expires_at && Carbon::now()->lessThan($tenant->plan_expires_at))
            ? Carbon::parse($tenant->plan_expires_at)
            : Carbon::now();

        $newExpiresAt = reseller_add_jalali_months($baseDate, $months);

        $tenant->update([
            'plan_expires_at' => $newExpiresAt,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $tenant->modules()->update(['expires_at' => $newExpiresAt]);

        if ($latestOrder) {
            $latestOrder->update([
                'end_date' => $newExpiresAt,
                'renewal_date' => $newExpiresAt,
                'status' => ResellerOrder::STATUS_ACTIVE,
            ]);
        }

        $jalaliExpiresAt = reseller_format_jalali($newExpiresAt);
        $msg = __('resellerhub::messages.tenants.renewed_success', [
            'name' => $tenant->name,
            'months' => $months,
            'date' => $jalaliExpiresAt,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'plan_expires_at' => $newExpiresAt->format('Y/m/d'),
                'jalali_expires_at' => $jalaliExpiresAt,
                'issued_invoice' => false,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Attach or detach custom domain to tenant.
     */
    public function updateDomain(Request $request, Tenant $tenant, DomainVerificationService $domainService): JsonResponse
    {
        $reseller = $this->getReseller();
        $this->authorizeTenantAccess($tenant);

        if ($reseller && method_exists($reseller, 'allowsCustomDomains') && !$reseller->allowsCustomDomains()) {
            return response()->json([
                'success' => false,
                'message' => __('resellerhub::messages.tenants.domain_disabled'),
            ], 403);
        }

        $rawDomain = $request->input('custom_domain', '');
        $sanitized = $domainService->sanitizeDomain((string)$rawDomain);

        //If disconnect/remove requested
        if (empty($sanitized)) {
            $tenant->update(['custom_domain' => null]);
            return response()->json([
                'success' => true,
                'message' => __('resellerhub::messages.tenants.domain_removed'),
                'custom_domain' => null,
                'dns' => null,
            ]);
        }

        //Validate domain syntax
        if (!$domainService->isValidDomain($sanitized)) {
            return response()->json([
                'success' => false,
                'message' => __('resellerhub::messages.tenants.domain_invalid'),
            ], 422);
        }

        //Reserved domain check
        if ($domainService->isReservedDomain($sanitized)) {
            return response()->json([
                'success' => false,
                'message' => __('resellerhub::messages.tenants.domain_reserved'),
            ], 422);
        }

        // 4. Availability check (not taken by another tenant)
        if (!$domainService->isDomainAvailable($sanitized, $tenant->id)) {
            return response()->json([
                'success' => false,
                'message' => __('resellerhub::messages.tenants.domain_taken'),
            ], 422);
        }

        // 5. Update tenant
        $tenant->update(['custom_domain' => $sanitized]);

        // 6. Check DNS status
        $dns = $domainService->fullCheck($sanitized, $tenant->id);

        return response()->json([
            'success' => true,
            'message' => __('resellerhub::messages.tenants.domain_connected', ['domain' => $sanitized]),
            'custom_domain' => $sanitized,
            'dns' => $dns,
        ]);
    }

    /**
     * Switch active reseller context for SuperAdmin via POST request.
     */
    public function switchContext(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$this->isSuperAdmin($user)) {
            abort(403, 'تغییر زمینه کاری نماینده فقط توسط مدیر کل امکان‌پذیر است.');
        }

        $resellerId = $request->input('reseller_id');
        if ($resellerId === 'all' || empty($resellerId)) {
            session()->forget('active_reseller_id');
            Log::info("SuperAdmin user {$user->id} switched context to ALL resellers.");
            return back()->with('info', 'زمینه کاری روی تمام نمایندگان تنظیم شد.');
        }

        $reseller = Reseller::findOrFail((int)$resellerId);
        session(['active_reseller_id' => $reseller->id]);
        Log::info("SuperAdmin user {$user->id} switched context to reseller {$reseller->id} ({$reseller->company_name}).");

        return back()->with('success', "زمینه کاری به نماینده «{$reseller->company_name}» تغییر یافت.");
    }

    /**
     * Authorize that the current authenticated user has access to the tenant.
     */
    protected function authorizeTenantAccess(Tenant $tenant): void
    {
        $user = Auth::user();
        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if ($isSuperAdmin) {
            return;
        }

        $reseller = $this->getReseller();

        if ((int)$tenant->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }
    }
}
