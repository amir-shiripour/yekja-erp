<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Actions\Invoices;

use Carbon\Carbon;
use Exception;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\DTOs\UpdateInvoiceDTO;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Events\InvoiceStatusChanged;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\Billing\OrderProvisioningService;
use Throwable;

class UpdateInvoiceAction
{
    public function __construct(
        protected OrderProvisioningService $orderProvisioningService
    )
    {
    }

    /**
     * Update an existing invoice, regenerate items if provided, and handle status transitions safely.
     *
     * @param ResellerInvoice $invoice
     * @param UpdateInvoiceDTO|array $data
     * @return ResellerInvoice
     * @throws ResellerHubException|Exception|Throwable
     */
    public function execute(ResellerInvoice $invoice, UpdateInvoiceDTO|array $data): ResellerInvoice
    {
        $dto = $data instanceof UpdateInvoiceDTO ? $data : UpdateInvoiceDTO::fromArray($data);
        $oldStatus = $invoice->status;

        $invoice = DB::transaction(function () use ($invoice, $dto, $oldStatus) {
            $issueDate = $dto->issueDate ?? $invoice->issue_date;
            $dueDate = $dto->dueDate ?? $invoice->due_date;

            $newStatus = $dto->status ?? $invoice->status;

            // Validate status transition if state changed
            if ($newStatus !== $oldStatus) {
                $fromEnum = $oldStatus instanceof InvoiceStatus ? $oldStatus : InvoiceStatus::fromOrDefault((string)$oldStatus);
                $toEnum = $newStatus instanceof InvoiceStatus ? $newStatus : InvoiceStatus::fromOrDefault((string)$newStatus);

                if ($fromEnum && $toEnum && !$fromEnum->canTransitionTo($toEnum)) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.invoice_status_transition', [
                        'from' => $fromEnum->label(),
                        'to' => $toEnum->label(),
                    ]));
                }
            }

            $updatePayload = [
                'tenant_id' => $dto->tenantId ?? $invoice->tenant_id,
                'package_id' => $dto->packageId ?? $invoice->package_id,
                'type' => $dto->type ?? $invoice->type,
                'status' => $newStatus,
                'sub_account_name' => $dto->subAccountName ?? $invoice->sub_account_name,
                'sub_account_mobile' => $dto->subAccountMobile ?? $invoice->sub_account_mobile,
                'sub_account_email' => $dto->subAccountEmail ?? $invoice->sub_account_email,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'discount_type' => $dto->discountType ?? $invoice->discount_type,
                'discount_value' => $dto->discountValue ?? (int)$invoice->discount_value,
                'tax_percent' => $dto->taxPercent ?? (float)$invoice->tax_percent,
                'payment_method' => $dto->paymentMethod ?? $invoice->payment_method,
                'transaction_ref' => $dto->transactionRef ?? $invoice->transaction_ref,
                'notes' => $dto->notes ?? $invoice->notes,
                'terms' => $dto->terms ?? $invoice->terms,
            ];

            $invoice->update($updatePayload);

            // Replace items if provided
            if ($dto->items !== null && is_array($dto->items)) {
                $invoice->items()->delete();

                foreach ($dto->items as $item) {
                    $qty = max(1, (int)($item['quantity'] ?? 1));
                    $unitPrice = max(0, (int)($item['unit_price'] ?? 0));
                    $itemDiscount = max(0, (int)($item['discount_amount'] ?? 0));
                    $lineTotal = BillingCalculator::calculateLineTotal($qty, $unitPrice, $itemDiscount);

                    ResellerInvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => $item['item_type'] ?? 'module',
                        'item_key' => $item['item_key'] ?? $item['item_id'] ?? null,
                        'title' => trim($item['title'] ?? $item['description'] ?? __('resellerhub::messages.invoices.default_item_title')),
                        'description' => $item['description'] ?? $item['title'] ?? null,
                        'duration_months' => (int)($item['duration_months'] ?? 12),
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'discount_amount' => $itemDiscount,
                        'total_price' => $lineTotal,
                        'meta' => $item['meta'] ?? null,
                    ]);
                }
            }

            $invoice->load('items');
            $invoice->recalculateTotals()->save();

            // Status transition to paid
            $oldStatusVal = $oldStatus instanceof InvoiceStatus ? $oldStatus->value : (string)$oldStatus;
            $newStatusVal = $newStatus instanceof InvoiceStatus ? $newStatus->value : (string)$newStatus;

            if ($oldStatusVal !== ResellerInvoice::STATUS_PAID && $newStatusVal === ResellerInvoice::STATUS_PAID) {
                $invoice->paid_at = Carbon::now();
                $invoice->paid_amount = $invoice->total;
                $invoice->save();

                $this->orderProvisioningService->convertInvoiceToOrder($invoice);
            }

            return $invoice;
        }, 3);

        if ($oldStatus !== $invoice->status) {
            $fromStr = $oldStatus instanceof InvoiceStatus ? $oldStatus->value : (string)$oldStatus;
            $toStr = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : (string)$invoice->status;
            event(new InvoiceStatusChanged($invoice, $toStr, $fromStr));
        }

        return $invoice;
    }

    protected function parseJalaliToGregorian(mixed $dateStr): ?Carbon
    {
        return JalaliDateHelper::parseToGregorian($dateStr);
    }
}
