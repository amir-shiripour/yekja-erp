<?php

namespace Modules\ResellerHub\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\App\Services\AccountingEngine;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\Workflows\Services\WorkflowEngine;
use Nwidart\Modules\Facades\Module;
use Throwable;

class ResellerWorkflowService
{
    /**
     * Trigger workflows when a reseller invoice is created.
     */
    public function onInvoiceCreated(ResellerInvoice $invoice): void
    {
        $payload = [
            'invoice_number' => $invoice->invoice_number,
            'total' => (int)$invoice->total,
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_invoice_created', 'INVOICE', $invoice->id, $payload);

        if ($invoice->isPaid()) {
            $this->onInvoicePaid($invoice);
        }
    }

    /**
     * Trigger workflows when an invoice is fully paid.
     */
    public function onInvoicePaid(ResellerInvoice $invoice, ?ResellerInvoicePayment $lastPayment = null): void
    {
        $payload = [
            'invoice_number' => $invoice->invoice_number,
            'amount' => $lastPayment ? (int)$lastPayment->amount : (int)$invoice->total,
            'is_paid' => true,
            'is_overdue' => false,
            'remaining' => 0,
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_invoice_paid', 'INVOICE', $invoice->id, $payload);
    }

    /**
     * Trigger workflows when an invoice status changes.
     */
    public function onInvoiceStatusChanged(ResellerInvoice $invoice, string $newStatus, ?string $oldStatus = null): void
    {
        if ($newStatus === $oldStatus) {
            return;
        }

        $payload = [
            'invoice_number' => $invoice->invoice_number,
            'status' => $newStatus,
            'current_status' => $newStatus,
            'previous_status' => $oldStatus,
            'is_paid' => $invoice->isPaid(),
            'is_overdue' => $invoice->isOverdue(),
            'remaining' => (int)$invoice->remainingAmount(),
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_invoice_status_changed', 'INVOICE', $invoice->id, $payload);

        if ($newStatus === ResellerInvoice::STATUS_PAID) {
            $this->onInvoicePaid($invoice);
        } elseif ($newStatus === ResellerInvoice::STATUS_CANCELLED) {
            $this->onInvoiceCancelled($invoice);
        } elseif ($newStatus === ResellerInvoice::STATUS_UNPAID || $newStatus === ResellerInvoice::STATUS_OVERDUE) {
            $this->onInvoiceUnpaid($invoice);
        }
    }

    /**
     * Trigger workflows when an invoice is unpaid or reverted.
     */
    public function onInvoiceUnpaid(ResellerInvoice $invoice): void
    {
        $payload = [
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'is_paid' => false,
            'is_overdue' => $invoice->isOverdue(),
            'remaining' => (int)$invoice->remainingAmount(),
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_invoice_unpaid', 'INVOICE', $invoice->id, $payload);
    }


    public function onInvoiceCancelled(ResellerInvoice $invoice): void
    {
        $payload = [
            'invoice_number' => $invoice->invoice_number,
            'is_cancelled' => true,
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_invoice_cancelled', 'INVOICE', $invoice->id, $payload);
    }

    /**
     * Record in Accounting module and trigger payment workflows.
     */
    public function onPaymentRecorded(ResellerInvoicePayment $payment): void
    {
        // 1. Sync with Accounting Module (same as Services Module)
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            try {
                if (class_exists(AccountingEngine::class)) {
                    $engine = app(AccountingEngine::class);
                    $engine->recordServicePayment($payment);
                }
            } catch (Throwable $e) {
                Log::error('[AccountingEngine] Error recording reseller payment #' . $payment->id . ': ' . $e->getMessage());
            }
        }

        // 2. Trigger Workflows
        $payload = [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount' => (int)$payment->amount,
            'method' => $payment->method,
            'gateway' => $payment->gateway,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
        ];

        $this->startWorkflow('reseller_payment_created', 'PAYMENT', $payment->id, $payload);
    }


    public function onPaymentCancelled(ResellerInvoicePayment $payment): void
    {
        // 1. Cancel in Accounting Module (same as Services Module)
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            try {
                if (class_exists(AccountingEngine::class)) {
                    $engine = app(AccountingEngine::class);
                    $engine->cancelServicePayment($payment);
                }
            } catch (Throwable $e) {
                Log::error('[AccountingEngine] Error cancelling reseller payment in accounting #' . $payment->id . ': ' . $e->getMessage());
            }
        }

        // 2. Trigger Workflows
        $payload = [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount' => (int)$payment->amount,
            'method' => $payment->method,
            'status' => 'cancelled',
            'transaction_id' => $payment->transaction_id,
        ];

        $this->startWorkflow('reseller_payment_cancelled', 'PAYMENT', $payment->id, $payload);

        if ($payment->invoice && !$payment->invoice->isPaid()) {
            $this->onInvoiceUnpaid($payment->invoice);
        }
    }

    /**
     * Trigger workflows when a reseller order is created.
     */
    public function onOrderCreated(ResellerOrder $order): void
    {
        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'billing_cycle' => $order->billing_cycle,
            'total_amount' => (int)$order->total_amount,
            'tenant_id' => $order->tenant_id,
            'reseller_id' => $order->reseller_id,
        ];

        $this->startWorkflow('reseller_order_created', 'ORDER', $order->id, $payload);
    }

    /**
     * Trigger workflows when an order status changes.
     */
    public function onOrderStatusChanged(ResellerOrder $order, string $newStatus, ?string $oldStatus = null): void
    {
        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $newStatus,
            'current_status' => $newStatus,
            'previous_status' => $oldStatus,
            'total_amount' => (int)$order->total_amount,
            'tenant_id' => $order->tenant_id,
            'reseller_id' => $order->reseller_id,
        ];

        $this->startWorkflow('reseller_order_status_changed', 'ORDER', $order->id, $payload);
    }

    /**
     * Trigger workflows when an order is renewed.
     */
    public function onOrderRenewed(ResellerOrder $order): void
    {
        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'end_date' => $order->end_date?->format('Y-m-d'),
            'renewal_date' => $order->renewal_date?->format('Y-m-d'),
            'tenant_id' => $order->tenant_id,
            'reseller_id' => $order->reseller_id,
        ];

        $this->startWorkflow('reseller_order_renewed', 'ORDER', $order->id, $payload);
    }

    /**
     * Trigger workflows when an order is provisioned to tenant.
     */
    public function onOrderProvisioned(ResellerOrder $order): void
    {
        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'end_date' => $order->end_date?->format('Y-m-d'),
            'renewal_date' => $order->renewal_date?->format('Y-m-d'),
            'tenant_id' => $order->tenant_id,
            'reseller_id' => $order->reseller_id,
        ];

        $this->startWorkflow('reseller_order_provisioned', 'ORDER', $order->id, $payload);
    }

    /**
     * Trigger workflows when a tenant resource usage reaches warning/critical threshold.
     */
    public function onQuotaThresholdExceeded($tenant, $quotaKey, int $limit, int $used): void
    {
        $keyName = is_object($quotaKey) && isset($quotaKey->value) ? $quotaKey->value : (string)$quotaKey;
        $qEnum = QuotaKey::tryFrom($keyName);
        $percent = $limit > 0 ? (int)round(($used / $limit) * 100) : 100;

        $payload = [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name ?? 'کلینیک',
            'quota_key' => $keyName,
            'reseller_quota_title' => $qEnum?->shortTitle() ?? $keyName,
            'reseller_quota_used' => ResellerHelper::toPersianDigits($used),
            'reseller_quota_limit' => ResellerHelper::toPersianDigits($limit),
            'reseller_quota_percent' => ResellerHelper::toPersianDigits($percent),
            'limit' => $limit,
            'used' => $used,
            'percentage' => $percent,
            'reseller_id' => $tenant->reseller_id,
        ];

        $this->startWorkflow('reseller_quota_threshold_exceeded', 'TENANT', $tenant->id, $payload);
    }

    /**
     * Trigger workflows when an online payment gateway transaction fails.
     */
    public function onPaymentFailed(ResellerInvoice $invoice, ?string $gateway = null, ?string $errorMessage = null): void
    {
        $payload = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => (int)$invoice->total,
            'gateway' => $gateway,
            'error_message' => $errorMessage,
            'tenant_id' => $invoice->tenant_id,
            'reseller_id' => $invoice->reseller_id,
        ];

        $this->startWorkflow('reseller_payment_failed', 'INVOICE', $invoice->id, $payload);
    }

    /**
     * Safe execution of WorkflowEngine triggers.
     */
    protected function startWorkflow(string $triggerKey, string $targetType, int $targetId, array $payload = []): void
    {
        if (!config('resellerhub.workflows.enabled', true)) {
            return;
        }

        if (!class_exists(WorkflowEngine::class)) {
            return;
        }

        $execute = function () use ($triggerKey, $targetType, $targetId, $payload) {
            try {
                if (Module::has('Workflows') && !Module::isEnabled('Workflows')) {
                    return;
                }

                app(WorkflowEngine::class)->start(
                    $triggerKey,
                    $targetType,
                    $targetId,
                    $payload
                );
            } catch (Throwable $e) {
                Log::warning("[Workflows] Error executing trigger '{$triggerKey}' for {$targetType}:{$targetId}: " . $e->getMessage());
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($execute);
        } else {
            $execute();
        }
    }
}
