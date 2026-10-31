<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Actions\Invoices\CancelInvoiceAction;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Adapters\WalletAdapterInterface;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * Cancelling an invoice must never quietly erase money that was already
 * collected. Wallet payments are refunded, gateway payments block the
 * cancellation until a refund is recorded at the PSP, and offline payments are
 * simply reversed.
 */
class CancelInvoiceGuardTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createPaymentsTable();
        $this->createOrdersTable();
        $this->createInvoiceItemsTable();

        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();

        parent::tearDown();
    }

    private function invoice(int $total = 1000): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total' => $total,
            'paid_amount' => 0,
        ]);
    }

    private function pay(ResellerInvoice $invoice, int $amount, string $method, ?string $gateway = null): ResellerInvoicePayment
    {
        return $invoice->payments()->create([
            'amount' => $amount,
            'method' => $method,
            'gateway' => $gateway,
            'paid_at' => now(),
            'transaction_id' => 'TX-' . uniqid('', true),
            'status' => ResellerInvoicePayment::STATUS_PAID,
        ]);
    }

    private function action(?WalletAdapterInterface $wallet = null): CancelInvoiceAction
    {
        $billing = $this->mock(ResellerBillingService::class, function ($mock): void {
            $mock->shouldReceive('syncOrdersForInvoice')->andReturnNull();
        });

        return new CancelInvoiceAction($billing, $wallet ?? $this->mock(WalletAdapterInterface::class));
    }

    public function test_a_gateway_settled_invoice_cannot_be_cancelled(): void
    {
        $invoice = $this->invoice(1000);
        $this->pay($invoice, 400, 'online', 'zarinpal');

        $this->expectException(ResellerHubException::class);

        try {
            $this->action()->execute($invoice);
        } finally {
            $invoice->refresh();
            $this->assertSame(ResellerInvoice::STATUS_UNPAID, $invoice->status, 'The invoice must stay untouched.');
            $this->assertSame(
                ResellerInvoicePayment::STATUS_PAID,
                $invoice->payments()->first()->status,
                'The settled payment must stay settled.'
            );
        }
    }

    public function test_an_offline_payment_can_be_cancelled(): void
    {
        $invoice = $this->invoice(1000);
        $this->pay($invoice, 400, 'cash');

        $this->action()->execute($invoice);

        $invoice->refresh();
        $this->assertSame(ResellerInvoice::STATUS_CANCELLED, $invoice->status);
        $this->assertSame(0, (int)$invoice->paid_amount);
    }

    public function test_a_wallet_payment_is_refunded_before_cancellation(): void
    {
        $this->createUsersTable();
        $this->createSubAccountsTable();

        DB::table('users')->insert([
            'id' => 5,
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('reseller_sub_accounts')->insert([
            'id' => 1,
            'reseller_id' => 1,
            'user_id' => 5,
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'sub_account_email' => 'customer@example.test',
            'total' => 1000,
            'paid_amount' => 0,
        ]);

        $this->pay($invoice, 400, 'wallet');

        $refunded = 0;
        $wallet = $this->mock(WalletAdapterInterface::class, function ($mock) use (&$refunded): void {
            $mock->shouldReceive('refund')
                ->once()
                ->andReturnUsing(function ($user, int $amount) use (&$refunded): bool {
                    $refunded = $amount;

                    return true;
                });
        });

        $this->action($wallet)->execute($invoice);

        $this->assertSame(400, $refunded, 'The wallet balance must be credited back.');
        $this->assertSame(ResellerInvoice::STATUS_CANCELLED, $invoice->fresh()->status);
    }
}
