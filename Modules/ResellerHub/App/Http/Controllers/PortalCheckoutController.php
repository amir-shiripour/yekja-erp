<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Jobs\ProvisionTenantJob;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Gateway\PaymentGatewayFactory;
use Modules\ResellerHub\App\Services\Payment\GatewayAmountResolver;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Throwable;

class PortalCheckoutController extends Controller
{
    public function __construct(
        protected ?PaymentGatewayFactory $gatewayFactory = null,
        protected ?GatewayAmountResolver $amounts = null
    )
    {
        $this->gatewayFactory = $gatewayFactory ?: app(PaymentGatewayFactory::class);
        $this->amounts = $amounts ?: app(GatewayAmountResolver::class);
    }

    /**
     * Handle payment verification callback from online gateways.
     */
    public function verify(Request $request): ?RedirectResponse
    {
        $invoiceId = $request->route('invoice') ?: $request->query('invoice') ?: $request->input('invoice');

        if (!$invoiceId) {
            Log::error('[PortalCheckoutController] Verify callback received without invoice ID.', $request->all());
            return redirect()->route('portal.checkout.index')
                ->with('error', __('resellerhub::messages.portal.missing_invoice'));
        }

        /** @var ResellerInvoice|null $invoice */
        $invoice = ResellerInvoice::with(['payments', 'tenant.subAccount.user'])->find($invoiceId);

        if (!$invoice) {
            Log::error("[PortalCheckoutController] Invoice #$invoiceId not found during gateway verification.");
            return redirect()->route('portal.checkout.index')
                ->with('error', __('resellerhub::messages.portal.invoice_not_found'));
        }

        Log::info(json_encode([
            'checkout_id' => $invoice->meta['checkout_id'] ?? $invoice->invoice_number,
            'invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'stage' => 'verify_received',
        ], JSON_UNESCAPED_UNICODE));

        $customerUser = $invoice->customerUser();
        $meta = is_array($invoice->meta) ? $invoice->meta : [];
        $gateway = (string)($meta['gateway'] ?? '');

        if ($gateway === '') {
            $candidate = (string)(
            $request->route('gateway')
                ?: $request->query('gateway')
                ?: $request->input('gateway')
                    ?: ''
            );

            $gateway = ($candidate !== '' && ResellerHelper::isGatewayActive($candidate)) ? $candidate : '';
        }

        if ($gateway === '') {
            Log::error("[PortalCheckoutController] Rejected verification for invoice #{$invoiceId}: no valid gateway.", [
                'meta_gateway' => $meta['gateway'] ?? null,
                'requested_gateway' => $request->input('gateway'),
            ]);

            return redirect()->route('portal.checkout.index')
                ->with('error', __('resellerhub::messages.portal.no_gateway'));
        }

        // If invoice is already paid (e.g. user refreshed the page or duplicate callback)
        if ($invoice->isPaid()) {
            Log::info("[PortalCheckoutController] Invoice #$invoiceId is already paid.");

            if (!Auth::check()) {
                return redirect()->route('portal.checkout.index')
                    ->with('error', __('resellerhub::messages.portal.already_settled'));
            }

            if ($invoice->tenant_id) {
                ProvisionTenantJob::dispatch($invoice->tenant_id);
                return redirect()->route('portal.onboarding.loading', ['tenant' => $invoice->tenant_id]);
            }
            return redirect()->route('user.dashboard');
        }

        $dataToVerify = array_merge($request->all(), [
            'Amount' => $invoice->total,
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->id,
        ]);

        try {
            $paymentService = $this->gatewayFactory->make($gateway);
            $result = $paymentService->verifyPayment($dataToVerify);

            $isSuccess = !empty($result['success']) || (isset($result['status']) && $result['status'] === 'success');

            if ($isSuccess) {
                $refId = (string)($result['ref_id'] ?? $result['reference_id'] ?? $result['authority'] ?? '');

                if ($refId === '') {
                    Log::error("[PortalCheckoutController] Gateway {$gateway} reported success without a transaction reference for invoice #{$invoiceId}.");

                    return redirect()->route('portal.checkout.index')
                        ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
                }

                if (ResellerInvoicePayment::isTransactionSettledElsewhere($gateway, $refId, $invoice->id)) {
                    Log::critical("[PortalCheckoutController] Security Alert: Transaction {$refId} on gateway {$gateway} is already settled on another invoice. Blocked replay attempt on invoice #{$invoiceId}.");

                    return redirect()->route('portal.checkout.index')
                        ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
                }

                if (!empty($result['is_duplicate']) && !ResellerInvoicePayment::forInvoiceAndTransaction($invoice->id, $refId)->exists()) {
                    Log::warning("[PortalCheckoutController] Gateway reported transaction {$refId} as already verified, but it is not registered for invoice #{$invoiceId}. Settlement refused.");

                    return redirect()->route('portal.checkout.index')
                        ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
                }

                $reportedAmount = $result['amount'] ?? $result['Amount'] ?? null;
                if (!is_numeric($reportedAmount)) {
                    Log::critical("[PortalCheckoutController] Security Alert: Gateway {$gateway} did not report verified amount for invoice #{$invoiceId}. Verification rejected.");

                    return redirect()->route('portal.checkout.index')
                        ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
                }

                $reported = (int)round((float)$reportedAmount);
                $expected = $this->amounts->forInvoice($invoice)['amount'];

                if (abs($reported - $expected) > 1) {
                    Log::critical("[PortalCheckoutController] Security Alert: Amount mismatch on invoice #{$invoiceId}: gateway={$reported}, expected={$expected}.");

                    return redirect()->route('portal.checkout.index')
                        ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
                }

                $gatewayLabel = ResellerHelper::getGatewayName($gateway);
                $settled = DB::transaction(static function () use ($invoice, $refId, $gateway, $gatewayLabel, $customerUser) {
                    /** @var ResellerInvoice|null $locked */
                    $locked = ResellerInvoice::query()->whereKey($invoice->getKey())->lockForUpdate()->first();

                    if (!$locked || $locked->isPaid()) {
                        return false;
                    }

                    // 1. Mark invoice as paid
                    $locked->update([
                        'status' => InvoiceStatus::PAID->value,
                        'paid_at' => now(),
                        'paid_amount' => $locked->total,
                        'payment_method' => 'online',
                        'transaction_ref' => $refId,
                    ]);

                    // 2. Record payment in invoice payments history
                    if (!ResellerInvoicePayment::forInvoiceAndTransaction($locked->id, $refId)->exists()) {
                        ResellerInvoicePayment::create([
                            'invoice_id' => $locked->id,
                            'user_id' => $customerUser?->id,
                            'amount' => $locked->total,
                            'method' => 'online',
                            'gateway' => $gateway,
                            'paid_at' => now(),
                            'transaction_id' => $refId,
                            'status' => 'paid',
                            'notes' => __('resellerhub::messages.portal.payment_history_note', ['gateway' => $gatewayLabel]),
                        ]);
                    }

                    // 3. Ensure order conversion and activation
                    if (class_exists(ResellerBillingService::class)) {
                        $billingService = app(ResellerBillingService::class);
                        if (!$locked->order()->exists()) {
                            $billingService->convertInvoiceToOrder($locked);
                        } else {
                            $billingService->syncOrdersForInvoice($locked);
                        }
                    }

                    return true;
                }, 3);

                if ($settled) {
                    $invoice->refresh();
                }

                // 4. Dispatch provisioning job or return to active tenant dashboard
                if ($invoice->tenant_id) {
                    $tenantModel = Tenant::find($invoice->tenant_id);

                    if ($tenantModel && $tenantModel->isActive()) {
                        if (!Auth::check()) {
                            // The callback is public; never fabricate a session.
                            return redirect()->route('login')
                                ->with('success', __('resellerhub::messages.portal.payment_success'));
                        }

                        $tenantManager = app(TenantManager::class);
                        $targetUrl = $tenantManager->getTenantUrl($tenantModel, 'user/dashboard');

                        return redirect()->away($targetUrl)
                            ->with('success', __('resellerhub::messages.portal.payment_success'));
                    }

                    ProvisionTenantJob::dispatch($invoice->tenant_id)->afterCommit();

                    return redirect()
                        ->route('portal.onboarding.loading', ['tenant' => $invoice->tenant_id]);
                }

                if (!Auth::check()) {
                    return redirect()->route('login')
                        ->with('success', __('resellerhub::messages.portal.payment_success'));
                }

                return redirect()->route('user.dashboard')
                    ->with('success', __('resellerhub::messages.portal.payment_processing'));
            }

            $errorMessage = $result['message'] ?? __('resellerhub::messages.portal.payment_failed');
            Log::warning("[PortalCheckoutController] Payment verification failed for invoice #{$invoiceId}: {$errorMessage}", [
                'gateway' => $gateway,
                'result' => $result,
            ]);

            return redirect()->route('portal.checkout.index')->with('error', $errorMessage);

        } catch (Throwable $e) {
            Log::error("[PortalCheckoutController] Exception verifying payment for invoice #{$invoiceId}: " . $e->getMessage(), [
                'gateway' => $gateway,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('portal.checkout.index')
                ->with('error', __('resellerhub::messages.portal.payment_verify_error'));
        }
    }

    /**
     * Customer-facing online bank payment page/trigger for an invoice.
     * Strictly reads active online bank gateways from settings.
     */
    public function payInvoice(Request $request, $invoiceId): View|RedirectResponse
    {
        /** @var ResellerInvoice $invoice */
        $invoice = ResellerInvoice::with(['tenant.subAccount', 'package'])->findOrFail($invoiceId);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $this->authorize('view', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('reseller.invoices.print', $invoice->id);
        }

        if ($request->has('print') || $request->has('view')) {
            return redirect()->route('reseller.invoices.print', $invoice->id);
        }

        // Strictly fetch active online payment gateways from settings
        $availableGateways = ResellerHelper::getAvailableGateways();

        if (empty($availableGateways)) {
            return back()->with('error', __('resellerhub::messages.portal.no_gateway'));
        }

        $selectedGateway = $request->query('gateway') ?: $request->input('gateway');

        // If gateway explicitly specified, proceed directly to payment
        if ($selectedGateway && isset($availableGateways[$selectedGateway])) {
            return $this->initiateGatewayPayment($invoice, $selectedGateway, $user);
        }

        if (count($availableGateways) === 1) {
            return $this->initiateGatewayPayment($invoice, array_key_first($availableGateways), $user);
        }

        // Multiple active gateways: render clean, minimal customer checkout view
        return view('resellerhub::portal.pay-invoice', [
            'invoice' => $invoice,
            'availableGateways' => $availableGateways,
            'defaultGateway' => ResellerHelper::getDefaultGateway(),
            'user' => $user,
        ]);
    }

    /**
     * Process chosen online bank gateway payment.
     */
    public function processInvoicePayment(Request $request, $invoiceId): RedirectResponse
    {
        /** @var ResellerInvoice $invoice */
        $invoice = ResellerInvoice::with(['tenant.subAccount', 'package'])->findOrFail($invoiceId);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $this->authorize('pay', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('user.dashboard')
                ->with('success', __('resellerhub::messages.portal.already_settled'));
        }

        $availableGateways = ResellerHelper::getAvailableGateways();
        $gateway = $request->input('gateway') ?: ResellerHelper::getDefaultGateway();

        if (!isset($availableGateways[$gateway])) {
            $gateway = !empty($availableGateways) ? array_key_first($availableGateways) : null;
        }

        if (!$gateway) {
            return back()->with('error', __('resellerhub::messages.portal.no_gateway_available'));
        }

        return $this->initiateGatewayPayment($invoice, $gateway, $user);
    }

    /**
     * Connect to the selected bank gateway via PaymentService and redirect away to bank.
     */
    protected function initiateGatewayPayment(ResellerInvoice $invoice, string $gateway, User $user): RedirectResponse
    {
        try {
            $paymentService = $this->gatewayFactory->make($gateway);
            $verifyUrl = route('portal.checkout.verify', [
                'invoice' => $invoice->id,
                'gateway' => $gateway,
            ]);

            $meta = $invoice->meta ?: [];
            $meta['gateway'] = $gateway;
            $meta['gateway_name'] = ResellerHelper::getGatewayName($gateway);
            $invoice->meta = $meta;
            $invoice->save();

            $result = $paymentService->requestPayment(
                (float)$this->amounts->forInvoice($invoice)['amount'],
                __('resellerhub::messages.portal.order_payment_title', ['number' => $invoice->invoice_number]),
                $user->email ?? $invoice->sub_account_email ?? '',
                $user->mobile ?: $invoice->sub_account_mobile,
                $verifyUrl
            );

            if (!empty($result['success']) && !empty($result['payment_url'])) {
                return redirect()->away($result['payment_url']);
            }

            $gatewayLabel = ResellerHelper::getGatewayName($gateway);
            $msg = $result['message'] ?? 'Error';
            return back()->with('error', __('resellerhub::messages.portal.gateway_connection_error', ['gateway' => $gatewayLabel, 'message' => $msg]));
        } catch (Throwable $e) {
            Log::error("[PortalCheckoutController] Error initiating payment for invoice #$invoice->id: " . $e->getMessage(), [
                'gateway' => $gateway,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', __('resellerhub::messages.portal.gateway_server_error'));
        }
    }
}
