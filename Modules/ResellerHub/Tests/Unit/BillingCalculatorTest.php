<?php

namespace Modules\ResellerHub\Tests\Unit;

use Modules\ResellerHub\App\Services\BillingCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Contract tests for invoice arithmetic.
 *
 * These are the tests that make the tax fix *verifiable* rather than merely
 * plausible. The invariant under test is:
 *
 *     total === (subtotal - discount_amount) + tax_amount
 *
 * and the ordering rule is that tax is charged on the NET base (after
 * discount), never on the pre-discount subtotal.
 *
 * Pure unit tests: no database, no container, no framework boot.
 */
class BillingCalculatorTest extends TestCase
{
    /**
     * The exact case from the audit: 1,000,000 with 10% tax and 10% discount.
     *
     * The old implementation charged 100,000 of tax on the pre-discount amount
     * and produced 1,000,000 - 100,000 + 100,000 = 1,000,000... which happens
     * to look plausible. The tell is the tax figure itself: the taxable base is
     * 900,000, so the tax must be 90,000.
     */
    public function test_discount_is_applied_before_tax(): void
    {
        $result = BillingCalculator::computeTotals(
            subtotal: 1_000_000,
            taxPercent: 10.0,
            discountType: 'percent',
            discountValue: 10,
        );

        $this->assertSame(1_000_000, $result['subtotal']);
        $this->assertSame(100_000, $result['discount_amount']);
        $this->assertSame(900_000, $result['taxable_amount']);
        $this->assertSame(90_000, $result['tax_amount'], 'Tax must be 10% of the net 900,000, not of 1,000,000.');
        $this->assertSame(990_000, $result['total']);
    }

    public function test_tax_is_never_charged_on_the_discounted_away_amount(): void
    {
        $result = BillingCalculator::computeTotals(1_000_000, 10.0, 'percent', 10);

        $this->assertNotSame(100_000, $result['tax_amount'], 'This is the exact over-charge the audit reported.');
        $this->assertSame(90_000, $result['tax_amount']);
    }

    public function test_fixed_discount_reduces_the_taxable_base(): void
    {
        $result = BillingCalculator::computeTotals(500_000, 9.0, 'fixed', 50_000);

        $this->assertSame(50_000, $result['discount_amount']);
        $this->assertSame(450_000, $result['taxable_amount']);
        $this->assertSame(40_500, $result['tax_amount']);
        $this->assertSame(490_500, $result['total']);
    }

    public function test_zero_tax_rate_produces_no_tax(): void
    {
        $result = BillingCalculator::computeTotals(750_000, 0.0, 'percent', 20);

        $this->assertSame(0, $result['tax_amount']);
        $this->assertSame(600_000, $result['total']);
    }

    public function test_discount_cannot_exceed_the_subtotal(): void
    {
        $result = BillingCalculator::computeTotals(300_000, 9.0, 'fixed', 999_999);

        $this->assertSame(300_000, $result['discount_amount'], 'Discount is clamped to the subtotal.');
        $this->assertSame(0, $result['taxable_amount']);
        $this->assertSame(0, $result['tax_amount']);
        $this->assertSame(0, $result['total']);
    }

    public function test_percentage_discount_is_clamped_to_one_hundred(): void
    {
        $result = BillingCalculator::computeTotals(100_000, 10.0, 'percent', 150);

        $this->assertSame(100_000, $result['discount_amount']);
        $this->assertSame(0, $result['total']);
    }

    public function test_line_total_is_never_negative(): void
    {
        $this->assertSame(0, BillingCalculator::calculateLineTotal(1, 100, 500));
        $this->assertSame(0, BillingCalculator::calculateLineTotal(0, 0, 0));
        $this->assertSame(190, BillingCalculator::calculateLineTotal(2, 100, 10));
    }

    /**
     * The whole point of `taxMode`: per-line rounding genuinely differs from
     * aggregate rounding. Three lines of 105,550 at 9%:
     *   per line : round(9,499.5) = 9,500  ->  28,500
     *   aggregate: round(28,498.5) = 28,499
     * If the two modes ever return the same number here, `taxMode` is dead again.
     */
    public function test_item_mode_taxes_each_line_separately(): void
    {
        $lines = [105_550, 105_550, 105_550];

        $invoiceMode = BillingCalculator::computeTotals(316_650, 9.0, 'fixed', 0, 'invoice');
        $itemMode = BillingCalculator::computeTotals(316_650, 9.0, 'fixed', 0, 'item', $lines);

        $this->assertSame(28_499, $invoiceMode['tax_amount']);
        $this->assertSame(28_500, $itemMode['tax_amount']);
        $this->assertNotSame(
            $invoiceMode['tax_amount'],
            $itemMode['tax_amount'],
            'taxMode must change the result, otherwise the parameter is decorative.'
        );
        $this->assertSame([9_500, 9_500, 9_500], $itemMode['line_taxes']);
    }

    public function test_item_mode_allocates_invoice_discount_proportionally(): void
    {
        $lines = [400_000, 350_000, 250_000];

        $result = BillingCalculator::computeTotals(1_000_000, 10.0, 'fixed', 100_000, 'item', $lines);

        // Shares: 40,000 / 35,000 / 25,000 -> net 360,000 / 315,000 / 225,000
        $this->assertSame([36_000, 31_500, 22_500], $result['line_taxes']);
        $this->assertSame(90_000, $result['tax_amount']);
        $this->assertSame(90_000, array_sum($result['line_taxes']), 'Header tax must equal the sum of line taxes.');
        $this->assertSame(990_000, $result['total']);
    }

    public function test_item_mode_without_line_amounts_falls_back_to_the_invoice_base(): void
    {
        $withLines = BillingCalculator::computeTotals(1_000_000, 10.0, 'fixed', 100_000, 'item', [400_000, 600_000]);
        $withoutLines = BillingCalculator::computeTotals(1_000_000, 10.0, 'fixed', 100_000, 'item');

        $this->assertSame($withLines['tax_amount'], $withoutLines['tax_amount']);
        $this->assertSame([], $withoutLines['line_taxes'], 'No line data means no per-line breakdown.');
    }

    public function test_legacy_subtotal_mode_is_treated_as_invoice_mode(): void
    {
        $legacy = BillingCalculator::computeTotals(750_000, 10.0, 'percent', 5, 'subtotal');
        $invoice = BillingCalculator::computeTotals(750_000, 10.0, 'percent', 5, 'invoice');

        $this->assertSame(BillingCalculator::TAX_MODE_INVOICE, $legacy['tax_mode']);
        $this->assertSame($invoice['total'], $legacy['total']);
        $this->assertSame($invoice['tax_amount'], $legacy['tax_amount']);
    }

    public function test_unknown_tax_mode_falls_back_to_invoice(): void
    {
        $this->assertSame(BillingCalculator::TAX_MODE_INVOICE, BillingCalculator::normalizeTaxMode('nonsense'));
        $this->assertSame(BillingCalculator::TAX_MODE_INVOICE, BillingCalculator::normalizeTaxMode(''));
        $this->assertSame(BillingCalculator::TAX_MODE_ITEM, BillingCalculator::normalizeTaxMode(' ITEM '));
    }

    public function test_allocation_shares_always_sum_to_the_discount(): void
    {
        mt_srand(20261025);

        for ($i = 0; $i < 300; $i++) {
            $count = mt_rand(1, 6);
            $lines = [];
            for ($l = 0; $l < $count; $l++) {
                $lines[] = mt_rand(0, 5_000_000);
            }
            $subtotal = array_sum($lines);
            $discount = mt_rand(0, max(1, $subtotal));

            $shares = BillingCalculator::allocateDiscount($discount, $lines);

            $this->assertCount($count, $shares, "case {$i}");
            $this->assertSame(
                $discount,
                array_sum($shares),
                "case {$i}: shares must add up to exactly the discount, never lose or invent a Toman"
            );

            foreach ($shares as $index => $share) {
                $this->assertGreaterThanOrEqual(0, $share, "case {$i}: negative share");
                $this->assertLessThanOrEqual($lines[$index], $share, "case {$i}: share exceeds its own line");
            }
        }
    }

    /**
     * Property test: the invariant must hold for arbitrary input, not just for
     * the examples above. Seeded so a failure is reproducible.
     */
    public function test_invariant_holds_for_randomised_invoices(): void
    {
        mt_srand(20261025);

        for ($i = 0; $i < 300; $i++) {
            $count = mt_rand(1, 6);
            $lines = [];
            for ($l = 0; $l < $count; $l++) {
                $lines[] = mt_rand(0, 5_000_000);
            }

            $subtotal = array_sum($lines);
            $taxPercent = [0.0, 9.0, 10.0][mt_rand(0, 2)];
            $discountType = mt_rand(0, 1) ? 'percent' : 'fixed';
            $discountValue = $discountType === 'percent'
                ? mt_rand(0, 100)
                : mt_rand(0, max(1, $subtotal));
            $taxMode = mt_rand(0, 1) ? 'item' : 'invoice';

            $result = BillingCalculator::computeTotals($subtotal, $taxPercent, $discountType, $discountValue, $taxMode, $lines);

            $this->assertSame(
                $result['total'],
                ($result['subtotal'] - $result['discount_amount']) + $result['tax_amount'],
                "case {$i}: total must equal subtotal - discount + tax"
            );

            $this->assertGreaterThanOrEqual(0, $result['total'], "case {$i}: negative total");
            $this->assertGreaterThanOrEqual(0, $result['tax_amount'], "case {$i}: negative tax");
            $this->assertGreaterThanOrEqual(0, $result['discount_amount'], "case {$i}: negative discount");
            $this->assertLessThanOrEqual($result['subtotal'], $result['discount_amount'], "case {$i}: discount > subtotal");

            if ($taxMode === 'item') {
                $this->assertSame(
                    $result['tax_amount'],
                    array_sum($result['line_taxes']),
                    "case {$i}: header tax must equal the sum of line taxes"
                );
            }
        }
    }

    public function test_line_amounts_that_do_not_match_subtotal_do_not_break_the_invariant(): void
    {
        // Drifted data: lines sum to 800,000 but subtotal claims 1,000,000.
        $result = BillingCalculator::computeTotals(1_000_000, 10.0, 'fixed', 100_000, 'item', [400_000, 400_000]);

        $this->assertSame(
            $result['total'],
            ($result['subtotal'] - $result['discount_amount']) + $result['tax_amount']
        );
    }
}
