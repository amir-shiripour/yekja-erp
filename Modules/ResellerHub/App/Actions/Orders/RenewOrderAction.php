<?php

namespace Modules\ResellerHub\App\Actions\Orders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\DTOs\RenewOrderDTO;
use Modules\ResellerHub\App\Events\InvoiceCreated;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerModulePricing;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Throwable;

class RenewOrderAction
{
    /**
     * Create a renewal invoice from an existing invoice or order with typed DTO / array compatibility.
     *
     * @param ResellerInvoice|ResellerOrder $source
     * @param RenewOrderDTO|array $options
     * @return ResellerInvoice
     * @throws Throwable
     */
    public function execute(ResellerInvoice|ResellerOrder $source, RenewOrderDTO|array $options = []): ResellerInvoice
    {
        $dto = $options instanceof RenewOrderDTO ? $options : RenewOrderDTO::fromArray($options);

        if ($source instanceof ResellerOrder) {
            return $this->createFromOrder($source, $dto);
        }

        return $this->createFromInvoice($source, $dto);
    }

    /**
     * Generate renewal invoice from invoice instance.
     *
     * @param ResellerInvoice $sourceInvoice
     * @param RenewOrderDTO|array $options
     * @return ResellerInvoice
     * @throws Throwable
     */
    public function createFromInvoice(ResellerInvoice $sourceInvoice, RenewOrderDTO|array $options = []): ResellerInvoice
    {
        $dto = $options instanceof RenewOrderDTO ? $options : RenewOrderDTO::fromArray($options);

        /** @var ResellerInvoice $renewalInvoice */
        $renewalInvoice = DB::transaction(static function () use ($sourceInvoice, $dto): ResellerInvoice {
            $order = $sourceInvoice->order;

            if ($order) {
                $period = $order->renewalPeriod();
                $cycle = $period['cycle'];
                $months = $period['months'];
                $days = $period['days'];
                $isCustomPeriod = $period['is_custom'];
                $periodLabel = $period['label'];
            } else {
                $cycle = 'yearly';
                $months = 12;
                $days = null;
                $isCustomPeriod = false;
                $periodLabel = ResellerHelper::durationLabel(12, true);
            }

            if ($sourceInvoice->tenant) {
                app(ResellerBillingService::class)->assertCanRenewAdvance(
                    $sourceInvoice->tenant,
                    $months,
                    $sourceInvoice->reseller_id,
                    $sourceInvoice->id
                );
            }

            $startDate = Carbon::now();
            if ($sourceInvoice->due_date && Carbon::parse($sourceInvoice->due_date)->isFuture()) {
                $startDate = Carbon::parse($sourceInvoice->due_date);
            } elseif ($order && $order->end_date && Carbon::parse($order->end_date)->isFuture()) {
                $startDate = Carbon::parse($order->end_date);
            }

            $dueDate = $days
                ? (clone $startDate)->addDays((int)$days)
                : (clone $startDate)->addMonths($months);

            $invNumFa = ResellerHelper::toPersianDigits((string)$sourceInvoice->invoice_number);
            $tenantName = $sourceInvoice->tenant?->name ?? 'سامانه';
            $renewalNote = $dto->notes ?? __('resellerhub::messages.invoices.renewal_from_invoice_note', [
                'tenant' => $tenantName,
                'invoice' => $invNumFa,
            ]);

            /** @var ResellerInvoice $createdRenewal */
            $createdRenewal = ResellerInvoice::create([
                'reseller_id' => $sourceInvoice->reseller_id,
                'tenant_id' => $sourceInvoice->tenant_id,
                'parent_invoice_id' => $sourceInvoice->id,
                'source_order_id' => $order?->id,
                'source' => 'renewal',
                'package_id' => $sourceInvoice->package_id,
                'invoice_number' => ResellerInvoice::generateNextNumber($sourceInvoice->reseller_id),
                'type' => $dto->type,
                'status' => ResellerInvoice::STATUS_UNPAID,
                'sub_account_name' => $sourceInvoice->sub_account_name,
                'sub_account_mobile' => $sourceInvoice->sub_account_mobile,
                'sub_account_email' => $sourceInvoice->sub_account_email,
                'issue_date' => Carbon::now(),
                'due_date' => $dueDate,
                'discount_type' => $sourceInvoice->discount_type,
                'discount_value' => 0,
                'tax_percent' => $sourceInvoice->tax_percent,
                'notes' => $renewalNote,
                'terms' => $sourceInvoice->terms,
                'meta' => [
                    'is_renewal' => true,
                    'tax_mode' => (string)($sourceInvoice->meta['tax_mode'] ?? BillingCalculator::TAX_MODE_INVOICE),
                    'created_by_manual_renewal' => $dto->manual || !$dto->createdByWorkflow,
                    'created_by_workflow' => $dto->createdByWorkflow,
                    'source_invoice_id' => $sourceInvoice->id,
                    'source_order_id' => $order?->id,
                    'billing_cycle' => $cycle,
                    'duration_months' => $months,
                    'duration_days' => $days,
                    'is_custom_period' => $isCustomPeriod,
                    'period_label' => $periodLabel,
                ],
                'created_by' => auth()->id() ?? $sourceInvoice->created_by,
            ]);

            // Copy items (skipping one-time setup fee items)
            foreach ($sourceInvoice->items as $item) {
                if ($item->item_type === 'setup_fee') {
                    continue;
                }

                $qty = max(1, (int)$item->quantity);
                $unitPrice = (int)$item->unit_price;

                if (!$isCustomPeriod) {
                    if ($item->item_type === 'module' && !empty($item->item_key)) {
                        /** @var ResellerModulePricing|null $pricing */
                        $pricing = ResellerModulePricing::where('reseller_id', $sourceInvoice->reseller_id)
                            ->where('module_slug', $item->item_key)
                            ->whereNull('feature_key')
                            ->first();
                        if ($pricing) {
                            $p = match ($cycle) {
                                'monthly' => $pricing->monthly_price,
                                'quarterly' => $pricing->quarterly_price ?: ($pricing->monthly_price * 3),
                                'semi_annual' => $pricing->semi_annual_price ?: ($pricing->monthly_price * 6),
                                default => $pricing->yearly_price ?: ($pricing->monthly_price * 12),
                            };
                            if ($p > 0) {
                                $unitPrice = (int)$p;
                            }
                        }
                    } elseif ($item->item_type === 'feature' && !empty($item->item_key)) {
                        /** @var ResellerModulePricing|null $pricing */
                        $pricing = ResellerModulePricing::where('reseller_id', $sourceInvoice->reseller_id)
                            ->where('feature_key', $item->item_key)
                            ->first();
                        if ($pricing) {
                            $p = match ($cycle) {
                                'monthly' => $pricing->monthly_price,
                                'quarterly' => $pricing->quarterly_price ?: ($pricing->monthly_price * 3),
                                'semi_annual' => $pricing->semi_annual_price ?: ($pricing->monthly_price * 6),
                                default => $pricing->yearly_price ?: ($pricing->monthly_price * 12),
                            };
                            if ($p > 0) {
                                $unitPrice = (int)$p;
                            }
                        }
                    }
                }

                $itemTitle = __('resellerhub::messages.invoices.renewal_item_title', ['title' => $item->title]);
                $itemDesc = __('resellerhub::messages.invoices.renewal_item_desc', [
                    'title' => $item->title,
                    'period' => $periodLabel,
                ]);

                ResellerInvoiceItem::create([
                    'invoice_id' => $createdRenewal->id,
                    'item_type' => $item->item_type,
                    'item_key' => $item->item_key,
                    'title' => $itemTitle,
                    'description' => $itemDesc,
                    'duration_months' => $months,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'total_price' => $qty * $unitPrice,
                    'meta' => [
                        'is_renewal_item' => true,
                        'source_item_id' => $item->id,
                    ],
                ]);
            }

            // Recalculate totals
            $createdRenewal->load('items');
            $createdRenewal->recalculateTotals()->save();

            // Record expected renewal price on order without advancing renewal_date before payment
            if ($order) {
                $order->update([
                    'renewal_price' => $createdRenewal->total,
                ]);
            }

            return $createdRenewal;
        }, 3);

        event(new InvoiceCreated($renewalInvoice));

        return $renewalInvoice;
    }

    /**
     * Generate renewal invoice directly from order instance.
     *
     * @param ResellerOrder $order
     * @param RenewOrderDTO|array $options
     * @return ResellerInvoice
     * @throws Throwable
     */
    public function createFromOrder(ResellerOrder $order, RenewOrderDTO|array $options = []): ResellerInvoice
    {
        $dto = $options instanceof RenewOrderDTO ? $options : RenewOrderDTO::fromArray($options);

        $resellerId = $order->reseller_id;
        $order->loadMissing(['tenant', 'invoice.items', 'invoice.package']);

        $period = $order->renewalPeriod();
        $cycle = $period['cycle'];
        $months = $period['months'];
        $days = $period['days'];
        $periodLabel = $period['label'];

        if ($order->tenant) {
            app(ResellerBillingService::class)->assertCanRenewAdvance(
                $order->tenant,
                $months,
                $resellerId
            );
        }

        $startDate = ($order->end_date && Carbon::parse($order->end_date)->isFuture())
            ? Carbon::parse($order->end_date)
            : Carbon::now();

        $nextRenewalDate = $days
            ? (clone $startDate)->addDays((int)$days)
            : (clone $startDate)->addMonths($months);

        $dueDate = clone $startDate;

        $taxPercent = (float)($order->invoice?->tax_percent ?? 0);
        $taxMode = (string)($order->invoice?->meta['tax_mode'] ?? BillingCalculator::TAX_MODE_INVOICE);
        $invoiceNumber = ResellerInvoice::generateNextNumber($resellerId);

        $itemSubtotal = 0;
        if ($order->renewal_price_type === 'custom' && $order->renewal_price !== null) {
            $itemSubtotal = (int)$order->renewal_price;
        } elseif ($order->invoice && $order->invoice->items->isNotEmpty()) {
            $itemSubtotal = (int)$order->invoice->items->filter(fn($i) => $i->item_type !== 'setup_fee')->sum('total_price');
        } else {
            $itemSubtotal = (int)($order->total_amount ?: 0);
        }

        $itemTax = BillingCalculator::calculateTax($itemSubtotal, $taxPercent);
        $itemTotal = $itemSubtotal + $itemTax;

        /** @var ResellerInvoice $renewalInvoice */
        $renewalInvoice = DB::transaction(static function () use (
            $resellerId, $order, $invoiceNumber, $dueDate, $nextRenewalDate,
            $taxPercent, $taxMode, $itemSubtotal, $itemTax, $itemTotal,
            $cycle, $periodLabel, $months, $days, $dto
        ): ResellerInvoice {
            $orderRenewalNote = $dto->notes ?? __('resellerhub::messages.invoices.renewal_from_order_note', [
                'order' => $order->order_number,
                'period' => $periodLabel,
            ]);

            /** @var ResellerInvoice $inv */
            $inv = ResellerInvoice::create([
                'reseller_id' => $resellerId,
                'tenant_id' => $order->tenant_id,
                'package_id' => $order->invoice?->package_id,
                'parent_invoice_id' => $order->invoice_id,
                'source_order_id' => $order->id,
                'source' => 'renewal',
                'invoice_number' => $invoiceNumber,
                'type' => $dto->type,
                'status' => ResellerInvoice::STATUS_UNPAID,
                'sub_account_name' => $order->sub_account_name,
                'sub_account_mobile' => $order->sub_account_mobile,
                'sub_account_email' => $order->sub_account_email,
                'issue_date' => Carbon::now(),
                'due_date' => $dueDate,
                'subtotal' => $itemSubtotal,
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percent' => $taxPercent,
                'tax_amount' => $itemTax,
                'total' => $itemTotal,
                'paid_amount' => 0,
                'notes' => $orderRenewalNote,
                'terms' => $order->invoice?->terms,
                'meta' => [
                    'is_renewal' => true,
                    'tax_mode' => $taxMode,
                    'created_by_manual_renewal' => $dto->manual || !$dto->createdByWorkflow,
                    'created_by_workflow' => $dto->createdByWorkflow,
                    'source_order_id' => $order->id,
                    'source_invoice_id' => $order->invoice_id,
                    'billing_cycle' => $cycle,
                    'duration_months' => $months,
                    'duration_days' => $days,
                    'next_renewal_date' => $nextRenewalDate->toIso8601String(),
                    'period_label' => $periodLabel,
                ],
                'created_by' => auth()->id() ?? $order->created_by,
            ]);

            $orderItemTitle = __('resellerhub::messages.invoices.renewal_order_package_title', [
                'order' => $order->order_number,
            ]);
            $orderItemDesc = __('resellerhub::messages.invoices.renewal_order_package_desc', [
                'period' => $periodLabel,
            ]);

            ResellerInvoiceItem::create([
                'invoice_id' => $inv->id,
                'item_type' => 'package',
                'item_key' => 'order_renewal',
                'title' => $orderItemTitle,
                'description' => $orderItemDesc,
                'duration_months' => $months,
                'quantity' => 1,
                'unit_price' => $itemSubtotal,
                'discount_amount' => 0,
                'total_price' => $itemSubtotal,
                'meta' => [
                    'order_id' => $order->id,
                    'billing_cycle' => $cycle,
                ],
            ]);

            $order->update([
                'renewal_price' => $itemTotal,
            ]);

            return $inv;
        }, 3);

        event(new InvoiceCreated($renewalInvoice));

        return $renewalInvoice;
    }
}
