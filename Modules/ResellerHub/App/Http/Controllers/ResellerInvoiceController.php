<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Modules\ModuleMenuService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Actions\Invoices\CancelInvoiceAction;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\MarkAsPaidRequest;
use Modules\ResellerHub\App\Http\Requests\StorePaymentRequest;
use Modules\ResellerHub\App\Http\Requests\StoreResellerInvoiceRequest;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerInvoiceStatusRequest;
use Modules\ResellerHub\App\Services\Payment\ProcessInvoicePaymentService;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\InvoicePaymentService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Modules\ResellerHub\App\Events\InvoicePaid;
use Modules\ResellerHub\App\Events\InvoiceStatusChanged;
use Modules\ResellerHub\App\Events\PaymentCancelled;
use Modules\ResellerHub\App\Events\PaymentRecorded;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerPagination;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Modules\ResellerHub\App\Traits\HasSubAccountSearch;
use Modules\Settings\Entities\Setting;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Services\WalletService;
use Morilog\Jalali\Jalalian;
use Nwidart\Modules\Facades\Module;
use Throwable;

class ResellerInvoiceController extends Controller
{
    use HasReseller, HasSubAccountSearch, HasResellerSorting, HasResellerPagination;

    protected ResellerBillingService $billingService;
    protected ModuleCapabilityCatalogService $catalogService;
    protected ResellerWorkflowService $workflowService;
    protected InvoicePaymentService $invoicePaymentService;
    protected CancelInvoiceAction $cancelInvoiceAction;


    public function __construct(
        ResellerBillingService         $billingService,
        ModuleCapabilityCatalogService $catalogService,
        ResellerWorkflowService        $workflowService,
        ?InvoicePaymentService         $invoicePaymentService = null,
        ?CancelInvoiceAction           $cancelInvoiceAction = null
    )
    {
        $this->billingService = $billingService;
        $this->catalogService = $catalogService;
        $this->workflowService = $workflowService;
        $this->invoicePaymentService = $invoicePaymentService ?: app(InvoicePaymentService::class);
        $this->cancelInvoiceAction = $cancelInvoiceAction ?: app(CancelInvoiceAction::class);
    }

    protected function getResellerSettings(int $resellerId): array
    {
        $settings = ResellerSetting::allForReseller($resellerId);
        $currency = $settings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);

        return [$settings, $currency, $currencyLabel];
    }

    public function index(Request $request): View
    {
        $reseller = $this->getReseller();
        [$settings, $currency, $currencyLabel] = $this->getResellerSettings($reseller->id);

        $query = ResellerInvoice::where('reseller_id', $reseller->id)
            ->with(['tenant.subAccount', 'package', 'items', 'order', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $this->applySourceFilter($query, $request->input('source'));

        $this->applyInvoiceSearch($query, $request->input('search'));

        $this->applySorting($query, $request, [
            'invoice_number' => 'reseller_invoices.invoice_number',
            'subaccount' => 'reseller_invoices.sub_account_name',
            'subaccount_name' => 'reseller_invoices.sub_account_name',
            'name' => 'reseller_invoices.sub_account_name',
            'total' => 'reseller_invoices.total',
            'total_amount' => 'reseller_invoices.total',
            'amount' => 'reseller_invoices.total',
            'status' => 'reseller_invoices.status',
            'payment_status' => 'reseller_invoices.paid_amount',
            'paid_amount' => 'reseller_invoices.paid_amount',
            'issue_date' => fn($q, $dir) => $q->orderByRaw("reseller_invoices.issue_date IS NULL, reseller_invoices.issue_date {$dir}")->orderBy('reseller_invoices.id', $dir),
            'due_date' => fn($q, $dir) => $q->orderByRaw("reseller_invoices.due_date IS NULL, reseller_invoices.due_date {$dir}")->orderBy('reseller_invoices.id', $dir),
            'date' => 'reseller_invoices.created_at',
            'created_at' => 'reseller_invoices.created_at',
        ], defaultColumn: 'reseller_invoices.id');

        $perPage = $this->resolvePerPage($request);

        $invoices = $query->paginate($perPage)
            ->withQueryString();
        $totalsQuery = ResellerInvoice::where('reseller_id', $reseller->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')));

        $this->applySourceFilter($totalsQuery, $request->input('source'));
        $this->applyInvoiceSearch($totalsQuery, $request->input('search'));

        $cancelled = InvoiceStatus::CANCELLED->value;
        $paidStatus = InvoiceStatus::PAID->value;

        $totals = (clone $totalsQuery)
            ->selectRaw('COUNT(*) as aggregate_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN status <> ? THEN total ELSE 0 END), 0) as billed_total', [$cancelled])
            ->selectRaw('COALESCE(SUM(CASE WHEN status <> ? THEN paid_amount ELSE 0 END), 0) as paid_total', [$cancelled])
            ->selectRaw('COALESCE(SUM(CASE WHEN status NOT IN (?, ?) THEN 1 ELSE 0 END), 0) as unpaid_count', [$paidStatus, $cancelled])
            ->first();

        $grandCount = (int)($totals->aggregate_count ?? 0);
        $grandTotal = (int)($totals->billed_total ?? 0);
        $grandPaid = (int)($totals->paid_total ?? 0);
        $grandDue = max(0, $grandTotal - $grandPaid);
        $totalInvoices = $grandCount;
        $totalAmount = $grandTotal;
        $paidAmount = $grandPaid;
        $unpaidCount = (int)($totals->unpaid_count ?? 0);
        $unpaidAmount = $grandDue;

        return view('resellerhub::user.invoices.index', compact(
            'reseller',
            'invoices',
            'grandCount',
            'grandTotal',
            'grandPaid',
            'grandDue',
            'totalInvoices',
            'totalAmount',
            'paidAmount',
            'unpaidCount',
            'unpaidAmount',
            'settings',
            'currency',
            'currencyLabel'
        ));
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('reseller.tenants.create', $request->query());
    }

    public function store(StoreResellerInvoiceRequest $request): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();
        $validated = $request->validated();

        if (!isset($validated['meta']) || !is_array($validated['meta'])) {
            $validated['meta'] = [];
        }
        if (empty($validated['meta']['source'])) {
            $validated['meta']['source'] = 'manual_reseller';
            $validated['meta']['source_label'] = __('resellerhub::messages.invoices.source_manual');
        }

        $invoice = $this->billingService->createInvoice($reseller, $validated, Auth::user());

        $message = __('resellerhub::messages.invoices.created_success', ['number' => $invoice->invoice_number]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice' => $invoice->load(['items', 'tenant', 'package']),
                'redirect_url' => route('reseller.invoices.show', $invoice->id),
            ]);
        }

        return redirect()->route('reseller.invoices.show', $invoice->id)
            ->with('success', $message);
    }

    public function show(ResellerInvoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $reseller = $this->getReseller();

        [$settings, $currency, $currencyLabel] = $this->getResellerSettings($reseller->id);
        if (!empty($invoice->currency)) {
            $currency = (string)$invoice->currency;
            $currencyLabel = ResellerHelper::currencyLabel($currency);
        }
        $invoice->load(['items', 'tenant.package', 'package', 'order', 'creator', 'parentInvoice', 'renewals', 'payments']);

        $menuService = class_exists(ModuleMenuService::class) ? app(ModuleMenuService::class) : null;
        $moduleTitles = Cache::remember('resellerhub:module_title_map', 600, static function () use ($menuService) {
            $titles = [];

            if (class_exists(\App\Models\Module::class)) {
                foreach (\App\Models\Module::all() as $m) {
                    $t = $menuService
                        ? ($menuService->resolveModuleGroupTitle($m->slug, $m->name) ?: $m->name)
                        : $m->name;

                    $titles[$m->slug] = $t;
                    $titles[strtolower($m->slug)] = $t;
                    $titles[$m->name] = $t;
                }
            }

            return $titles;
        });

        return view('resellerhub::user.invoices.show', compact(
            'reseller',
            'invoice',
            'settings',
            'currency',
            'currencyLabel',
            'moduleTitles'
        ));
    }

    public function destroy(Request $request, ResellerInvoice $invoice): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->isPaid() || $invoice->calculatePaidAmount() > 0) {
            $msg = __('resellerhub::messages.invoices.cannot_delete_has_payments');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($invoice->order()->exists()) {
            $msg = __('resellerhub::messages.invoices.cannot_delete_has_orders');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        DB::transaction(static function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        }, 3);

        $msg = __('resellerhub::messages.invoices.deleted_success');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect_url' => route('reseller.invoices.index'),
            ]);
        }

        return redirect()->route('reseller.invoices.index')
            ->with('success', $msg);
    }

    public function cancel(Request $request, ResellerInvoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);
        $reseller = $this->getReseller();

        if ($invoice->reseller_id !== $reseller->id) {
            abort(403, __('resellerhub::messages.common.unauthorized'));
        }

        if ($invoice->isPaid()) {
            return back()->with('error', __('resellerhub::messages.invoices.cannot_cancel_paid'));
        }

        try {
            $this->cancelInvoiceAction->execute($invoice);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('reseller.invoices.show', $invoice->id)
            ->with('success', __('resellerhub::messages.invoices.cancelled_success'));
    }

    public function print(ResellerInvoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $reseller = $invoice->reseller
            ?: (Reseller::find($invoice->reseller_id) ?: $this->getResellerOrNull());

        if (!$reseller) {
            $reseller = Reseller::first();
        }

        if (!$reseller) {
            abort(404, __('resellerhub::messages.subscription.reseller_not_found'));
        }

        [$settings, $currency, $currencyLabel] = $this->getResellerSettings($reseller->id);
        if (!empty($invoice->currency)) {
            $currency = (string)$invoice->currency;
            $currencyLabel = ResellerHelper::currencyLabel($currency);
        }
        $invoice->load(['items', 'tenant.subAccount.user', 'tenant.package', 'package', 'payments.user']);

        $printMode = $settings['reseller_print_mode'] ?? 'standard';
        $viewName = $printMode === 'official' ? 'resellerhub::user.invoices.print_official' : 'resellerhub::user.invoices.print';

        $sellerInfo = [
            'name' => $reseller->company_name ?: ($reseller->brand_title ?: config('app.name')),
            'brand_title' => $reseller->brand_title ?: $reseller->company_name,
            'economic_number' => $settings['reseller_economic_number'] ?? '',
            'national_id' => $settings['reseller_national_id'] ?? '',
            'registration_number' => $settings['reseller_registration_number'] ?? '',
            'phone_fax' => $reseller->support_phone ?? '',
            'email' => $reseller->support_email ?? '',
            'address' => $settings['reseller_address'] ?? '',
            'postal_code' => $settings['reseller_postal_code'] ?? '',
            'stamp_signature_image' => $settings['brand_seal_signature'] ?? null,
        ];

        $siteName = $sellerInfo['brand_title'] ?: $sellerInfo['name'];
        $appLogo = $reseller->brand_logo;

        return view($viewName, compact(
            'reseller',
            'invoice',
            'settings',
            'currency',
            'currencyLabel',
            'sellerInfo',
            'siteName',
            'appLogo',
            'printMode'
        ));
    }

    public function payment(ResellerInvoice $invoice): View
    {
        $this->authorize('pay', $invoice);
        $reseller = $invoice->reseller ?: (Reseller::find($invoice->reseller_id) ?: $this->getReseller());

        $invoice->load(['items', 'tenant.subAccount.user', 'package', 'payments.user']);
        [$resellerSettings, $currency, $currencyLabel] = $this->getResellerSettings($reseller->id);
        $settings = Cache::remember('resellerhub:global_settings', 300, static function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        $paymentCurrency = strtolower($settings['payment_currency'] ?? 'toman');

        $conversionFactor = ResellerHelper::conversionFactor($currency, $paymentCurrency);

        $customerUser = $invoice->customerUser();

        $customerWallet = null;
        if ($customerUser
            && Module::has('Wallet')
            && Module::isEnabled('Wallet')
            && class_exists(Wallet::class)) {
            $customerWallet = Wallet::where('holder_type', get_class($customerUser))
                ->where('holder_id', $customerUser->id)
                ->first();
        }

        return view('resellerhub::user.invoices.payment', [
            'reseller' => $reseller,
            'invoice' => $invoice,
            'currency' => $paymentCurrency,
            'settings' => $settings,
            'resellerSettings' => $resellerSettings,
            'customerWallet' => $customerWallet,
            'conversionFactor' => $conversionFactor,
        ]);
    }

    public function storePayment(
        StorePaymentRequest          $request,
        ResellerInvoice              $invoice,
        ProcessInvoicePaymentService $paymentService
    ): RedirectResponse
    {
        $this->authorize('pay', $invoice);

        $lock = Cache::lock('reseller_invoice_payment_' . $invoice->id, 10);
        if (!$lock->get()) {
            return back()->with('error', __('resellerhub::messages.invoices.processing_lock'));
        }

        try {
            $result = $paymentService->process($invoice, $request->all(), $request->user());

            if ($result['type'] === 'redirect') {
                return redirect()->away($result['url']);
            }

            // A customer-submitted offline payment is only a claim: it is
            // recorded as pending and does not settle the invoice.
            if ($result['type'] === 'pending') {
                return redirect()->route('reseller.invoices.show', $invoice->id)
                    ->with('success', __('resellerhub::messages.invoices.payment_claim_registered'));
            }

            return redirect()->route('reseller.invoices.show', $invoice->id)
                ->with('success', __('resellerhub::messages.invoices.payment_successful'));
        } catch (Throwable $e) {
            Log::error('[ResellerInvoiceController@storePayment] ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', __('resellerhub::messages.invoices.payment_error', [
                'error' => __('resellerhub::messages.common.unexpected_error'),
            ]));
        } finally {
            optional($lock)->release();
        }
    }

    public function cancelPayment(ResellerInvoice $invoice, ResellerInvoicePayment $payment): RedirectResponse
    {
        $this->authorize('cancelPayment', [$invoice, $payment]);

        if ($payment->status === ResellerInvoicePayment::STATUS_CANCELED) {
            return back()->with('error', __('resellerhub::messages.invoices.payment_already_cancelled'));
        }

        $oldStatus = $invoice->status instanceof InvoiceStatus
            ? $invoice->status->value
            : (string)$invoice->status;

        try {
            DB::transaction(function () use ($invoice, $payment) {
                $lockedPayment = ResellerInvoicePayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($lockedPayment->status === ResellerInvoicePayment::STATUS_CANCELED) {
                    throw new \Modules\ResellerHub\App\Exceptions\ResellerHubException(__('resellerhub::messages.invoices.payment_already_cancelled'));
                }
                $lockedInvoice = ResellerInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

                $this->reversePaymentRecord($lockedInvoice, $lockedPayment);

                $lockedPayment->status = ResellerInvoicePayment::STATUS_CANCELED;
                $lockedPayment->save();

                $lockedInvoice->updatePaymentStatus(true);
                $this->syncOrdersForInvoice($lockedInvoice);
            }, 3);
        } catch (\Modules\ResellerHub\App\Exceptions\ResellerHubException $e) {
            return back()->with('error', $e->getMessage());
        }

        $invoice->refresh();

        // Trigger Domain Events (Workflows, Accounting Cancellation & Audit Trail)
        event(new PaymentCancelled($payment));
        event(new InvoiceStatusChanged(
            $invoice,
            $invoice->status instanceof InvoiceStatus ? $invoice->status->value : (string)$invoice->status,
            $oldStatus
        ));

        return redirect()->route('reseller.invoices.show', $invoice->id)
            ->with('success', __('resellerhub::messages.invoices.payment_cancelled_success'));
    }

    public function verifyOnlinePayment(Request $request, ResellerInvoice $invoice, ?string $gateway = null): RedirectResponse
    {
        $result = $this->invoicePaymentService->verifyOnlinePayment($invoice, $request->all(), $gateway);

        if ($result['success']) {
            $dest = Auth::check()
                ? redirect()->route('reseller.invoices.show', $invoice->id)
                : redirect()->route('login');

            return $dest->with('success', $result['message']);
        }

        if (Auth::check()) {
            return redirect()->route('reseller.invoices.payment', $invoice->id)->with('error', $result['message']);
        }

        return redirect()->route('login')->with('error', $result['message']);
    }

    public function markAsPaid(MarkAsPaidRequest $request, ResellerInvoice $invoice): JsonResponse|RedirectResponse
    {
        $this->authorize('pay', $invoice);

        $paymentData = [
            'payment_method' => $request->input('payment_method', __('resellerhub::messages.invoices.default_payment_method')),
            'transaction_ref' => $request->input('transaction_ref'),
        ];

        $order = $this->billingService->markAsPaid($invoice, $paymentData);
        $this->syncOrdersForInvoice($invoice);

        $message = __('resellerhub::messages.invoices.mark_as_paid_success', ['invoice' => $invoice->invoice_number, 'order' => $order->order_number]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'order' => $order,
                'redirect_url' => route('reseller.invoices.show', $invoice->id),
            ]);
        }

        return redirect()->route('reseller.invoices.show', $invoice->id)
            ->with('success', $message);
    }

    public function renew(Request $request, ResellerInvoice $invoice): JsonResponse|RedirectResponse
    {
        $this->authorize('renew', $invoice);

        $options = [
            'billing_cycle' => $request->input('billing_cycle', 'yearly'),
            'type' => $request->input('type', $invoice->type),
            'notes' => $request->input('notes'),
        ];

        if ($invoice->tenant) {
            $period = $invoice->order ? $invoice->order->renewalPeriod() : ['months' => 12];
            $months = (int)($period['months'] ?? 12);
            $limitCheck = $this->billingService->validateAdvanceRenewalLimit($invoice->tenant, $months, $invoice->reseller_id, $invoice->id);
            if (!$limitCheck['allowed']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $limitCheck['message']], 422);
                }
                return back()->with('error', $limitCheck['message']);
            }
        }

        try {
            $renewalInvoice = $this->billingService->createRenewalInvoice($invoice, $options);
            $message = __('resellerhub::messages.invoices.renewal_created', ['invoice' => $renewalInvoice->invoice_number]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'invoice' => $renewalInvoice->load(['items', 'tenant', 'package']),
                    'redirect_url' => route('reseller.invoices.show', $renewalInvoice->id),
                ]);
            }

            return redirect()->route('reseller.invoices.show', $renewalInvoice->id)
                ->with('success', $message);
        } catch (Throwable $e) {
            Log::error('[ResellerInvoiceController@renew] ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $message = __('resellerhub::messages.invoices.renewal_error', [
                'error' => __('resellerhub::messages.common.unexpected_error'),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('reseller.invoices.show', $invoice->id)
                ->with('error', $message);
        }
    }

    public function updateStatus(UpdateResellerInvoiceStatusRequest $request, ResellerInvoice $invoice): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];

        $this->authorize('updateStatus', $invoice);
        $oldStatus = $invoice->status instanceof InvoiceStatus
            ? $invoice->status->value
            : (string)$invoice->status;

        // Cancellation has side effects (payment reversal, order sync, audit
        // trail). It lives in exactly one place so a status change and a
        // "cancel" click can never drift apart.
        if ($newStatus === ResellerInvoice::STATUS_CANCELLED) {
            try {
                $this->cancelInvoiceAction->execute($invoice);
            } catch (Throwable $e) {
                Log::warning('[ResellerInvoiceController@updateStatus] Cancellation refused: ' . $e->getMessage(), [
                    'invoice_id' => $invoice->id,
                ]);

                $message = $e instanceof ResellerHubException
                    ? $e->getMessage()
                    : __('resellerhub::messages.common.unexpected_error');

                return ($request->expectsJson() || $request->ajax())
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : back()->with('error', $message);
            }

            $invoice->refresh();
            $message = __('resellerhub::messages.invoices.status_updated', ['status' => $invoice->status_label]);

            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['success' => true, 'message' => $message, 'invoice' => $invoice])
                : redirect()->route('reseller.invoices.show', $invoice->id)->with('success', $message);
        }

        $createdPayment = null;

        DB::transaction(function () use ($invoice, $newStatus, &$createdPayment) {
            /** @var ResellerInvoice $lockedInvoice */
            $lockedInvoice = ResellerInvoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($newStatus === ResellerInvoice::STATUS_PAID) {
                $total = (int)$lockedInvoice->total;
                if ($total < 0) {
                    throw new \RuntimeException('Invoices with a negative total cannot be marked as paid.');
                }

                $alreadyPaid = $lockedInvoice->calculatePaidAmount();
                $remaining = max(0, $total - $alreadyPaid);

                if ($remaining > 0) {
                    $createdPayment = $lockedInvoice->payments()->create([
                        'user_id' => auth()->id() ?? $lockedInvoice->created_by,
                        'amount' => $remaining,
                        'method' => 'manual',
                        'gateway' => null,
                        'paid_at' => now(),
                        'transaction_id' => 'MANUAL-' . $lockedInvoice->id . '-' . now()->format('YmdHis'),
                        'notes' => __('resellerhub::messages.invoices.manual_payment_note'),
                        'status' => 'paid',
                    ]);
                }

                $lockedInvoice->paid_amount = $lockedInvoice->calculatePaidAmount();
                $lockedInvoice->status = ResellerInvoice::STATUS_PAID;
                if (!$lockedInvoice->paid_at) {
                    $lockedInvoice->paid_at = now();
                }
            } else {
                $lockedInvoice->status = $newStatus;
            }

            $lockedInvoice->save();
            $this->syncOrdersForInvoice($lockedInvoice);
        }, 3);

        $invoice->refresh();
        if ($createdPayment) {
            event(new PaymentRecorded($createdPayment));
        }
        event(new InvoiceStatusChanged($invoice, $newStatus, $oldStatus));

        $message = __('resellerhub::messages.invoices.status_updated', ['status' => $invoice->status_label]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice' => $invoice,
            ]);
        }

        return redirect()->route('reseller.invoices.show', $invoice->id)
            ->with('success', $message);
    }

    /**
     * Synchronize connected order and underlying tenant based on invoice status.
     * Modeled 1:1 after Services Module syncOrdersForInvoice with full renewal support.
     */
    protected function syncOrdersForInvoice(ResellerInvoice $invoice): void
    {
        $this->billingService->syncOrdersForInvoice($invoice);
    }

    /**
     *
     * Delegates to CancelInvoiceAction so a single-payment cancellation and a
     * full invoice cancellation can never drift apart again.
     */
    protected function reversePaymentRecord(ResellerInvoice $invoice, ResellerInvoicePayment $payment): void
    {
        $this->cancelInvoiceAction->reversePayment($invoice, $payment);
    }
}
