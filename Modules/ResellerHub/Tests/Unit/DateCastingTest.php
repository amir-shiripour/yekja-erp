<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Orders\RenewalPeriodResolver;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * Date columns on invoices and orders must be Carbon instances.
 *
 * Without the casts they come back as raw strings, so every `->greaterThan()`,
 * `->isFuture()` or `?->format()` call on them is a fatal error — which is
 * exactly how the daily overdue scan and the order detail page were broken.
 */
class DateCastingTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createOrdersTable();
        $this->createPaymentsTable();

        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();

        parent::tearDown();
    }

    private function invoice(array $attributes = []): ResellerInvoice
    {
        return ResellerInvoice::create(array_merge([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-08',
            'total' => 1000,
            'paid_amount' => 0,
        ], $attributes));
    }

    private function order(array $attributes = []): ResellerOrder
    {
        return ResellerOrder::create(array_merge([
            'reseller_id' => 1,
            'order_number' => 'ORD-' . uniqid('', true),
            'status' => ResellerOrder::STATUS_ACTIVE,
            'billing_cycle' => 'yearly',
            'start_date' => '2026-01-01',
            'end_date' => '2027-01-01',
            'renewal_date' => '2027-01-01',
        ], $attributes));
    }

    public function test_invoice_dates_are_carbon_instances(): void
    {
        $invoice = $this->invoice();

        $this->assertInstanceOf(Carbon::class, $invoice->issue_date);
        $this->assertInstanceOf(Carbon::class, $invoice->due_date);
        $this->assertSame('2026-01-08', $invoice->due_date->format('Y-m-d'));
    }

    public function test_order_dates_are_carbon_instances(): void
    {
        $order = $this->order();

        $this->assertInstanceOf(Carbon::class, $order->start_date);
        $this->assertInstanceOf(Carbon::class, $order->end_date);
        $this->assertInstanceOf(Carbon::class, $order->renewal_date);
        $this->assertTrue($order->end_date->isFuture());
    }

    public function test_a_custom_cycle_order_resolves_its_period_without_a_type_error(): void
    {
        $order = $this->order([
            'billing_cycle' => 'custom',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-01',
        ]);

        $period = (new RenewalPeriodResolver())->resolve($order);

        $this->assertTrue($period['is_custom']);
        $this->assertSame(90, $period['days']);
    }

    public function test_an_invoice_exposes_all_of_its_orders(): void
    {
        $invoice = $this->invoice();

        $this->order(['invoice_id' => $invoice->id]);

        $this->assertCount(1, $invoice->fresh()->orders);
    }
}
