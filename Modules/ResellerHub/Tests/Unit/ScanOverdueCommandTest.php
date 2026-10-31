<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * The daily `reseller:scan-overdue` schedule is the only thing that moves
 * unpaid invoices to "overdue" and applies dunning. If it throws, the whole
 * lifecycle silently stops — no reminders, no suspensions, no cancellation.
 * These tests execute the real command against an isolated database.
 */
class ScanOverdueCommandTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createInvoiceItemsTable();
        $this->createOrdersTable();

        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();

        parent::tearDown();
    }

    private function overdueCandidate(int $daysLate = 30): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'issue_date' => now()->subDays($daysLate + 10)->toDateString(),
            'due_date' => now()->subDays($daysLate)->toDateString(),
            'total' => 5000,
            'paid_amount' => 0,
        ]);
    }

    public function test_the_dry_run_completes_and_reports_the_invoice(): void
    {
        $invoice = $this->overdueCandidate();

        $this->artisan('reseller:scan-overdue', ['--dry-run' => true])
            ->expectsOutputToContain($invoice->invoice_number)
            ->expectsOutputToContain('Marked 1 invoices as overdue.')
            ->assertExitCode(0);

        $this->assertSame(
            ResellerInvoice::STATUS_UNPAID,
            $invoice->fresh()->status,
            'A dry run must not modify any record.'
        );
    }

    public function test_it_transitions_a_late_invoice_to_overdue(): void
    {
        $invoice = $this->overdueCandidate();

        $this->mock(ResellerWorkflowService::class, function ($mock): void {
            $mock->shouldReceive('onInvoiceStatusChanged')->once();
        });
        $this->mock(ResellerAuditService::class);

        $this->artisan('reseller:scan-overdue', ['--cancel-after-days' => 0])
            ->assertExitCode(0);

        $this->assertSame(ResellerInvoice::STATUS_OVERDUE, $invoice->fresh()->status);
    }

    public function test_the_dunning_branch_cancels_orders_of_severely_overdue_invoices(): void
    {
        $invoice = $this->overdueCandidate(30);

        ResellerOrder::create([
            'reseller_id' => 1,
            'invoice_id' => $invoice->id,
            'order_number' => 'ORD-' . uniqid('', true),
            'status' => ResellerOrder::STATUS_PENDING,
            'billing_cycle' => 'yearly',
        ]);

        $this->mock(ResellerWorkflowService::class, function ($mock): void {
            $mock->shouldReceive('onInvoiceStatusChanged')->once();
            $mock->shouldReceive('onOrderStatusChanged')->once();
        });
        $this->mock(ResellerAuditService::class);

        $this->artisan('reseller:scan-overdue', ['--cancel-after-days' => 7])
            ->assertExitCode(0);

        $this->assertSame(
            ResellerOrder::STATUS_CANCELLED,
            ResellerOrder::where('invoice_id', $invoice->id)->first()->status
        );
    }
}
