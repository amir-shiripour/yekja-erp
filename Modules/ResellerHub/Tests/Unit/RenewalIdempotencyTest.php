<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Billing\OrderProvisioningService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * A paid renewal invoice extends its order exactly once.
 * `convertInvoiceToOrder()` is reachable from the gateway callback, the
 * "mark as paid" button, a manual status change and `syncOrdersForInvoice()`.
 * Before the `renewal_applied_at` guard every replay added another full period.
 */
class RenewalIdempotencyTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createInvoiceItemsTable();
        $this->createPaymentsTable();
        $this->createOrdersTable();
        $this->createTenantsTable();
        $this->createTenantModulesTable();

        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();

        parent::tearDown();
    }

    private function tenant(int $id = 1): void
    {
        DB::table('tenants')->insert([
            'id' => $id,
            'reseller_id' => 1,
            'name' => 'Tenant ' . $id,
            'subdomain' => 'tenant' . $id,
            'status' => 'active',
            'plan_expires_at' => '2026-12-31 00:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function baseInvoice(): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => 1,
            'invoice_number' => 'INV-BASE-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_PAID,
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-08',
            'total' => 1000,
            'paid_amount' => 1000,
            'paid_at' => now(),
        ]);
    }

    private function order(ResellerInvoice $base): void
    {
        DB::table('reseller_orders')->insert([
            'id' => 1,
            'reseller_id' => 1,
            'invoice_id' => $base->id,
            'tenant_id' => 1,
            'order_number' => 'ORD-BASE-1',
            'sub_account_name' => 'Tenant 1',
            'status' => 'active',
            'billing_cycle' => 'yearly',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'renewal_date' => '2026-12-31',
            'total_amount' => 1000,
            'contract_value' => 1000,
            'renewal_price' => 1000,
            'modules_snapshot' => json_encode([]),
            'provisioning_status' => 'provisioned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function renewalInvoice(ResellerInvoice $base): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => 1,
            'parent_invoice_id' => $base->id,
            'source_order_id' => 1,
            'invoice_number' => 'INV-REN-' . uniqid('', true),
            'type' => 'official',
            'source' => 'renewal',
            'status' => ResellerInvoice::STATUS_PAID,
            'issue_date' => '2026-12-01',
            'due_date' => '2026-12-31',
            'total' => 1000,
            'paid_amount' => 1000,
            'paid_at' => now(),
            'meta' => [
                'is_renewal' => true,
                'duration_months' => 12,
            ],
        ]);
    }

    public function test_replaying_a_renewal_extends_the_order_only_once(): void
    {
        $this->tenant();

        $base = $this->baseInvoice();
        $this->order($base);
        $renewal = $this->renewalInvoice($base);

        $service = $this->app->make(OrderProvisioningService::class);

        $service->convertInvoiceToOrder($renewal);
        $afterFirst = ResellerOrder::find(1)->end_date->toDateString();

        // A second callback, a "mark as paid" click, a status update — any of
        // them replay the same conversion.
        $service->convertInvoiceToOrder($renewal->fresh());
        $service->convertInvoiceToOrder($renewal->fresh());

        $afterReplays = ResellerOrder::find(1)->end_date->toDateString();

        $this->assertSame('2027-12-31', $afterFirst, 'One period of 12 months should have been added.');
        $this->assertSame($afterFirst, $afterReplays, 'A replayed renewal must not extend the order again.');
        $this->assertNotNull($renewal->fresh()->renewal_applied_at);
    }

    public function test_an_unpaid_renewal_invoice_never_extends_the_order(): void
    {
        $this->tenant();

        $base = $this->baseInvoice();
        $this->order($base);

        $renewal = $this->renewalInvoice($base);
        $renewal->forceFill(['status' => ResellerInvoice::STATUS_UNPAID, 'paid_amount' => 0])->save();

        $this->app->make(OrderProvisioningService::class)->convertInvoiceToOrder($renewal);

        $this->assertSame('2026-12-31', ResellerOrder::find(1)->end_date->toDateString());
        $this->assertNull($renewal->fresh()->renewal_applied_at);
    }
}
