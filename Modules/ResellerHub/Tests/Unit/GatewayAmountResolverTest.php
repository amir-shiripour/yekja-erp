<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Payment\GatewayAmountResolver;
use Modules\ResellerHub\App\Support\CurrencyContext;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * Every gateway in this installation charges in one currency, while invoices
 * are denominated in the reseller's. The conversion has to be identical on the
 * checkout page, the reseller payment page and the verification callback.
 */
class GatewayAmountResolverTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createSettingsTable();
        $this->createResellerSettingsTable();

        DB::table('settings')->insert([
            ['key' => 'payment_currency', 'value' => 'toman'],
        ]);

        GatewayAmountResolver::flush();
        CurrencyContext::clear();
        ResellerSetting::clearRuntimeCache();
    }

    protected function tearDown(): void
    {
        GatewayAmountResolver::flush();
        CurrencyContext::clear();
        ResellerSetting::clearRuntimeCache();

        $this->shutdownIsolatedDatabase();

        parent::tearDown();
    }

    private function invoice(int $total, int $resellerId = 1): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => $resellerId,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total' => $total,
            'paid_amount' => 0,
        ]);
    }

    public function test_the_payment_currency_comes_from_settings(): void
    {
        $this->assertSame('toman', (new GatewayAmountResolver())->paymentCurrency());
    }

    public function test_a_rial_invoice_is_divided_by_ten_for_a_toman_gateway(): void
    {
        $this->assertSame(100, (new GatewayAmountResolver())->convert(1000, 'rial'));
    }

    public function test_a_toman_invoice_is_charged_as_is(): void
    {
        $this->assertSame(1000, (new GatewayAmountResolver())->convert(1000, 'toman'));
    }

    public function test_the_invoice_currency_is_read_from_the_reseller_settings(): void
    {
        DB::table('reseller_settings')->insert([
            'reseller_id' => 1,
            'currency' => 'rial',
            'tax_mode' => 'invoice',
            'default_tax_rate' => 0,
        ]);

        $invoice = $this->invoice(2_000_000);
        $resolver = new GatewayAmountResolver();

        $this->assertSame('rial', $resolver->sourceCurrency($invoice));
        $this->assertSame(200_000, $resolver->forInvoice($invoice)['amount']);
        $this->assertSame(0.1, $resolver->forInvoice($invoice)['factor']);
    }

    public function test_a_currency_snapshot_on_the_invoice_wins_over_current_settings(): void
    {
        DB::table('reseller_settings')->insert([
            'reseller_id' => 1,
            'currency' => 'rial',
            'tax_mode' => 'invoice',
            'default_tax_rate' => 0,
        ]);

        $invoice = $this->invoice(1000);
        $invoice->forceFill(['currency' => 'toman'])->save();

        $this->assertSame(1000, (new GatewayAmountResolver())->forInvoice($invoice->fresh())['amount']);
    }
}
