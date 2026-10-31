<?php

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Actions\Invoices\MarkInvoicePaidAction;
use Modules\ResellerHub\App\Http\Controllers\ResellerInvoiceController;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerInvoiceStatusRequest;
use Modules\ResellerHub\App\Livewire\Portal\Checkout;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\Portal\CheckoutPricingService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Phase 2 regression tests for invoice settlement and checkout totals.
 *
 * The database-backed tests use a private in-memory SQLite connection instead
 * of RefreshDatabase so they can never touch the application's real database.
 */
class Phase2FinancialTest extends TestCase
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
            $table->string('invoice_number', 50)->unique();
            $table->string('type', 20)->default('official');
            $table->string('source', 64)->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->string('sub_account_name', 150)->nullable();
            $table->string('sub_account_mobile', 50)->nullable();
            $table->string('sub_account_email', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->string('discount_type', 20)->default('fixed');
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_ref', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Spatie's Gate::before hook touches the permissions table even when a
        // test defines its own gate. An empty table lets it fail closed and
        // fall through to the test gate.
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('reseller_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('method');
            $table->string('gateway')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('paid');
            $table->timestamps();
        });

        Event::fake();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    private function invoice(int $total, int $paid, string $status = ResellerInvoice::STATUS_UNPAID): ResellerInvoice
    {
        return ResellerInvoice::create([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => $status,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total' => $total,
            'paid_amount' => $paid,
            'paid_at' => $status === ResellerInvoice::STATUS_PAID ? now() : null,
            'created_by' => 1,
        ]);
    }

    private function bindOrderConversion(): void
    {
        $order = new ResellerOrder();

        $this->mock(ResellerBillingService::class, function ($mock) use ($order): void {
            $mock->shouldReceive('convertInvoiceToOrder')->once()->andReturn($order);
        });
    }

    public function test_mark_invoice_paid_records_the_missing_payment_before_setting_status(): void
    {
        $this->bindOrderConversion();

        // Simulates an invoice already marked paid by the old buggy action:
        // status=paid, but no payment row and paid_amount=0.
        $invoice = $this->invoice(1000, 0, ResellerInvoice::STATUS_PAID);

        (new MarkInvoicePaidAction())->execute($invoice, [
            'payment_method' => 'cash',
            'transaction_ref' => 'TX-100',
        ]);

        $invoice->refresh();

        $this->assertSame(1000, $invoice->paid_amount);
        $this->assertSame(ResellerInvoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertDatabaseHas('reseller_invoice_payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);
    }

    public function test_mark_invoice_paid_only_charges_the_remaining_balance(): void
    {
        $this->bindOrderConversion();

        $invoice = $this->invoice(1000, 300);
        $invoice->payments()->create([
            'amount' => 300,
            'method' => 'cash',
            'paid_at' => now(),
            'transaction_id' => 'TX-PARTIAL',
            'status' => 'paid',
        ]);

        (new MarkInvoicePaidAction())->execute($invoice, ['payment_method' => 'cash']);

        $invoice->refresh();

        $this->assertSame(1000, $invoice->paid_amount);
        $this->assertSame(ResellerInvoice::STATUS_PAID, $invoice->status);
        $this->assertSame(2, $invoice->payments()->count());
        $this->assertDatabaseHas('reseller_invoice_payments', [
            'invoice_id' => $invoice->id,
            'amount' => 700,
        ]);
    }

    public function test_manual_status_update_records_the_missing_payment(): void
    {
        Gate::before(static fn($user, $ability): bool => true);

        $user = new User();
        $user->id = 1;
        $this->actingAs($user);

        $invoice = $this->invoice(1000, 0);

        $billing = $this->mock(ResellerBillingService::class, function ($mock): void {
            $mock->shouldReceive('syncOrdersForInvoice')->once();
        });

        $controller = new ResellerInvoiceController(
            $billing,
            $this->mock(ModuleCapabilityCatalogService::class),
            $this->mock(ResellerWorkflowService::class)
        );

        $request = UpdateResellerInvoiceStatusRequest::create('/reseller/invoices/' . $invoice->id, 'POST', [
            'status' => ResellerInvoice::STATUS_PAID,
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setUserResolver(static fn() => auth()->user());
        $request->validateResolved();

        $controller->updateStatus($request, $invoice);

        $invoice->refresh();

        $this->assertSame(1000, $invoice->paid_amount);
        $this->assertSame(ResellerInvoice::STATUS_PAID, $invoice->status);
        $this->assertDatabaseHas('reseller_invoice_payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'method' => 'manual',
        ]);
    }

    public function test_package_setup_fee_is_included_in_checkout_total(): void
    {
        $package = new ResellerPackage([
            'name' => 'Pro',
            'price_type' => ResellerPackage::PRICE_TYPE_SUBSCRIPTION,
            'monthly_price' => 1000,
            'setup_fee' => 250,
        ]);

        $total = (new CheckoutPricingService())->getTotalPrice($package, 1);

        $this->assertSame(1250, $total);
    }

    public function test_free_package_with_setup_fee_is_not_treated_as_zero_payable(): void
    {
        $package = new ResellerPackage([
            'name' => 'Free with setup',
            'price_type' => ResellerPackage::PRICE_TYPE_FREE,
            'monthly_price' => 0,
            'setup_fee' => 250,
        ]);

        $checkout = new Checkout();
        $checkout->whiteLabelType = 'default';

        $itemsMethod = new \ReflectionMethod(Checkout::class, 'resolveInvoiceItems');
        $itemsMethod->setAccessible(true);
        $items = $itemsMethod->invoke($checkout, $package, 1, $package->getFinalPriceForMonths(1));

        $setupItem = collect($items)->firstWhere('item_type', 'setup_fee');
        $this->assertNotNull($setupItem);
        $this->assertSame(250, $setupItem['unit_price']);

        $payableMethod = new \ReflectionMethod(Checkout::class, 'calculatePayableTotal');
        $payableMethod->setAccessible(true);
        $this->assertSame(250, $payableMethod->invoke($checkout, $package, 1));
    }
}
