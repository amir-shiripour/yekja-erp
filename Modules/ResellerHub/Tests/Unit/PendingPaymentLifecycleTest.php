<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Modules\ResellerHub\App\Actions\Invoices\CancelInvoiceAction;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Http\Controllers\ResellerDashboardController;
use Modules\ResellerHub\App\Http\Requests\RenewTenantRequest;
use Modules\ResellerHub\App\Jobs\ProvisionTenantJob;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\Adapters\WalletAdapterInterface;
use Modules\ResellerHub\App\Services\Checkout\PendingCheckoutService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

class PendingPaymentLifecycleTest extends TestCase
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

    private function service(): PendingCheckoutService
    {
        $walletMock = $this->createMock(WalletAdapterInterface::class);
        $billingMock = $this->createMock(ResellerBillingService::class);
        $cancelAction = new CancelInvoiceAction($billingMock, $walletMock);

        return new PendingCheckoutService($cancelAction);
    }

    private function makeUser(int $id = 1, string $mobile = '09120000001'): User
    {
        $user = new User();
        $user->id = $id;
        $user->name = 'Test User';
        $user->email = 'user@example.com';
        $user->mobile = $mobile;
        $user->save();

        return $user;
    }

    private function makeSubAccount(User $user, int $resellerId = 1): ResellerSubAccount
    {
        return ResellerSubAccount::create([
            'reseller_id' => $resellerId,
            'user_id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'status' => 'active',
        ]);
    }

    private function makePendingTenant(ResellerSubAccount $subAccount, string $subdomain = 'clinic1'): Tenant
    {
        $tenant = new Tenant();
        $tenant->reseller_id = $subAccount->reseller_id;
        $tenant->sub_account_id = $subAccount->id;
        $tenant->name = 'Clinic 1';
        $tenant->subdomain = $subdomain;
        $tenant->status = Tenant::STATUS_PENDING_PAYMENT;
        $tenant->checkout_expires_at = now()->addMinutes(60);
        $tenant->save();

        $subAccount->update(['tenant_id' => $tenant->id]);

        return $tenant;
    }

    /**
     * 1. findReusable with a valid reservation returns the same tenant.
     */
    public function test_find_reusable_returns_active_reservation(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount, 'mysubdomain');

        $found = $this->service()->findReusable($user, 1);

        $this->assertNotNull($found);
        $this->assertSame($tenant->id, $found->id);
        $this->assertSame('mysubdomain', $found->subdomain);
    }

    /**
     * 2. findReusable returns null when invoice is already paid.
     */
    public function test_find_reusable_returns_null_when_invoice_is_paid(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-001',
            'status' => InvoiceStatus::PAID->value,
            'total' => 1000,
        ]);

        $found = $this->service()->findReusable($user, 1);
        $this->assertNull($found);
    }

    /**
     * 3. findReusable returns null when a settled payment exists.
     */
    public function test_find_reusable_returns_null_when_settled_payment_exists(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-002',
            'status' => InvoiceStatus::UNPAID->value,
            'total' => 1000,
        ]);

        ResellerInvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'method' => 'online',
            'status' => ResellerInvoicePayment::STATUS_PAID,
        ]);

        $found = $this->service()->findReusable($user, 1);
        $this->assertNull($found);
    }

    /**
     * 4. findReusable returns null when checkout has expired.
     */
    public function test_find_reusable_returns_null_when_checkout_expired(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);
        $tenant->update(['checkout_expires_at' => now()->subMinutes(5)]);

        $found = $this->service()->findReusable($user, 1);
        $this->assertNull($found);
    }

    /**
     * 5. expireStale soft deletes expired tenant, renames subdomain, and detaches subAccount.
     */
    public function test_expire_stale_soft_deletes_and_renames_subdomain(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount, 'release-subdomain');
        // Expired beyond the 10-minute safety buffer
        $tenant->update(['checkout_expires_at' => now()->subMinutes(25)]);

        $expired = $this->service()->expireStale();

        $this->assertCount(1, $expired);
        $this->assertSame($tenant->id, $expired[0]['id']);

        $refreshedTenant = Tenant::withTrashed()->find($tenant->id);
        $this->assertTrue($refreshedTenant->trashed());
        $this->assertStringContainsString('release-subdomain-exp-' . $tenant->id, $refreshedTenant->subdomain);

        $subAccount->refresh();
        $this->assertNull($subAccount->tenant_id);
    }

    /**
     * 6. expireStale does NOT touch reservation with a pending payment.
     */
    public function test_expire_stale_does_not_touch_tenant_with_pending_payment(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount, 'pending-gateway');
        $tenant->update(['checkout_expires_at' => now()->subMinutes(30)]);

        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-003',
            'status' => InvoiceStatus::UNPAID->value,
            'total' => 2000,
        ]);

        ResellerInvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => 2000,
            'method' => 'online',
            'status' => ResellerInvoicePayment::STATUS_PENDING,
        ]);

        $expired = $this->service()->expireStale();

        $this->assertEmpty($expired);
        $this->assertFalse($tenant->fresh()->trashed());
    }

    /**
     * 7. expireStale with dryRun=true reports matches without deleting.
     */
    public function test_expire_stale_dry_run_reports_without_deleting(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount, 'dryrun-subdomain');
        $tenant->update(['checkout_expires_at' => now()->subMinutes(30)]);

        $expired = $this->service()->expireStale(dryRun: true);

        $this->assertCount(1, $expired);
        $this->assertFalse($tenant->fresh()->trashed());
        $this->assertSame('dryrun-subdomain', $tenant->fresh()->subdomain);
    }

    /**
     * 8. isSettledForActivation rejects paid invoice without a settled payment row (Invariant I3).
     */
    public function test_is_settled_for_activation_rejects_paid_invoice_without_settled_payment(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-004',
            'status' => InvoiceStatus::PAID->value,
            'total' => 5000,
        ]);

        $job = new ProvisionTenantJob($tenant);
        $this->assertFalse($job->isSettledForActivation($tenant));
    }

    /**
     * 9. isSettledForActivation accepts paid invoice with settled payment.
     */
    public function test_is_settled_for_activation_accepts_paid_invoice_with_settled_payment(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        $invoice = ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-005',
            'status' => InvoiceStatus::PAID->value,
            'total' => 5000,
        ]);

        ResellerInvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => 5000,
            'method' => 'online',
            'status' => ResellerInvoicePayment::STATUS_PAID,
        ]);

        $job = new ProvisionTenantJob($tenant);
        $this->assertTrue($job->isSettledForActivation($tenant));
    }

    /**
     * 10. isSettledForActivation accepts zero-total paid invoice (free grant).
     */
    public function test_is_settled_for_activation_accepts_zero_total_paid_invoice(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        ResellerInvoice::create([
            'reseller_id' => 1,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-006',
            'status' => InvoiceStatus::PAID->value,
            'total' => 0,
        ]);

        $job = new ProvisionTenantJob($tenant);
        $this->assertTrue($job->isSettledForActivation($tenant));
    }

    /**
     * 11. isSettledForActivation accepts tenant without invoice and without order (direct registration tour).
     */
    public function test_is_settled_for_activation_accepts_tenant_without_invoice_and_without_order(): void
    {
        $user = $this->makeUser();
        $subAccount = $this->makeSubAccount($user);
        $tenant = $this->makePendingTenant($subAccount);

        $job = new ProvisionTenantJob($tenant);
        $this->assertTrue($job->isSettledForActivation($tenant));
    }

    /**
     * 12. Model accessors, scopes, and module checks reflect pending_payment status.
     */
    public function test_tenant_model_accessors_and_guards_for_pending_payment(): void
    {
        $tenant = new Tenant([
            'status' => Tenant::STATUS_PENDING_PAYMENT,
            'checkout_expires_at' => now()->subMinutes(1),
        ]);

        $this->assertTrue($tenant->isPendingPayment());
        $this->assertTrue($tenant->hasCheckoutExpired());
        $this->assertSame('در انتظار پرداخت', $tenant->status_label);
        $this->assertStringContainsString('text-blue', $tenant->status_badge);
        $this->assertSame('bg-blue-500', $tenant->status_dot_class);
        $this->assertFalse($tenant->hasModule('crm'));
        $this->assertSame([], $tenant->activeModuleSlugs());
    }

    /**
     * 13. ResellerDashboardController guards toggleStatus and renewTenant on pending tenants.
     */
    public function test_dashboard_toggle_status_and_renew_guards_on_pending_payment(): void
    {
        $user = $this->makeUser();
        $reseller = Reseller::create([
            'user_id' => $user->id,
            'name' => 'Reseller Admin',
            'status' => 'active',
        ]);

        $subAccount = $this->makeSubAccount($user, $reseller->id);
        $tenant = $this->makePendingTenant($subAccount);

        $controller = $this->getMockBuilder(ResellerDashboardController::class)
            ->onlyMethods(['getResellerOrNull', 'authorizeTenantAccess'])
            ->getMock();
        $controller->method('getResellerOrNull')->willReturn($reseller);
        $controller->method('authorizeTenantAccess')->willReturnCallback(function (): void {
        });

        // Test toggleStatus guard (expects JSON 422)
        request()->headers->set('Accept', 'application/json');
        $toggleResponse = $controller->toggleStatus($tenant);
        $this->assertSame(422, $toggleResponse->getStatusCode());
        $this->assertFalse(json_decode($toggleResponse->getContent(), true)['success']);

        // Test renewTenant guard (expects JSON 422)
        $renewRequest = new RenewTenantRequest();
        $renewRequest->headers->set('Accept', 'application/json');
        $renewResponse = $controller->renewTenant($renewRequest, $tenant);
        $this->assertSame(422, $renewResponse->getStatusCode());
        $this->assertFalse(json_decode($renewResponse->getContent(), true)['success']);
    }
}
