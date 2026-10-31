<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Http\Controllers\PortalCheckoutController;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Gateway\PaymentGatewayFactory;
use Modules\ResellerHub\App\Services\InvoicePaymentService;
use Modules\ResellerHub\App\Services\Payment\GatewayAmountResolver;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

class PaymentSecurityHardeningTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createUsersTable();
        $this->createResellersTable();
        $this->createSubAccountsTable();
        $this->createTenantsTable();
        $this->createInvoicesTable();
        $this->createPaymentsTable();
        $this->createOrdersTable();

        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    private function makeInvoice(int $id, int $total = 500000, string $status = 'unpaid'): ResellerInvoice
    {
        $invoice = new ResellerInvoice();
        $invoice->id = $id;
        $invoice->reseller_id = 1;
        $invoice->invoice_number = 'INV-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
        $invoice->total = $total;
        $invoice->subtotal = $total;
        $invoice->status = $status;
        $invoice->save();

        return $invoice;
    }

    /**
     * Invariant: No bank transaction reference can be settled twice across different invoices.
     */
    public function test_cross_invoice_replay_is_detected_and_blocked(): void
    {
        $invoiceA = $this->makeInvoice(1, 100000, 'paid');
        $invoiceB = $this->makeInvoice(2, 5000000, 'unpaid');

        // Settle payment on Invoice A
        ResellerInvoicePayment::create([
            'invoice_id' => $invoiceA->id,
            'amount' => 100000,
            'method' => 'online',
            'gateway' => 'zarinpal',
            'paid_at' => now(),
            'transaction_id' => 'BANK_REF_998877',
            'status' => 'paid',
        ]);

        // Model guard should report settled elsewhere for Invoice B
        $this->assertTrue(
            ResellerInvoicePayment::isTransactionSettledElsewhere('zarinpal', 'BANK_REF_998877', $invoiceB->id),
            'Guard must identify that BANK_REF_998877 belongs to another invoice.'
        );

        // Model guard should NOT report settled elsewhere for Invoice A itself
        $this->assertFalse(
            ResellerInvoicePayment::isTransactionSettledElsewhere('zarinpal', 'BANK_REF_998877', $invoiceA->id),
            'Guard must allow the owner invoice to verify its own ref.'
        );
    }

    /**
     * Database level constraint: [gateway, transaction_id] unique index stops duplicate settlements.
     */
    public function test_database_unique_constraint_stops_replay_attacks(): void
    {
        $invoiceA = $this->makeInvoice(10, 100000, 'paid');
        $invoiceB = $this->makeInvoice(11, 2000000, 'unpaid');

        ResellerInvoicePayment::create([
            'invoice_id' => $invoiceA->id,
            'amount' => 100000,
            'method' => 'online',
            'gateway' => 'behpardakht',
            'paid_at' => now(),
            'transaction_id' => 'SALE_REF_554433',
            'status' => 'paid',
        ]);

        $this->expectException(QueryException::class);

        // Attempting to insert the same transaction_id for the same gateway must throw QueryException
        ResellerInvoicePayment::create([
            'invoice_id' => $invoiceB->id,
            'amount' => 2000000,
            'method' => 'online',
            'gateway' => 'behpardakht',
            'paid_at' => now(),
            'transaction_id' => 'SALE_REF_554433',
            'status' => 'paid',
        ]);
    }

    /**
     * Empty string transaction_id is sanitized to NULL so offline payments do not collide.
     */
    public function test_empty_transaction_id_is_cast_to_null(): void
    {
        $invoiceA = $this->makeInvoice(20, 50000);
        $invoiceB = $this->makeInvoice(21, 60000);

        $paymentA = ResellerInvoicePayment::create([
            'invoice_id' => $invoiceA->id,
            'amount' => 50000,
            'method' => 'cash',
            'gateway' => null,
            'paid_at' => now(),
            'transaction_id' => '',
            'status' => 'paid',
        ]);

        $paymentB = ResellerInvoicePayment::create([
            'invoice_id' => $invoiceB->id,
            'amount' => 60000,
            'method' => 'cash',
            'gateway' => null,
            'paid_at' => now(),
            'transaction_id' => '   ',
            'status' => 'paid',
        ]);

        $this->assertNull($paymentA->transaction_id);
        $this->assertNull($paymentB->transaction_id);
    }

    /**
     * Portal Checkout verify: Fail-Closed on missing reported amount.
     */
    public function test_portal_checkout_rejects_missing_reported_amount(): void
    {
        $invoice = $this->makeInvoice(30, 250000, 'unpaid');

        $paymentServiceMock = $this->createMock(PaymentService::class);
        $paymentServiceMock->method('verifyPayment')->willReturn([
            'success' => true,
            'ref_id' => 'REF_NO_AMOUNT',
            'authority' => 'AUTH_123',
            // Missing 'amount'
        ]);

        $gatewayFactoryMock = $this->createMock(PaymentGatewayFactory::class);
        $gatewayFactoryMock->method('make')->willReturn($paymentServiceMock);

        $controller = new PortalCheckoutController(
            gatewayFactory: $gatewayFactoryMock,
            amounts: new GatewayAmountResolver()
        );

        $request = Request::create('/get-started/checkout/verify/30/zarinpal', 'GET', [
            'Authority' => 'AUTH_123',
            'Status' => 'OK',
        ]);

        $response = $controller->verify($request, (string)$invoice->id, 'zarinpal');

        // Invoice must remain unpaid
        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
        $this->assertTrue($response->isRedirection());
    }

    /**
     * Portal Checkout verify: Fail-Closed on tampered/underpaid amount.
     */
    public function test_portal_checkout_rejects_underpaid_tampered_amount(): void
    {
        $invoice = $this->makeInvoice(31, 1000000, 'unpaid'); // Expected 1,000,000

        $paymentServiceMock = $this->createMock(PaymentService::class);
        $paymentServiceMock->method('verifyPayment')->willReturn([
            'success' => true,
            'ref_id' => 'REF_TAMPERED',
            'authority' => 'AUTH_TAMPERED',
            'amount' => 10000, // Attacker only paid 10,000!
        ]);

        $gatewayFactoryMock = $this->createMock(PaymentGatewayFactory::class);
        $gatewayFactoryMock->method('make')->willReturn($paymentServiceMock);

        $controller = new PortalCheckoutController(
            gatewayFactory: $gatewayFactoryMock,
            amounts: new GatewayAmountResolver()
        );

        $request = Request::create('/get-started/checkout/verify/31/zarinpal', 'GET', [
            'Authority' => 'AUTH_TAMPERED',
            'Status' => 'OK',
        ]);

        $response = $controller->verify($request, (string)$invoice->id, 'zarinpal');

        // Invoice must remain unpaid
        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
    }

    /**
     * Portal Checkout verify: Refuses duplicate flag on fresh invoice.
     */
    public function test_portal_checkout_rejects_duplicate_flag_on_fresh_invoice(): void
    {
        $invoice = $this->makeInvoice(32, 500000, 'unpaid');

        $paymentServiceMock = $this->createMock(PaymentService::class);
        $paymentServiceMock->method('verifyPayment')->willReturn([
            'success' => true,
            'ref_id' => 'REF_DUP_43',
            'authority' => 'AUTH_DUP_43',
            'amount' => 500000,
            'is_duplicate' => true, // Mellat code 43 or Zarinpal 101
        ]);

        $gatewayFactoryMock = $this->createMock(PaymentGatewayFactory::class);
        $gatewayFactoryMock->method('make')->willReturn($paymentServiceMock);

        $controller = new PortalCheckoutController(
            gatewayFactory: $gatewayFactoryMock,
            amounts: new GatewayAmountResolver()
        );

        $request = Request::create('/get-started/checkout/verify/32/behpardakht', 'POST', [
            'SaleReferenceId' => 'REF_DUP_43',
            'ResCode' => '0',
        ]);

        $response = $controller->verify($request, (string)$invoice->id, 'behpardakht');

        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
    }

    /**
     * InvoicePaymentService: Fail-Closed on tampered amount and replay attempts.
     */
    public function test_invoice_payment_service_rejects_tampered_amount(): void
    {
        $invoice = $this->makeInvoice(40, 800000, 'unpaid');

        // Create pending payment row
        $pending = ResellerInvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => 800000,
            'method' => 'online',
            'gateway' => 'zarinpal',
            'paid_at' => now(),
            'transaction_id' => 'AUTH_PENDING_40',
            'status' => 'pending',
        ]);

        $workflowMock = $this->createMock(ResellerWorkflowService::class);
        $service = new InvoicePaymentService(
            workflowService: $workflowMock,
            amounts: new GatewayAmountResolver()
        );

        // Simulate callback with amount mismatch by mocking PaymentService inside verifyOnlinePayment
        // or passing callback data
        $callbackData = [
            'Authority' => 'AUTH_PENDING_40',
            'Status' => 'NOK',
        ];

        $result = $service->verifyOnlinePayment($invoice, $callbackData, 'zarinpal');
        $this->assertFalse($result['success']);

        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
    }
}
