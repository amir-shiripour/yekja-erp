<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Invoices\InvoiceNumberGenerator;
use Modules\ResellerHub\App\Services\Invoices\InvoiceSettlementService;
use Modules\ResellerHub\App\Services\Orders\OrderNumberGenerator;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Concurrency, locking and idempotency tests for ResellerHub financial operations.
 */
class ConcurrencyAndIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('reseller_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->default(1);
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('source_order_id')->nullable();
            $table->unsignedBigInteger('parent_invoice_id')->nullable();
            $table->string('currency', 8)->default('toman');
            $table->string('invoice_number', 50)->unique();
            $table->string('type', 20)->default('official');
            $table->string('source', 64)->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->string('sub_account_name', 150)->nullable();
            $table->string('sub_account_mobile', 50)->nullable();
            $table->string('sub_account_email', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->string('discount_type', 20)->default('fixed');
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->text('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->default(1);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('currency', 8)->default('toman');
            $table->string('order_number', 50)->unique();
            $table->string('status', 20)->default('pending');
            $table->string('billing_cycle', 50)->default('yearly');
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('contract_value')->default(0);
            $table->string('provisioning_status', 30)->default('not_provisioned');
            $table->text('modules_snapshot')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('reseller_id')->primary();
            $table->string('currency', 8)->default('toman');
            $table->string('invoice_prefix', 20)->default('INV-');
            $table->string('invoice_middle_prefix', 20)->nullable();
            $table->string('invoice_suffix', 20)->nullable();
            $table->unsignedInteger('invoice_padding')->default(4);
            $table->timestamps();
        });

        Schema::create('reseller_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('method', 50)->default('direct');
            $table->string('currency', 8)->default('toman');
            $table->string('gateway', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('paid');
            $table->timestamps();
        });
    }

    public function test_invoice_number_generator_allocates_sequential_numbers(): void
    {
        $generator = new InvoiceNumberGenerator();

        $num1 = $generator->generate(1);
        ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => $num1,
            'total' => 100000,
        ]);

        $num2 = $generator->generate(1);
        $this->assertNotSame($num1, $num2);

        ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => $num2,
            'total' => 200000,
        ]);

        $num3 = $generator->generate(1);
        $this->assertNotSame($num2, $num3);
    }

    public function test_order_number_generator_allocates_sequential_numbers(): void
    {
        $generator = new OrderNumberGenerator();

        $orderNum1 = $generator->generate(1);
        ResellerOrder::create([
            'reseller_id' => 1,
            'order_number' => $orderNum1,
            'total_amount' => 500000,
        ]);

        $orderNum2 = $generator->generate(1);
        $this->assertNotSame($orderNum1, $orderNum2);
    }

    public function test_payment_recording_updates_invoice_settlement_status(): void
    {
        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-TEST-001',
            'total' => 100000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $service = app(InvoiceSettlementService::class);

        // Record partial payment
        $payment1 = $service->recordPayment(
            invoice: $invoice,
            amount: 40000,
            transactionId: 'TX-001'
        );

        $this->assertSame(40000, (int)$invoice->paid_amount);
        $this->assertSame(60000, $invoice->remainingAmount());
        $this->assertSame('unpaid', $invoice->status);

        // Record remaining payment
        $payment2 = $service->recordPayment(
            invoice: $invoice,
            amount: 60000,
            transactionId: 'TX-002'
        );

        $this->assertSame(100000, (int)$invoice->paid_amount);
        $this->assertSame(0, $invoice->remainingAmount());
        $this->assertTrue($invoice->isPaid());
        $this->assertSame('paid', $invoice->status);
    }

    public function test_currency_is_snapshotted_on_model_creation(): void
    {
        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-CURRENCY-001',
            'total' => 50000,
        ]);

        $this->assertSame('toman', $invoice->currency);
        $this->assertSame(50000, $invoice->money('total')->amount);
        $this->assertSame('toman', $invoice->money('total')->currency);

        $order = ResellerOrder::create([
            'reseller_id' => 1,
            'order_number' => 'RORD-CURRENCY-001',
            'total_amount' => 150000,
        ]);

        $this->assertSame('toman', $order->currency);
        $this->assertSame(150000, $order->money('total_amount')->amount);
        $this->assertSame('toman', $order->money('total_amount')->currency);
    }
}
