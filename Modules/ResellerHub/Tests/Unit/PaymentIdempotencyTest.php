<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Invoices\InvoiceSettlementService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * P1-2 regression: `recordPayment()` with an idempotency key must be
 * replay-safe — the same key on the same invoice returns the original
 * payment row and never creates a second settled payment.
 */
class PaymentIdempotencyTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createPaymentsTable();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    private function makeInvoice(int $total = 100000): ResellerInvoice
    {
        return ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-TEST-' . uniqid('', true),
            'status' => ResellerInvoice::STATUS_UNPAID,
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => 0,
        ]);
    }

    public function test_same_idempotency_key_returns_original_payment_without_duplicate(): void
    {
        $invoice = $this->makeInvoice();
        $service = app(InvoiceSettlementService::class);

        $first = $service->recordPayment($invoice, 100000, method: 'gateway', idempotencyKey: 'GW-CALL-abc-123');
        $replay = $service->recordPayment($invoice, 100000, method: 'gateway', idempotencyKey: 'GW-CALL-abc-123');

        $this->assertSame($first->id, $replay->id, 'Replay must return the original payment row');
        $this->assertSame(1, $invoice->payments()->count(), 'No duplicate payment row may be created');

        $invoice->refresh();
        $this->assertSame(100000, (int)$invoice->paid_amount);
        $this->assertSame(ResellerInvoice::STATUS_PAID, $invoice->status);
    }

    public function test_different_keys_create_separate_payments(): void
    {
        $invoice = $this->makeInvoice(200000);
        $service = app(InvoiceSettlementService::class);

        $service->recordPayment($invoice, 100000, method: 'gateway', idempotencyKey: 'key-1');
        $service->recordPayment($invoice, 100000, method: 'gateway', idempotencyKey: 'key-2');

        $this->assertSame(2, $invoice->payments()->count());
        $invoice->refresh();
        $this->assertSame(200000, (int)$invoice->paid_amount);
    }

    public function test_null_key_keeps_legacy_behaviour(): void
    {
        $invoice = $this->makeInvoice();
        $service = app(InvoiceSettlementService::class);

        $payment = $service->recordPayment($invoice, 50000, method: 'direct');

        $this->assertNull($payment->idempotency_key);
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_unique_index_blocks_racy_duplicate_insert(): void
    {
        // Even if two requests race past the application-level check, the
        // composite unique index (invoice_id, idempotency_key) is the last
        // line of defence.
        $invoice = $this->makeInvoice();
        $service = app(InvoiceSettlementService::class);

        $service->recordPayment($invoice, 100000, idempotencyKey: 'race-key');

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        ResellerInvoicePayment::query()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'method' => 'gateway',
            'paid_at' => now(),
            'transaction_id' => 'X',
            'status' => ResellerInvoicePayment::STATUS_PAID,
            'idempotency_key' => 'race-key',
        ]);
    }
}
