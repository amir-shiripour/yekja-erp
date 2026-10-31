<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Throwable;

class ResellerAuditService
{
    /**
     * Record invoice creation audit log.
     */
    public function logInvoiceCreated(ResellerInvoice $invoice): void
    {
        $this->record(
            type: 'reseller_invoice_created',
            description: "صدور فاکتور #{$invoice->invoice_number} به مبلغ " . number_format($invoice->total) . ' ' . $invoice->currency,
            subject: $invoice,
            properties: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'type' => $invoice->type,
                'status' => $invoice->status,
                'reseller_id' => $invoice->reseller_id,
                'tenant_id' => $invoice->tenant_id,
                'sub_account_id' => $invoice->sub_account_id,
                'total' => (int)$invoice->total,
            ]
        );
    }

    /**
     * Record invoice payment audit log.
     */
    public function logInvoicePaid(ResellerInvoice $invoice, ?ResellerInvoicePayment $payment = null): void
    {
        $this->record(
            type: 'reseller_invoice_paid',
            description: "تسویه کامل فاکتور #{$invoice->invoice_number} به مبلغ " . number_format($invoice->paid_amount) . ' ' . $invoice->currency,
            subject: $invoice,
            properties: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reseller_id' => $invoice->reseller_id,
                'tenant_id' => $invoice->tenant_id,
                'sub_account_id' => $invoice->sub_account_id,
                'paid_amount' => (int)$invoice->paid_amount,
                'payment_id' => $payment?->id,
                'payment_method' => $payment?->method ?? $invoice->payment_method,
                'transaction_ref' => $payment?->transaction_id ?? $invoice->transaction_ref,
            ]
        );
    }

    /**
     * Record invoice cancellation audit log.
     */
    public function logInvoiceCancelled(ResellerInvoice $invoice, string $reason = ''): void
    {
        $this->record(
            type: 'reseller_invoice_cancelled',
            description: "ابطال فاکتور #{$invoice->invoice_number}" . ($reason ? " (علت: {$reason})" : ''),
            subject: $invoice,
            properties: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reseller_id' => $invoice->reseller_id,
                'tenant_id' => $invoice->tenant_id,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Record payment transaction audit log.
     */
    public function logPaymentRecorded(ResellerInvoicePayment $payment): void
    {
        $this->record(
            type: 'reseller_payment_recorded',
            description: "ثبت پرداخت #{$payment->id} به مبلغ " . number_format($payment->amount) . " به روش {$payment->method}",
            subject: $payment,
            properties: [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => (int)$payment->amount,
                'method' => $payment->method,
                'gateway' => $payment->gateway,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
            ]
        );
    }

    /**
     * Record payment cancellation audit log.
     */
    public function logPaymentCancelled(ResellerInvoicePayment $payment, string $reason = ''): void
    {
        $this->record(
            type: 'reseller_payment_cancelled',
            description: "ابطال پرداخت #{$payment->id} به مبلغ " . number_format($payment->amount) . ($reason ? " (علت: {$reason})" : ''),
            subject: $payment,
            properties: [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => (int)$payment->amount,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Record order creation audit log.
     */
    public function logOrderCreated(ResellerOrder $order): void
    {
        $this->record(
            type: 'reseller_order_created',
            description: "ایجاد سفارش #{$order->order_number} برای زیرحساب تننت {$order->tenant_id}",
            subject: $order,
            properties: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reseller_id' => $order->reseller_id,
                'tenant_id' => $order->tenant_id,
                'sub_account_id' => $order->sub_account_id,
                'status' => $order->status,
                'total_amount' => (int)$order->total_amount,
            ]
        );
    }

    /**
     * Record order provisioned audit log.
     */
    public function logOrderProvisioned(ResellerOrder $order): void
    {
        $this->record(
            type: 'reseller_order_provisioned',
            description: "تخصیص و فعال‌سازی سفارش #{$order->order_number} روی تننت {$order->tenant_id}",
            subject: $order,
            properties: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reseller_id' => $order->reseller_id,
                'tenant_id' => $order->tenant_id,
                'sub_account_id' => $order->sub_account_id,
                'end_date' => $order->end_date?->format('Y-m-d'),
            ]
        );
    }

    /**
     * Record order renewal audit log.
     */
    public function logOrderRenewed(ResellerOrder $order): void
    {
        $this->record(
            type: 'reseller_order_renewed',
            description: "تمدید دوره سفارش #{$order->order_number} تا تاریخ " . ($order->end_date?->format('Y/m/d') ?? 'نامشخص'),
            subject: $order,
            properties: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reseller_id' => $order->reseller_id,
                'tenant_id' => $order->tenant_id,
                'end_date' => $order->end_date?->format('Y-m-d'),
                'renewal_date' => $order->renewal_date?->format('Y-m-d'),
            ]
        );
    }

    /**
     * Record security / authentication events.
     */
    public function logSecurityEvent(string $type, string $description, array $context = []): void
    {
        $this->record(
            type: 'reseller_security_' . $type,
            description: $description,
            properties: $context
        );
    }

    /**
     * Record tenant data export event with format, user context and filename.
     */
    public function logDataExport(Tenant $tenant, string $format, string $filename, array $context = []): void
    {
        $this->record(
            type: 'tenant_data_export',
            description: "خروجی اطلاعات تنانت '{$tenant->subdomain}' با فرمت " . strtoupper($format) . " (فایل: {$filename})",
            subject: $tenant,
            properties: array_merge([
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
                'format' => $format,
                'filename' => $filename,
                'exported_by' => auth()->id(),
                'ip' => request()->ip(),
            ], $context)
        );
    }

    /**
     * Record quota limit warning/exceeded event.
     */
    public function logQuotaExceeded(Tenant $tenant, string $key, ?int $limit, int $used): void
    {
        $this->record(
            type: 'reseller_quota_exceeded',
            description: "هشدار مصرف سهمیه '{$key}' در تننت {$tenant->id} (مصرف: {$used} از سقف: " . ($limit ?? 'نامحدود') . ")",
            subject: $tenant,
            properties: [
                'tenant_id' => $tenant->id,
                'quota_key' => $key,
                'limit' => $limit,
                'used' => $used,
            ]
        );
    }

    /**
     * Record tenant impersonation / SSO login audit log.
     */
    public function logImpersonationStart(Tenant $tenant, int $actorId, ?string $ip = null, ?string $userAgent = null): void
    {
        $this->record(
            type: 'tenant_impersonation_started',
            description: "ورود به عنوان کاربر به سامانه مستأجر '{$tenant->subdomain}' (شناسه تنانت: {$tenant->id}) توسط کاربر #{$actorId}",
            subject: $tenant,
            properties: [
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
                'actor_id' => $actorId,
                'ip' => $ip ?? Request::ip(),
                'user_agent' => $userAgent ?? Request::userAgent(),
                'started_at' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Record tenant impersonation exit audit log.
     */
    public function logImpersonationEnd(Tenant $tenant, int $actorId): void
    {
        $this->record(
            type: 'tenant_impersonation_ended',
            description: "خروج از نشست مستأجر '{$tenant->subdomain}' توسط کاربر #{$actorId}",
            subject: $tenant,
            properties: [
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
                'actor_id' => $actorId,
                'ended_at' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Record non-wallet payment reversal / credit document audit.
     */
    public function logPaymentReversedManual(ResellerInvoice $invoice, ResellerInvoicePayment $payment): void
    {
        $this->record(
            type: 'reseller_payment_reversed_manual',
            description: "ابطال پرداخت نقدی/غیرکیف‌پول #{$payment->id} به مبلغ " . number_format((int)$payment->amount) . " روش {$payment->method} برای فاکتور #{$invoice->invoice_number} (سند بستانکاری/ابطال)",
            subject: $invoice,
            properties: [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => (int)$payment->amount,
                'method' => $payment->method,
            ]
        );
    }

    /**
     * Safe record dispatcher.
     */
    public function record(string $type, string $description, ?object $subject = null, array $properties = []): void
    {
        try {
            $modelSubject = ($subject instanceof Model) ? $subject : null;

            ActivityLogger::log(
                type: $type,
                description: $description,
                subject: $modelSubject,
                properties: array_merge($properties, [
                    'actor_id' => Auth::id(),
                    'ip' => Request::ip(),
                    'timestamp' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            // Non-blocking fallback to system logger
            Log::warning("[ResellerAuditService] ActivityLogger failed: " . $e->getMessage(), [
                'type' => $type,
                'description' => $description,
            ]);
        }
    }
}
