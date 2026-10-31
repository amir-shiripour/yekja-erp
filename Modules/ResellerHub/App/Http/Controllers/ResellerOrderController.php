<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ResellerHub\App\Enums\OrderStatus;
use Modules\ResellerHub\App\Events\OrderStatusChanged;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\AddOrderItemsRequest;
use Modules\ResellerHub\App\Http\Requests\RemoveOrderItemRequest;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerOrderRequest;
use Modules\ResellerHub\App\Http\Requests\UpgradeOrderPackageRequest;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerPagination;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Modules\ResellerHub\App\Traits\HasSubAccountSearch;
use Morilog\Jalali\Jalalian;
use Throwable;

class ResellerOrderController extends Controller
{
    use HasReseller, HasSubAccountSearch, HasResellerSorting, HasResellerPagination;

    protected ResellerBillingService $billingService;

    public function __construct(ResellerBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request): View
    {
        $reseller = $this->getReseller();
        $query = ResellerOrder::query()
            ->where('reseller_id', $reseller->id)
            ->with(['tenant.subAccount', 'tenant.package', 'invoice.package']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('package_id')) {
            $pkgId = $request->input('package_id');
            $query->whereHas('invoice', fn($q) => $q->where('package_id', $pkgId));
        }

        $this->applySourceFilter($query, $request->input('source'));

        $this->applyOrderSearch($query, $request->input('search'));

        $this->applySorting($query, $request, [
            'order_number' => 'reseller_orders.order_number',
            'subaccount' => 'reseller_orders.sub_account_name',
            'subaccount_name' => 'reseller_orders.sub_account_name',
            'name' => 'reseller_orders.sub_account_name',
            'service' => 'reseller_orders.sub_account_name',
            'package' => 'reseller_orders.sub_account_name',
            'invoice' => 'reseller_orders.invoice_id',
            'invoice_id' => 'reseller_orders.invoice_id',
            'total_amount' => 'reseller_orders.total_amount',
            'amount' => 'reseller_orders.total_amount',
            'renewal_price' => 'reseller_orders.renewal_price',
            'issue_date' => fn($q, $dir) => $q->orderByRaw("reseller_orders.start_date IS NULL, reseller_orders.start_date {$dir}")->orderBy('reseller_orders.id', $dir),
            'renewal_date' => fn($q, $dir) => $q->orderByRaw("reseller_orders.renewal_date IS NULL, reseller_orders.renewal_date {$dir}")->orderBy('reseller_orders.id', $dir),
            'status' => 'reseller_orders.status',
            'date' => 'reseller_orders.created_at',
            'created_at' => 'reseller_orders.created_at',
        ], defaultColumn: 'reseller_orders.id');

        $perPage = $this->resolvePerPage($request);

        $orders = $query->paginate($perPage)->withQueryString();

        $packages = ResellerPackage::where('reseller_id', $reseller->id)
            ->orWhereNull('reseller_id')
            ->orderBy('name')
            ->get();

        $statuses = OrderStatus::allForSelect();

        // Clean stats query across all matching orders without pagination/sorting
        $statsQuery = ResellerOrder::where('reseller_id', $reseller->id);

        if ($request->filled('status')) {
            $statsQuery->where('status', $request->input('status'));
        }

        if ($request->filled('package_id')) {
            $pkgId = $request->input('package_id');
            $statsQuery->whereHas('invoice', fn($q) => $q->where('package_id', $pkgId));
        }

        $this->applySourceFilter($statsQuery, $request->input('source'));
        $this->applyOrderSearch($statsQuery, $request->input('search'));

        $totalOrders = (clone $statsQuery)->count();
        $activeOrders = (clone $statsQuery)->where('status', ResellerOrder::STATUS_ACTIVE)->count();
        $pendingOrders = (clone $statsQuery)->where('status', ResellerOrder::STATUS_PENDING)->count();
        $totalSales = (int)(clone $statsQuery)->whereIn('status', [ResellerOrder::STATUS_ACTIVE, ResellerOrder::STATUS_COMPLETED])->sum('total_amount');

        $settings = ResellerSetting::allForReseller($reseller->id);
        $currency = $settings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);

        return view('resellerhub::user.orders.index', compact(
            'reseller',
            'orders',
            'packages',
            'statuses',
            'totalOrders',
            'activeOrders',
            'pendingOrders',
            'totalSales',
            'settings',
            'currency',
            'currencyLabel'
        ));
    }

    public function show(ResellerOrder $order): View
    {
        $this->authorize('view', $order);
        $reseller = $this->getReseller();

        $settings = ResellerSetting::allForReseller($reseller->id);
        $currency = $settings['currency'] ?? 'toman';
        if (!empty($order->currency)) {
            $currency = (string)$order->currency;
        }
        $currencyLabel = ResellerHelper::currencyLabel($currency);

        $order->load(['tenant.modules', 'tenant.subAccount', 'invoice.items', 'invoice.payments', 'creator']);

        try {
            $relatedInvoices = $order->resolveRelatedInvoices();
        } catch (Throwable $e) {
            $relatedInvoices = collect();
        }

        $statuses = OrderStatus::allForSelect();

        $availablePackages = ResellerPackage::where(static function ($q) use ($reseller) {
            $q->where('reseller_id', $reseller->id)
                ->orWhereNull('reseller_id');
        })->active()->orderBy('sort_order')->orderBy('id')->get();

        $catalogService = app(ModuleCapabilityCatalogService::class);
        $fullCatalog = $catalogService->getCatalogForReseller($reseller);

        $packageModuleSlugs = (array)($order->package?->modules ?? []);
        $existingModuleSlugs = collect($order->modules_snapshot ?? [])
            ->where('type', 'module')
            ->pluck('slug')
            ->merge($packageModuleSlugs)
            ->map(fn($s) => Str::lower($s))
            ->unique()
            ->toArray();

        $packageFeatureSlugs = (array)($order->package?->features ?? []);
        $existingFeatureSlugs = collect($order->modules_snapshot ?? [])
            ->where('type', 'feature')
            ->pluck('slug')
            ->merge($packageFeatureSlugs)
            ->map(fn($s) => Str::lower($s))
            ->unique()
            ->toArray();

        $addableModules = $fullCatalog
            ->filter(fn($m) => $m['is_allowed'])
            ->map(function ($m) use ($existingModuleSlugs, $existingFeatureSlugs, $order) {
                $cycle = $order->billing_cycle ?? 'yearly';
                $orderMonths = ResellerOrder::cycleToMonths($cycle);
                $cyclePrice = match ($cycle) {
                    'monthly' => (int)($m['base_monthly_price'] ?? 0),
                    'quarterly' => (int)($m['base_quarterly_price'] ?? 0) ?: ((int)($m['base_monthly_price'] ?? 0) * 3),
                    'semi_annual' => (int)($m['base_semi_annual_price'] ?? 0) ?: ((int)($m['base_monthly_price'] ?? 0) * 6),
                    'yearly', 'annual' => (int)($m['base_yearly_price'] ?? 0) ?: ((int)($m['base_monthly_price'] ?? 0) * 12),
                    default => ((int)($m['base_monthly_price'] ?? 0) * $orderMonths),
                };

                $features = collect($m['features'] ?? [])
                    ->values()
                    ->map(function ($f) use ($cycle, $orderMonths, $existingFeatureSlugs) {
                        $fp = match ($cycle) {
                            'monthly' => (int)($f['monthly_price'] ?? 0),
                            'quarterly' => (int)($f['quarterly_price'] ?? 0) ?: ((int)($f['monthly_price'] ?? 0) * 3),
                            'semi_annual' => (int)($f['semi_annual_price'] ?? 0) ?: ((int)($f['monthly_price'] ?? 0) * 6),
                            'yearly', 'annual' => (int)($f['yearly_price'] ?? 0) ?: ((int)($f['monthly_price'] ?? 0) * 12),
                            default => ((int)($f['monthly_price'] ?? 0) * $orderMonths),
                        };
                        return array_merge($f, [
                            'cycle_price' => $fp,
                            'already_in_order' => in_array(Str::lower($f['key']), $existingFeatureSlugs, true),
                        ]);
                    })
                    ->toArray();

                return [
                    'slug' => $m['slug'],
                    'title' => $m['title'],
                    'description' => $m['description'] ?? '',
                    'already_in_order' => in_array(Str::lower($m['slug']), $existingModuleSlugs, true),
                    'cycle_price' => $cyclePrice,
                    'monthly_price' => (int)($m['base_monthly_price'] ?? 0),
                    'quarterly_price' => (int)($m['base_quarterly_price'] ?? 0),
                    'semi_annual_price' => (int)($m['base_semi_annual_price'] ?? 0),
                    'yearly_price' => (int)($m['base_yearly_price'] ?? 0),
                    'features' => $features,
                ];
            })
            ->values();

        $moduleTitles = $fullCatalog->pluck('title', 'slug')->toArray();

        return view('resellerhub::user.orders.show', compact(
            'reseller',
            'order',
            'settings',
            'currency',
            'currencyLabel',
            'relatedInvoices',
            'statuses',
            'availablePackages',
            'addableModules',
            'moduleTitles'
        ));
    }

    public function update(UpdateResellerOrderRequest $request, ResellerOrder $order): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validated();

        // Normalize status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'suspended') {
                $validated['status'] = ResellerOrder::STATUS_INACTIVE;
            } elseif ($validated['status'] === 'completed') {
                $validated['status'] = ResellerOrder::STATUS_ACTIVE;
            }
        }

        // Parse dates if provided in Persian or other formats
        if (!empty($validated['end_date'])) {
            $parsed = JalaliDateHelper::parseToGregorian($validated['end_date']);
            if ($parsed) {
                $validated['end_date'] = $parsed;
            }
        }
        if (!empty($validated['renewal_date'])) {
            $parsed = JalaliDateHelper::parseToGregorian($validated['renewal_date']);
            if ($parsed) {
                $validated['renewal_date'] = $parsed;
            }
        }

        $oldStatus = $order->status;
        $order->update($validated);

        if (isset($validated['status']) && $oldStatus !== $order->status) {
            event(new OrderStatusChanged($order, $order->status, $oldStatus));
        }

        $message = __('resellerhub::messages.orders.updated_success', ['order' => $order->order_number]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'order' => $order,
            ]);
        }

        return redirect()->route('reseller.orders.show', $order->id)
            ->with('success', $message);
    }

    public function provision(Request $request, ResellerOrder $order): JsonResponse|RedirectResponse
    {
        $this->authorize('provision', $order);

        if (!$order->tenant_id) {
            $msg = __('resellerhub::messages.orders.tenant_required_for_provision');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $res = false;
        try {
            DB::transaction(function () use ($order, &$res) {
                $res = $this->billingService->provisionOrderToTenant($order);
                if (!$res) {
                    throw new ResellerHubException(__('resellerhub::messages.orders.provision_failed'));
                }

                if (!$order->isActive()) {
                    $oldStatus = $order->status;
                    $order->update(['status' => ResellerOrder::STATUS_ACTIVE]);
                    event(new OrderStatusChanged($order, ResellerOrder::STATUS_ACTIVE, $oldStatus));
                }
            }, 3);
        } catch (Throwable $e) {
            Log::error("[ResellerOrderController] Provisioning failed for order {$order->id}: " . $e->getMessage());
            $res = false;
        }

        $msg = $res
            ? __('resellerhub::messages.orders.provision_success', ['order' => $order->order_number])
            : __('resellerhub::messages.orders.provision_failed');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $res,
                'message' => $msg,
                'order' => $order,
            ]);
        }

        return redirect()->back()->with($res ? 'success' : 'error', $msg);
    }

    public function destroy(ResellerOrder $order): RedirectResponse
    {
        $this->authorize('delete', $order);
        return back()->with('error', __('resellerhub::messages.orders.cannot_delete'));
    }

    /**
     * Create a manual renewal invoice for the order, advancing renewal date to the next cycle.
     */
    public function createRenewalInvoice(Request $request, ResellerOrder $order): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        if ($order->reseller_id !== $reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $order->load(['tenant', 'invoice.items', 'invoice.package']);

        $period = $order->renewalPeriod();
        $months = (int)($period['months'] ?? 12);
        if ($order->tenant) {
            $limitCheck = $this->billingService->validateAdvanceRenewalLimit($order->tenant, $months, $order->reseller_id);
            if (!$limitCheck['allowed']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $limitCheck['message']], 422);
                }
                return back()->with('error', $limitCheck['message']);
            }
        }

        try {
            $renewalInvoice = $this->billingService->createRenewalInvoiceFromOrder($order, [
                'manual' => true,
            ]);
        } catch (Throwable $e) {
            $msg = __('resellerhub::messages.orders.renewal_error', ['error' => $e->getMessage()]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $periodLabel = $renewalInvoice->meta['period_label'] ?? '';
        $jalaliNext = $order->renewal_date ? reseller_format_jalali($order->renewal_date) : '—';
        $message = __('resellerhub::messages.orders.renewal_invoice_created_detail', [
            'period' => $periodLabel,
            'invoice' => $renewalInvoice->invoice_number,
            'next' => $jalaliNext,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice' => $renewalInvoice,
                'redirect_url' => route('reseller.invoices.show', $renewalInvoice->id),
            ]);
        }

        return redirect()->route('reseller.invoices.show', $renewalInvoice->id)
            ->with('success', $message);
    }

    /**
     * Create an upgrade invoice for the order to transition to a higher package.
     */
    public function upgradePackage(UpgradeOrderPackageRequest $request, ResellerOrder $order): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $order);
        $reseller = $this->getReseller();

        if ($order->reseller_id !== $reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        $validated = $request->validated();

        $newPackageId = (int)$validated['new_package_id'];
        $calcMode = $validated['calculation_mode'] ?? 'prorated';
        $customPrice = !empty($validated['custom_price']) ? (int)$validated['custom_price'] : null;
        $issueInvoice = $request->boolean('issue_invoice', true);

        if (!$issueInvoice) {
            try {
                $this->billingService->upgradeOrderPackageWithoutInvoice($order, $newPackageId, [
                    'notes' => $request->input('notes'),
                ]);
            } catch (Throwable $e) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                return back()->with('error', $e->getMessage());
            }

            $message = __('resellerhub::messages.orders.upgrade_without_invoice_success');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => route('reseller.orders.show', $order->id),
                ]);
            }

            return redirect()->route('reseller.orders.show', $order->id)
                ->with('success', $message);
        }

        try {
            $upgradeInvoice = $this->billingService->createUpgradeInvoiceFromOrder($order, $newPackageId, [
                'calculation_mode' => $calcMode,
                'custom_price' => $customPrice,
                'notes' => $request->input('notes'),
            ]);
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        $message = __('resellerhub::messages.orders.upgrade_invoice_created', ['invoice' => $upgradeInvoice->invoice_number]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice' => $upgradeInvoice,
                'redirect_url' => route('reseller.invoices.show', $upgradeInvoice->id),
            ]);
        }

        return redirect()->route('reseller.invoices.show', $upgradeInvoice->id)
            ->with('success', $message);
    }

    public function addItems(AddOrderItemsRequest $request, ResellerOrder $order): JsonResponse
    {
        $this->authorize('update', $order);
        $reseller = $this->getReseller();

        if ($order->reseller_id !== $reseller->id) {
            return response()->json(['success' => false, 'message' => __('resellerhub::messages.common.unauthorized')], 403);
        }

        $validated = $request->validated();
        $issueInvoice = $request->boolean('issue_invoice', true);

        try {
            $result = $this->billingService->addItemsToOrder(
                $order,
                $validated['items'],
                $reseller,
                $validated['notes'] ?? null,
                $issueInvoice,
                Auth::id()
            );

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function removeItem(RemoveOrderItemRequest $request, ResellerOrder $order): JsonResponse
    {
        $this->authorize('update', $order);
        $reseller = $this->getReseller();

        if ($order->reseller_id !== $reseller->id) {
            return response()->json(['success' => false, 'message' => __('resellerhub::messages.common.unauthorized')], 403);
        }

        $validated = $request->validated();
        $itemsList = !empty($validated['items']) && is_array($validated['items'])
            ? $validated['items']
            : [['type' => $validated['type'], 'slug' => $validated['slug'], 'title' => $validated['title'] ?? null]];

        try {
            $result = $this->billingService->removeItemsFromOrder($order, $itemsList, $reseller);
            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => __('resellerhub::messages.orders.remove_item_error', ['error' => $e->getMessage()])], 422);
        }
    }
}
