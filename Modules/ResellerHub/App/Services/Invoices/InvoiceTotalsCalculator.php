<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Invoices;

use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;


final class InvoiceTotalsCalculator
{
    public function __construct(
        private readonly PriceRoundingService $rounding,
    )
    {
    }

    public function recalculate(ResellerInvoice $invoice): ResellerInvoice
    {
        $items = $invoice->items;
        $regularItems = $items->filter(static fn($it) => $it->item_type !== 'rounding');

        $subtotal = (int)$regularItems->sum('total_price');
        $invoice->subtotal = $subtotal;

        $breakdown = BillingCalculator::computeTotals(
            subtotal: $subtotal,
            taxPercent: (float)($invoice->tax_percent ?? 0),
            discountType: (string)($invoice->discount_type ?? 'fixed'),
            discountValue: (float)($invoice->discount_value ?? 0),
            taxMode: (string)($invoice->meta['tax_mode'] ?? BillingCalculator::TAX_MODE_INVOICE),
            lineAmounts: $regularItems->map(static fn($item): int => (int)$item->total_price)->all(),
        );

        $invoice->tax_amount = $breakdown['tax_amount'];
        $invoice->discount_amount = $breakdown['discount_amount'];

        if ($breakdown['tax_mode'] === BillingCalculator::TAX_MODE_ITEM) {
            $this->syncLineTaxes($invoice, $regularItems, $breakdown['line_taxes']);
        }

        [$roundedTotal, $roundingMeta] = $this->rounding->apply($breakdown['total'], (int)$invoice->reseller_id);
        $invoice->total = $roundedTotal;

        $roundingDiff = (int)$roundedTotal - (int)$breakdown['total'];

        $meta = is_array($invoice->meta) ? $invoice->meta : [];

        if (!empty($roundingMeta['is_rounded'])) {
            $meta['rounding'] = array_merge($roundingMeta, ['rounding_diff' => $roundingDiff]);
        } else {
            unset($meta['rounding']);
        }

        $invoice->meta = $meta;

        // FIN-03: Official rounding line item synchronization
        if ($invoice->exists) {
            $roundingItem = $items->firstWhere('item_type', 'rounding');
            if ($roundingDiff !== 0) {
                if ($roundingItem) {
                    $roundingItem->update([
                        'title' => 'تعدیل گرد کردن مبلغ فاکتور (رُندینگ)',
                        'unit_price' => $roundingDiff,
                        'total_price' => $roundingDiff,
                        'quantity' => 1,
                    ]);
                } else {
                    $invoice->items()->create([
                        'item_type' => 'rounding',
                        'item_key' => 'rounding_adjustment',
                        'title' => 'تعدیل گرد کردن مبلغ فاکتور (رُندینگ)',
                        'quantity' => 1,
                        'unit_price' => $roundingDiff,
                        'discount_amount' => 0,
                        'total_price' => $roundingDiff,
                        'duration_months' => 0,
                    ]);
                }
            } elseif ($roundingItem) {
                $roundingItem->delete();
            }
        }

        return $invoice;
    }

    /**
     * @param iterable<int, \Modules\ResellerHub\App\Models\ResellerInvoiceItem> $items
     * @param array<int, int> $lineTaxes
     */
    private function syncLineTaxes(ResellerInvoice $invoice, iterable $items, array $lineTaxes): void
    {
        foreach (array_values(is_array($items) ? $items : iterator_to_array($items)) as $index => $item) {
            $lineTax = (int)($lineTaxes[$index] ?? 0);
            $meta = is_array($item->meta) ? $item->meta : [];

            if ((int)($meta['tax_amount'] ?? -1) === $lineTax
                && (float)($meta['tax_rate'] ?? -1) === (float)$invoice->tax_percent) {
                continue;
            }

            $item->meta = array_merge($meta, [
                'tax_rate' => (float)($invoice->tax_percent ?? 0),
                'tax_amount' => $lineTax,
            ]);

            $item->saveQuietly();
        }
    }
}
