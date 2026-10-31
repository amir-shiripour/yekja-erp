<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Payment\ProcessInvoicePaymentService;
use ReflectionMethod;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * Only the reseller side may record money as received.
 *
 * A tenant user submitting the "bank transfer" form used to create a settled
 * payment row, which flipped the invoice to paid and unlocked modules — with no
 * money actually moving. Their submission must stay a *claim* until the
 * reseller confirms it.
 */
class ManualPaymentGuardTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createPaymentsTable();

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

    private function service(): ProcessInvoicePaymentService
    {
        return $this->app->make(ProcessInvoicePaymentService::class);
    }

    private function callProtected(object $object, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }

    public function test_a_claim_does_not_settle_the_invoice(): void
    {
        $invoice = $this->invoice(1000);

        /** @var ResellerInvoicePayment[] $payments */
        $payments = $this->callProtected($this->service(), 'processDirectPayments', [
            $invoice,
            [['method' => 'transfer', 'amount' => 1000, 'transaction_id' => 'REF-1']],
            1000,
            now(),
            null,
            false, // settle: false → claim
        ]);

        $this->assertCount(1, $payments);
        $this->assertSame(ResellerInvoicePayment::STATUS_PENDING, $payments[0]->status);

        $invoice->refresh();
        $this->assertSame(0, $invoice->calculatePaidAmount());
        $this->assertFalse($invoice->isPaid());
        $this->assertSame(ResellerInvoice::STATUS_UNPAID, $invoice->status);
    }

    public function test_the_reseller_side_settles_immediately(): void
    {
        $invoice = $this->invoice(1000);

        /** @var ResellerInvoicePayment[] $payments */
        $payments = $this->callProtected($this->service(), 'processDirectPayments', [
            $invoice,
            [['method' => 'cash', 'amount' => 1000]],
            1000,
            now(),
            null,
            true, // settle: true → reseller recording money received
        ]);

        $this->assertSame(ResellerInvoicePayment::STATUS_PAID, $payments[0]->status);
        $this->assertSame(1000, $invoice->fresh()->calculatePaidAmount());
    }

    public function test_a_user_who_cannot_change_the_invoice_cannot_settle_offline(): void
    {
        $this->createUsersTable();
        $this->createResellersTable();
        $this->createPermissionTables();

        DB::table('users')->insert([
            'id' => 42,
            'name' => 'Tenant User',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $actor = User::find(42);

        $this->assertFalse(
            $this->callProtected($this->service(), 'actorCanSettleOffline', [$actor, $this->invoice()])
        );
    }

    public function test_the_reseller_owner_can_settle_offline(): void
    {
        $this->createUsersTable();
        $this->createResellersTable();
        $this->createPermissionTables();

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Reseller Owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('resellers')->insert([
            'id' => 1,
            'user_id' => 1,
            'company_name' => 'Reseller',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $actor = User::find(1);

        $this->assertTrue(
            $this->callProtected($this->service(), 'actorCanSettleOffline', [$actor, $this->invoice()])
        );
    }

    public function test_a_guest_can_never_settle_offline(): void
    {
        $this->assertFalse(
            $this->callProtected($this->service(), 'actorCanSettleOffline', [null, $this->invoice()])
        );
    }
}
