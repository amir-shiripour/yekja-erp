<?php

namespace Modules\ResellerHub\App\Services;


class BillingCalculator
{
    /** Tax is computed once, on the whole invoice net amount. */
    public const TAX_MODE_INVOICE = 'invoice';

    /** Tax is computed per line item on that line's net amount, then summed. */
    public const TAX_MODE_ITEM = 'item';

    /**
     * Calculate line item total price after discount.
     */
    public static function calculateLineTotal(int $quantity, int $unitPrice, int $discountAmount = 0): int
    {
        $qty = max(1, $quantity);
        $price = max(0, $unitPrice);
        $discount = max(0, $discountAmount);
        return max(0, ($qty * $price) - $discount);
    }


    public static function calculateDiscount(string $discountType, float|int $discountValue, int $baseAmount): int
    {
        if ($baseAmount <= 0 || $discountValue <= 0) {
            return 0;
        }

        if ($discountType === 'percent') {
            $clampedPercent = min(100, max(0, (float)$discountValue));
            return (int)round(($baseAmount * $clampedPercent) / 100);
        }

        return min((int)$discountValue, $baseAmount);
    }

    /**
     * Calculate tax amount based on taxable amount and tax rate percentage.
     */
    public static function calculateTax(int $taxableAmount, float $taxPercent): int
    {
        if ($taxableAmount <= 0 || $taxPercent <= 0) {
            return 0;
        }

        return (int)round(($taxableAmount * $taxPercent) / 100);
    }


    public static function normalizeTaxMode(string $taxMode): string
    {
        return strtolower(trim($taxMode)) === self::TAX_MODE_ITEM
            ? self::TAX_MODE_ITEM
            : self::TAX_MODE_INVOICE;
    }

    /**
     * @param array<int, int|float> $lineAmounts Per-line net totals.
     * @return array<int, int>  Discount shares, index-aligned with $lineAmounts.
     */
    public static function allocateDiscount(int $discountAmount, array $lineAmounts): array
    {
        $amounts = array_map(static fn($amount) => max(0, (int)$amount), array_values($lineAmounts));
        $count = count($amounts);

        if ($count === 0) {
            return [];
        }

        $total = array_sum($amounts);
        $discount = max(0, min($discountAmount, $total));

        if ($discount === 0 || $total === 0) {
            return array_fill(0, $count, 0);
        }

        $shares = [];
        $remainders = [];
        $assigned = 0;

        foreach ($amounts as $index => $amount) {
            $exact = ($amount * $discount) / $total;
            $floor = (int)floor($exact);
            $shares[$index] = $floor;
            $remainders[$index] = $exact - $floor;
            $assigned += $floor;
        }

        // Hand the leftover Toman to the lines with the largest fractional part.
        $leftover = $discount - $assigned;
        if ($leftover > 0) {
            arsort($remainders, SORT_NUMERIC);
            foreach (array_keys($remainders) as $index) {
                if ($leftover <= 0) {
                    break;
                }
                $shares[$index]++;
                $leftover--;
            }
        }

        return array_values($shares);
    }

    /**
     * @param int $subtotal Sum of item line totals (already net of line discounts).
     * @param float $taxPercent Tax rate, e.g. 10.0 for 10%.
     * @param string $discountType 'percent' or 'fixed'.
     * @param float|int $discountValue Percent number or fixed currency value.
     * @param string $taxMode 'invoice' or 'item' (legacy 'subtotal' -> 'invoice').
     * @param array<int, int|float> $lineAmounts Per-line net totals. Needed for a
     * @return array{subtotal:int, discount_amount:int, taxable_amount:int, tax_amount:int, total:int, tax_mode:string, line_taxes:array<int,int>}
     */
    public static function computeTotals(
        int       $subtotal,
        float     $taxPercent = 0.0,
        string    $discountType = 'fixed',
        float|int $discountValue = 0,
        string    $taxMode = self::TAX_MODE_INVOICE,
        array     $lineAmounts = []
    ): array
    {
        $subtotal = max(0, $subtotal);
        $mode = self::normalizeTaxMode($taxMode);

        $discountAmount = self::calculateDiscount($discountType, $discountValue, $subtotal);
        $taxableAmount = max(0, $subtotal - $discountAmount);

        $lineTaxes = [];
        $taxAmount = 0;

        if ($mode === self::TAX_MODE_ITEM && $lineAmounts !== []) {
            $shares = self::allocateDiscount($discountAmount, $lineAmounts);

            foreach (array_values($lineAmounts) as $index => $lineAmount) {
                $lineTaxable = max(0, (int)$lineAmount - (int)($shares[$index] ?? 0));
                $lineTax = self::calculateTax($lineTaxable, $taxPercent);
                $lineTaxes[$index] = $lineTax;
                $taxAmount += $lineTax;
            }
        } else {
            $taxAmount = self::calculateTax($taxableAmount, $taxPercent);
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'tax_amount' => $taxAmount,
            'total' => max(0, $taxableAmount + $taxAmount),
            'tax_mode' => $mode,
            'line_taxes' => $lineTaxes,
        ];
    }
}
