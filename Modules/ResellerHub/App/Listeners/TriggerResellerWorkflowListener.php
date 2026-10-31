<?php

namespace Modules\ResellerHub\App\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Events\InvoiceCancelled;
use Modules\ResellerHub\App\Events\InvoiceCreated;
use Modules\ResellerHub\App\Events\InvoicePaid;
use Modules\ResellerHub\App\Events\InvoiceStatusChanged;
use Modules\ResellerHub\App\Events\OrderCreated;
use Modules\ResellerHub\App\Events\OrderProvisioned;
use Modules\ResellerHub\App\Events\OrderRenewed;
use Modules\ResellerHub\App\Events\OrderStatusChanged;
use Modules\ResellerHub\App\Events\PaymentCancelled;
use Modules\ResellerHub\App\Events\PaymentRecorded;
use Modules\ResellerHub\App\Events\QuotaThresholdExceeded;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Throwable;

class TriggerResellerWorkflowListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        protected ResellerWorkflowService $workflowService,
        protected ResellerAuditService    $auditService
    )
    {
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[TriggerResellerWorkflowListener] Queued listener failed: ' . $exception->getMessage());
    }

    public function handleInvoiceCreated(InvoiceCreated $event): void
    {
        $this->safe('invoice.created', fn() => $this->workflowService->onInvoiceCreated($event->invoice));
        $this->safe('invoice.created.audit', fn() => $this->auditService->logInvoiceCreated($event->invoice));
    }

    public function handleInvoicePaid(InvoicePaid $event): void
    {
        $this->safe('invoice.paid', fn() => $this->workflowService->onInvoicePaid($event->invoice, $event->payment));
        $this->safe('invoice.paid.audit', fn() => $this->auditService->logInvoicePaid($event->invoice, $event->payment));
    }

    public function handleInvoiceCancelled(InvoiceCancelled $event): void
    {
        $this->safe('invoice.cancelled', fn() => $this->workflowService->onInvoiceCancelled($event->invoice));
        $this->safe('invoice.cancelled.audit', fn() => $this->auditService->logInvoiceCancelled($event->invoice));
    }

    public function handleInvoiceStatusChanged(InvoiceStatusChanged $event): void
    {
        $this->safe('invoice.status_changed', fn() => $this->workflowService->onInvoiceStatusChanged($event->invoice, $event->newStatus, $event->oldStatus));
    }

    public function handlePaymentRecorded(PaymentRecorded $event): void
    {
        $this->safe('payment.recorded', fn() => $this->workflowService->onPaymentRecorded($event->payment));
        $this->safe('payment.recorded.audit', fn() => $this->auditService->logPaymentRecorded($event->payment));
    }

    public function handlePaymentCancelled(PaymentCancelled $event): void
    {
        $this->safe('payment.cancelled', fn() => $this->workflowService->onPaymentCancelled($event->payment));
        $this->safe('payment.cancelled.audit', fn() => $this->auditService->logPaymentCancelled($event->payment));
    }

    public function handleOrderCreated(OrderCreated $event): void
    {
        $this->safe('order.created', fn() => $this->workflowService->onOrderCreated($event->order));
        $this->safe('order.created.audit', fn() => $this->auditService->logOrderCreated($event->order));
    }

    public function handleOrderProvisioned(OrderProvisioned $event): void
    {
        $this->safe('order.provisioned', fn() => $this->workflowService->onOrderProvisioned($event->order));
        $this->safe('order.provisioned.audit', fn() => $this->auditService->logOrderProvisioned($event->order));
    }

    public function handleOrderRenewed(OrderRenewed $event): void
    {
        $this->safe('order.renewed', fn() => $this->workflowService->onOrderRenewed($event->order));
        $this->safe('order.renewed.audit', fn() => $this->auditService->logOrderRenewed($event->order));
    }

    public function handleOrderStatusChanged(OrderStatusChanged $event): void
    {
        $this->safe('order.status_changed', fn() => $this->workflowService->onOrderStatusChanged($event->order, $event->newStatus, $event->oldStatus));
    }

    public function handleQuotaThresholdExceeded(QuotaThresholdExceeded $event): void
    {
        $keyName = is_object($event->quotaKey) && isset($event->quotaKey->value)
            ? $event->quotaKey->value
            : (string)$event->quotaKey;

        $this->safe('quota.threshold.audit', fn() => $this->auditService->logQuotaExceeded($event->tenant, $keyName, $event->limit, $event->used));
        $this->safe('quota.threshold', fn() => $this->workflowService->onQuotaThresholdExceeded($event->tenant, $event->quotaKey, $event->limit, $event->used));
    }

    protected function safe(string $action, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::error('[TriggerResellerWorkflowListener] Side-effect failed and was isolated', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            InvoiceCreated::class => 'handleInvoiceCreated',
            InvoicePaid::class => 'handleInvoicePaid',
            InvoiceCancelled::class => 'handleInvoiceCancelled',
            InvoiceStatusChanged::class => 'handleInvoiceStatusChanged',
            PaymentRecorded::class => 'handlePaymentRecorded',
            PaymentCancelled::class => 'handlePaymentCancelled',
            OrderCreated::class => 'handleOrderCreated',
            OrderProvisioned::class => 'handleOrderProvisioned',
            OrderRenewed::class => 'handleOrderRenewed',
            OrderStatusChanged::class => 'handleOrderStatusChanged',
            QuotaThresholdExceeded::class => 'handleQuotaThresholdExceeded',
        ];
    }
}
