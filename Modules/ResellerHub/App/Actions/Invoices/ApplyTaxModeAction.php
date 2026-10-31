<?php

namespace Modules\ResellerHub\App\Actions\Invoices;

use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;

class ApplyTaxModeAction
{
    /**
     * Apply a tax mode to an invoice and re-derive every money field from it.
     * @param string $taxMode 'invoice' or 'item' (legacy 'subtotal' -> 'invoice').
     * @param float $taxRate Tax percentage, e.g. 10.0 for 10%.
     */
    public function execute(ResellerInvoice $invoice, string $taxMode, float $taxRate, ?string $source = null): ResellerInvoice
    {
        $invoice->refresh();
        $invoice->loadMissing('items');

        $items = $invoice->items;
        $mode = BillingCalculator::normalizeTaxMode($taxMode);

        $breakdown = BillingCalculator::computeTotals(
            subtotal: (int)$items->sum('total_price'),
            taxPercent: $taxRate,
            discountType: 'fixed',
            discountValue: (int)$invoice->discount_amount,
            taxMode: $mode,
            lineAmounts: $items->map(static fn($item) => (int)$item->total_price)->all()
        );

        $invoice->subtotal = $breakdown['subtotal'];
        $invoice->tax_percent = $taxRate;
        $invoice->tax_amount = $breakdown['tax_amount'];
        $invoice->discount_amount = $breakdown['discount_amount'];

        [$roundedTotal] = app(PriceRoundingService::class)->apply($breakdown['total'], $invoice->reseller_id);
        $invoice->total = $roundedTotal;

        $this->syncItemTaxMeta($invoice, $mode, $taxRate, $breakdown['line_taxes']);

        $metaUpdate = ['tax_mode' => $mode];
        if ($source !== null) {
            $metaUpdate['source'] = $source;
        } elseif (empty($invoice->meta['source'])) {
            $metaUpdate['source'] = 'tenant_creator_wizard';
        }

        $invoice->meta = array_merge($invoice->meta ?? [], $metaUpdate);
        $invoice->save();

        if ($invoice->order) {
            $invoice->order->update(['total_amount' => $invoice->total]);
        }

        return $invoice;
    }

    /**
     * Persist the per-line tax figures shown on the printed invoice.
     *
     * @param array<int, int> $lineTaxes
     */
    protected function syncItemTaxMeta(ResellerInvoice $invoice, string $mode, float $taxRate, array $lineTaxes): void
    {
        $isItemMode = $mode === BillingCalculator::TAX_MODE_ITEM;

        foreach (array_values($invoice->items->all()) as $index => $item) {
            $meta = is_array($item->meta) ? $item->meta : [];

            if (!$isItemMode) {
                if (!array_key_exists('tax_amount', $meta) && !array_key_exists('tax_rate', $meta)) {
                    continue;
                }
                unset($meta['tax_amount'], $meta['tax_rate']);
                $item->meta = $meta;
                $item->saveQuietly();
                continue;
            }

            $lineTax = (int)($lineTaxes[$index] ?? 0);
            if ((int)($meta['tax_amount'] ?? -1) === $lineTax && (float)($meta['tax_rate'] ?? -1) === $taxRate) {
                continue;
            }

            $item->meta = array_merge($meta, [
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
            ]);
            $item->saveQuietly();
        }
    }
}
