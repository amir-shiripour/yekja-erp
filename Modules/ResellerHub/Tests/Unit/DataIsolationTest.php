<?php

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Policies\ResellerInvoicePolicy;
use Modules\ResellerHub\App\Policies\ResellerOrderPolicy;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Data-isolation and authorisation regression tests.
 *
 * These lock in the P0 fixes: a reseller must never reach another reseller's
 * money, and the old mobile-number ownership bypass must stay dead. If someone
 * re-adds `$user->mobile === $invoice->sub_account_mobile`, the suite goes red.
 *
 * Uses a private in-memory SQLite connection (never the real database), the
 * same pattern as Phase2FinancialTest.
 */
class DataIsolationTest extends TestCase
{
    private ResellerInvoicePolicy $invoicePolicy;

    private ResellerOrderPolicy $orderPolicy;

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

        $this->createSchema();

        Event::fake();

        $this->invoicePolicy = new ResellerInvoicePolicy;
        $this->orderPolicy = new ResellerOrderPolicy;
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('source_order_id')->nullable();
            $table->unsignedBigInteger('parent_invoice_id')->nullable();
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
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('method')->nullable();
            $table->string('status')->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reseller_sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('order_number', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->unsignedBigInteger('sub_account_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Spatie permission tables. Empty permissions means `can()` fails
        // closed, which is what the deny cases rely on.
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'کاربر ' . uniqid('', true),
            'email' => uniqid('', true) . '@example.test',
            'mobile' => '0912' . mt_rand(1000000, 9999999),
        ], $attributes));
    }

    private function reseller(User $owner, string $status = 'active'): Reseller
    {
        return Reseller::create([
            'user_id' => $owner->id,
            'company_name' => 'نمایندگی ' . uniqid('', true),
            'status' => $status,
        ]);
    }

    private function grantRole(User $user, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id');

        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $user->unsetRelation('roles');
    }

    private function invoice(int $resellerId, array $attributes = []): ResellerInvoice
    {
        return ResellerInvoice::create(array_merge([
            'reseller_id' => $resellerId,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => 'unpaid',
            'subtotal' => 1_000_000,
            'total' => 1_000_000,
            'paid_amount' => 0,
        ], $attributes));
    }

    // ---------------------------------------------------------------------
    // Invoice isolation
    // ---------------------------------------------------------------------

    public function test_a_reseller_can_view_its_own_invoice(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);
        $invoice = $this->invoice($reseller->id);

        $this->assertTrue($this->invoicePolicy->view($owner, $invoice));
    }

    public function test_a_reseller_cannot_view_another_resellers_invoice(): void
    {
        $attacker = $this->user();
        $this->reseller($attacker);

        $victimOwner = $this->user();
        $victimReseller = $this->reseller($victimOwner);
        $victimInvoice = $this->invoice($victimReseller->id);

        $this->assertFalse(
            $this->invoicePolicy->view($attacker, $victimInvoice),
            'Cross-reseller invoice access must be denied.'
        );
        $this->assertFalse($this->invoicePolicy->isResellerOwnerOrSuperAdmin($attacker, $victimInvoice));
        $this->assertFalse($this->invoicePolicy->update($attacker, $victimInvoice));
        $this->assertFalse($this->invoicePolicy->cancel($attacker, $victimInvoice));
        $this->assertFalse($this->invoicePolicy->pay($attacker, $victimInvoice));
    }

    /**
     * The exact bypass that was removed: matching the invoice's editable
     * `sub_account_mobile` against the logged-in user's mobile used to grant
     * full access. It must never come back.
     */
    public function test_matching_mobile_number_grants_no_access(): void
    {
        $sharedMobile = '09121234567';

        $attacker = $this->user(['mobile' => $sharedMobile]);
        $this->reseller($attacker);

        $victimOwner = $this->user();
        $victimReseller = $this->reseller($victimOwner);
        $victimInvoice = $this->invoice($victimReseller->id, [
            'sub_account_mobile' => $sharedMobile,
            'sub_account_email' => $attacker->email,
            'sub_account_name' => $attacker->name,
        ]);

        $this->assertFalse(
            $this->invoicePolicy->view($attacker, $victimInvoice),
            'A mobile/email match is attacker-editable data and must not prove ownership.'
        );
    }

    /**
     * The second half of the same defect, and the more dangerous half.
     *
     * `getSubAccountModel()` builds its fallback as
     * `->where(function ($q) { ... })`, but when the invoice carries neither an
     * email nor a mobile the closure adds no condition at all. Laravel drops an
     * empty nested group from the SQL, so the query degrades to
     * `where reseller_id = ?` and `first()` returns an arbitrary sub-account of
     * that reseller.
     *
     * The result feeds `customerUser()`, and `customerUser()` is what the wallet
     * adapter debits and refunds. So the consequence is not a wrong name on a
     * screen — it is money moving to the wrong person.
     */
    public function test_an_invoice_with_no_identity_data_resolves_no_sub_account(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);

        $bystander = $this->user();
        ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'user_id' => $bystander->id,
            'name' => 'bystander',
            'email' => 'bystander@example.test',
            'mobile' => '09120000001',
            'status' => 'active',
        ]);

        $invoice = $this->invoice($reseller->id, [
            'sub_account_name' => null,
            'sub_account_email' => null,
            'sub_account_mobile' => null,
            'meta' => null,
        ]);

        $this->assertNull(
            $invoice->getSubAccountModel(),
            'An invoice that identifies nobody must not resolve to somebody.'
        );

        $this->assertNull(
            $invoice->customerUser(),
            'and must therefore never name a wallet to charge or refund.'
        );
    }

    /**
     * A shared phone number is common in family or small-business accounts. When
     * the invoice does carry a mobile, the match must still be exact and must
     * not silently pick whichever sub-account sorts first.
     */
    public function test_a_shared_mobile_resolves_only_the_matching_sub_account(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);

        $firstUser = $this->user();
        ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'user_id' => $firstUser->id,
            'name' => 'first',
            'email' => 'first@example.test',
            'mobile' => '09120000001',
            'status' => 'active',
        ]);

        $secondUser = $this->user();
        $second = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'user_id' => $secondUser->id,
            'name' => 'second',
            'email' => 'second@example.test',
            'mobile' => '09120000002',
            'status' => 'active',
        ]);

        $invoice = $this->invoice($reseller->id, [
            'sub_account_email' => null,
            'sub_account_mobile' => '09120000002',
            'meta' => null,
        ]);

        $this->assertSame(
            $second->id,
            $invoice->getSubAccountModel()?->id,
            'Only the sub-account that actually owns this mobile may be resolved.'
        );
    }

    public function test_a_suspended_reseller_cannot_view_its_own_invoice(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner, 'suspended');
        $invoice = $this->invoice($reseller->id);

        $this->assertFalse($this->invoicePolicy->view($owner, $invoice));
        $this->assertFalse($this->invoicePolicy->create($owner));
    }

    public function test_a_super_admin_can_view_any_invoice(): void
    {
        $admin = $this->user();
        $this->grantRole($admin, 'super-admin');

        $owner = $this->user();
        $reseller = $this->reseller($owner);
        $invoice = $this->invoice($reseller->id);

        $this->assertTrue($this->invoicePolicy->view($admin, $invoice));
        $this->assertTrue($this->invoicePolicy->isResellerOwnerOrSuperAdmin($admin, $invoice));
    }

    public function test_a_tenant_user_can_view_only_its_own_tenants_invoice(): void
    {
        $tenantUser = $this->user(['tenant_id' => 501]);
        $otherTenantUser = $this->user(['tenant_id' => 502]);

        $owner = $this->user();
        $reseller = $this->reseller($owner);

        $ownInvoice = $this->invoice($reseller->id, ['tenant_id' => 501]);
        $foreignInvoice = $this->invoice($reseller->id, ['tenant_id' => 502]);

        $this->assertTrue($this->invoicePolicy->view($tenantUser, $ownInvoice));
        $this->assertFalse($this->invoicePolicy->view($otherTenantUser, $ownInvoice));
        $this->assertFalse($this->invoicePolicy->view($tenantUser, $foreignInvoice));
    }

    public function test_a_tenant_user_cannot_cancel_an_invoice_it_can_only_view(): void
    {
        $tenantUser = $this->user(['tenant_id' => 501]);
        $owner = $this->user();
        $reseller = $this->reseller($owner);
        $invoice = $this->invoice($reseller->id, ['tenant_id' => 501]);

        $this->assertTrue($this->invoicePolicy->view($tenantUser, $invoice));
        $this->assertFalse(
            $this->invoicePolicy->cancel($tenantUser, $invoice),
            'Viewing an invoice must not imply the right to cancel it.'
        );
    }

    // ---------------------------------------------------------------------
    // Financial state guards
    // ---------------------------------------------------------------------

    public function test_a_paid_invoice_cannot_be_updated_cancelled_or_deleted(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);
        $invoice = $this->invoice($reseller->id, [
            'status' => 'paid',
            'paid_amount' => 1_000_000,
            'paid_at' => now(),
        ]);

        $this->assertFalse($this->invoicePolicy->update($owner, $invoice));
        $this->assertFalse($this->invoicePolicy->cancel($owner, $invoice));
        $this->assertFalse($this->invoicePolicy->delete($owner, $invoice));
        $this->assertFalse($this->invoicePolicy->pay($owner, $invoice));
        $this->assertTrue($this->invoicePolicy->view($owner, $invoice));
    }

    public function test_a_cancelled_invoice_cannot_be_paid_again(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);
        $invoice = $this->invoice($reseller->id, ['status' => 'cancelled']);

        $this->assertFalse($this->invoicePolicy->pay($owner, $invoice));
        $this->assertFalse($this->invoicePolicy->update($owner, $invoice));
    }

    public function test_cancelling_a_payment_from_another_invoice_is_denied(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);

        $invoice = $this->invoice($reseller->id);
        $otherInvoice = $this->invoice($reseller->id);

        $foreignPayment = ResellerInvoicePayment::create([
            'invoice_id' => $otherInvoice->id,
            'amount' => 100_000,
            'method' => 'manual',
            'status' => 'paid',
        ]);

        $this->assertFalse(
            $this->invoicePolicy->cancelPayment($owner, $invoice, $foreignPayment),
            'A payment must only be cancellable through the invoice it belongs to.'
        );
    }

    // ---------------------------------------------------------------------
    // Order isolation
    // ---------------------------------------------------------------------

    public function test_order_view_respects_the_reseller_boundary(): void
    {
        $ownerA = $this->user();
        $resellerA = $this->reseller($ownerA);

        $ownerB = $this->user();
        $resellerB = $this->reseller($ownerB);

        $orderB = ResellerOrder::create([
            'reseller_id' => $resellerB->id,
            'order_number' => 'ORD-' . uniqid('', true),
            'status' => 'pending',
        ]);

        $this->assertFalse($this->orderPolicy->view($ownerA, $orderB));
        $this->assertTrue($this->orderPolicy->view($ownerB, $orderB));
    }

    public function test_a_suspended_reseller_cannot_view_its_own_order(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner, 'suspended');

        $order = ResellerOrder::create([
            'reseller_id' => $reseller->id,
            'order_number' => 'ORD-' . uniqid('', true),
            'status' => 'pending',
        ]);

        $this->assertFalse($this->orderPolicy->view($owner, $order));
    }
}
