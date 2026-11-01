<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\PackagePriceType;
use Modules\ResellerHub\App\Http\Controllers\TenantProvisioningController;
use Modules\ResellerHub\App\Http\Requests\StoreTenantRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\TenantProvisioningService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;
use ReflectionMethod;

class NoInvoiceProvisioningTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected Reseller $reseller;
    protected ResellerPackage $freePackage;
    protected ResellerPackage $paidPackage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();

        $this->createTenantsTable();
        $this->createTenantModulesTable();
        $this->createResellersTable();
        $this->createSubAccountsTable();
        $this->createUsersTable();
        $this->createPermissionTables();
        $this->createResellerSettingsTable();
        $this->createOrdersTable();
        $this->createInvoicesTable();
        $this->createInvoiceItemsTable();
        $this->createPaymentsTable();
        $this->createSettingsTable();
        $this->createWidgetSettingsTable();
        $this->createPackagesTable();
        $this->createAllowedModulesTable();
        $this->createModulesTable();
        $this->createModulePricingsTable();

        $this->reseller = Reseller::create([
            'id' => 1,
            'user_id' => 1,
            'company_name' => 'Test Reseller Co',
            'brand_title' => 'Test Reseller',
            'status' => 'active',
        ]);

        $this->freePackage = ResellerPackage::create([
            'reseller_id' => $this->reseller->id,
            'name' => 'پلن رایگان تستی',
            'price_type' => PackagePriceType::FREE->value,
            'modules' => ['crm'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->paidPackage = ResellerPackage::create([
            'reseller_id' => $this->reseller->id,
            'name' => 'پلن طلایی اشتراکی',
            'price_type' => PackagePriceType::SUBSCRIPTION->value,
            'yearly_price' => 1200000,
            'period_prices' => ['12' => 1200000],
            'modules' => ['crm'],
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_free_package_provisioning_automatically_omits_invoice_and_creates_active_order(): void
    {
        /** @var TenantProvisioningService $service */
        $service = app(TenantProvisioningService::class);

        $tenantData = [
            'name' => 'مرکز درمانی سلامت',
            'subdomain' => 'salamat-free',
            'package_id' => $this->freePackage->id,
            'expires_in_months' => null,
            'admin_name' => 'دکتر حسینی',
            'admin_email' => 'dr@salamat.test',
            'admin_mobile' => '09121112233',
            'password' => 'Secret123456!',
            'modules' => ['crm'],
        ];

        // Even if should_issue_invoice is passed as false or true, free package must not have an invoice
        $financialData = [
            'should_issue_invoice' => false,
            'package_id' => $this->freePackage->id,
        ];

        $result = $service->provisionWithFinancials($this->reseller, $tenantData, $financialData);

        $this->assertNull($result['invoice'], 'Free package must never generate an invoice');
        $this->assertSame(0, ResellerInvoice::count());

        $tenant = $result['tenant'];
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->status);

        $order = $result['order'];
        $this->assertInstanceOf(ResellerOrder::class, $order);
        $this->assertSame(ResellerOrder::STATUS_ACTIVE, $order->status);
        $this->assertNull($order->invoice_id);
        $this->assertSame(0, (int)$order->total_amount);
        $this->assertSame(0, (int)$order->contract_value);
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    protected function createSubAccountsTable(): void
    {
        if (Schema::hasTable('reseller_sub_accounts')) {
            return;
        }

        Schema::create('reseller_sub_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('password')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('active');
            $table->json('limits')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createModulePricingsTable(): void
    {
        if (Schema::hasTable('reseller_module_pricings')) {
            return;
        }

        Schema::create('reseller_module_pricings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->string('module_slug', 100);
            $table->string('feature_key', 150)->nullable();
            $table->string('feature_title', 200)->nullable();
            $table->unsignedBigInteger('monthly_price')->default(0);
            $table->unsignedBigInteger('yearly_price')->default(0);
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    protected function createModulesTable(): void
    {
        if (Schema::hasTable('modules')) {
            return;
        }

        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->boolean('installed')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    protected function createAllowedModulesTable(): void
    {
        if (Schema::hasTable('reseller_allowed_modules')) {
            return;
        }

        Schema::create('reseller_allowed_modules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->string('module_slug', 50);
            $table->timestamps();
        });
    }

    protected function createWidgetSettingsTable(): void
    {
        if (Schema::hasTable('widget_settings')) {
            return;
        }

        Schema::create('widget_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('widget_key');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function createPackagesTable(): void
    {
        if (Schema::hasTable('reseller_packages')) {
            return;
        }

        Schema::create('reseller_packages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('cube');
            $table->string('color', 50)->default('indigo');
            $table->json('modules')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->string('price_type', 30)->default('subscription');
            $table->json('active_periods')->nullable();
            $table->json('period_prices')->nullable();
            $table->json('period_discounts')->nullable();
            $table->unsignedBigInteger('monthly_price')->nullable();
            $table->unsignedBigInteger('quarterly_price')->nullable();
            $table->unsignedBigInteger('semi_annual_price')->nullable();
            $table->unsignedBigInteger('yearly_price')->nullable();
            $table->unsignedBigInteger('one_time_price')->nullable();
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->timestamp('discount_starts_at')->nullable();
            $table->timestamp('discount_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function test_provisioning_with_invoice_disabled_creates_active_order_and_no_invoice(): void
    {
        /** @var TenantProvisioningService $service */
        $service = app(TenantProvisioningService::class);

        $tenantData = [
            'name' => 'شرکت توسعه پردازش',
            'subdomain' => 'tooseh-test',
            'package_id' => $this->paidPackage->id,
            'expires_in_months' => 12,
            'admin_name' => 'علی احمدی',
            'admin_email' => 'ali@tooseh.test',
            'admin_mobile' => '09123456789',
            'password' => 'Secret123456!',
            'modules' => ['crm'],
        ];

        $financialData = [
            'should_issue_invoice' => false,
            'package_id' => $this->paidPackage->id,
        ];

        $result = $service->provisionWithFinancials($this->reseller, $tenantData, $financialData);

        $tenant = $result['tenant'];
        $invoice = $result['invoice'];
        $order = $result['order'];

        // 1. Assert invoice is completely omitted
        $this->assertNull($invoice, 'Invoice must be null when should_issue_invoice is false');
        $this->assertSame(0, ResellerInvoice::count(), 'Database must not contain any invoices');

        // 2. Assert Tenant is active and properly provisioned
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->status);
        $this->assertNotNull($tenant->plan_expires_at);

        // 3. Assert Tenant Modules are immediately ACTIVE
        $tenantModules = TenantModule::where('tenant_id', $tenant->id)->get();
        $this->assertCount(1, $tenantModules);
        $this->assertTrue((bool)$tenantModules->first()->is_active, 'Tenant modules must be active when no invoice is required');

        // 4. Assert an active ResellerOrder was created
        $this->assertInstanceOf(ResellerOrder::class, $order);
        $this->assertSame(1, ResellerOrder::count());
        $this->assertSame(ResellerOrder::STATUS_ACTIVE, $order->status);
        $this->assertNull($order->invoice_id);
        $this->assertSame(ResellerOrder::PROVISION_DONE, $order->provisioning_status);
        $this->assertSame(1200000, (int)$order->contract_value);
        $this->assertSame(0, (int)$order->total_amount);
        $this->assertSame($tenant->id, $order->tenant_id);
    }

    public function test_provisioning_with_invoice_enabled_creates_invoice_and_pending_order(): void
    {
        /** @var TenantProvisioningService $service */
        $service = app(TenantProvisioningService::class);

        $tenantData = [
            'name' => 'شرکت طلوع فناوری',
            'subdomain' => 'toloo-test',
            'package_id' => $this->paidPackage->id,
            'expires_in_months' => 12,
            'admin_name' => 'سارا رضایی',
            'admin_email' => 'sara@toloo.test',
            'admin_mobile' => '09129876543',
            'password' => 'Secret123456!',
            'modules' => ['crm'],
        ];

        $financialData = [
            'should_issue_invoice' => true,
            'package_id' => $this->paidPackage->id,
            'invoice_number' => 'INV-TEST-PAID-01',
        ];

        $result = $service->provisionWithFinancials($this->reseller, $tenantData, $financialData);

        $tenant = $result['tenant'];
        $invoice = $result['invoice'];
        $order = $result['order'];

        // 1. Assert invoice is created with status unpaid
        $this->assertInstanceOf(ResellerInvoice::class, $invoice);
        $this->assertSame(1, ResellerInvoice::count());
        $this->assertSame(InvoiceStatus::UNPAID->value, $invoice->status);
        $this->assertSame(1200000, (int)$invoice->total);

        // 2. Assert order is created with status pending
        $this->assertInstanceOf(ResellerOrder::class, $order);
        $this->assertSame(ResellerOrder::STATUS_PENDING, $order->status);
        $this->assertSame($invoice->id, $order->invoice_id);

        // 3. Assert Tenant Modules are INACTIVE pending payment
        $tenantModules = TenantModule::where('tenant_id', $tenant->id)->get();
        $this->assertCount(1, $tenantModules);
        $this->assertFalse((bool)$tenantModules->first()->is_active, 'Tenant modules must be inactive while awaiting invoice payment');
    }

    public function test_free_package_provisioning_with_explicit_invoice_creates_zero_amount_paid_invoice(): void
    {
        /** @var TenantProvisioningService $service */
        $service = app(TenantProvisioningService::class);

        $tenantData = [
            'name' => 'مرکز درمانی سلامت ۲',
            'subdomain' => 'salamat-free-inv',
            'package_id' => $this->freePackage->id,
            'expires_in_months' => null,
            'admin_name' => 'دکتر حسینی',
            'admin_email' => 'dr2@salamat.test',
            'admin_mobile' => '09121112234',
            'password' => 'Secret123456!',
            'modules' => ['crm'],
        ];

        // User explicitly toggled invoice ON for free package
        $financialData = [
            'should_issue_invoice' => true,
            'package_id' => $this->freePackage->id,
            'invoice_number' => 'INV-FREE-001',
        ];

        $result = $service->provisionWithFinancials($this->reseller, $tenantData, $financialData);

        $invoice = $result['invoice'];
        $this->assertInstanceOf(ResellerInvoice::class, $invoice);
        $this->assertSame(1, ResellerInvoice::count());
        $this->assertSame(0, (int)$invoice->total);
        $this->assertSame(InvoiceStatus::PAID->value, $invoice->status, 'Zero amount invoice is automatically settled as paid');

        $tenant = $result['tenant'];
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->status);

        // Modules must be active since invoice is 0 and paid
        $tm = TenantModule::where('tenant_id', $tenant->id)->first();
        $this->assertTrue((bool)$tm->is_active);
    }

    public function test_store_tenant_request_defaults_issue_invoice_zero_when_omitted_for_free_package(): void
    {
        // 1. When omitted, defaults to 0
        $requestOmitted = new StoreTenantRequest();
        $requestOmitted->replace([
            'name' => 'سامانه رایگان',
            'subdomain' => 'free-crm-test',
            'package_id' => $this->freePackage->id,
            'expires_in_months' => 12,
        ]);

        $reflection = new ReflectionMethod($requestOmitted, 'prepareForValidation');
        $reflection->invoke($requestOmitted);

        $this->assertSame(0, $requestOmitted->input('issue_invoice'), 'issue_invoice must default to 0 for free packages');
        $this->assertNull($requestOmitted->input('expires_in_months'), 'expires_in_months must be null for free packages');

        // 2. When user explicitly chooses 1, it must be preserved
        $requestExplicit = new StoreTenantRequest();
        $requestExplicit->replace([
            'name' => 'سامانه رایگان با فاکتور',
            'subdomain' => 'free-crm-inv',
            'package_id' => $this->freePackage->id,
            'issue_invoice' => '1',
        ]);
        $reflection->invoke($requestExplicit);

        $this->assertSame('1', $requestExplicit->input('issue_invoice'), 'Explicit issue_invoice must not be overwritten');
    }

    public function test_controller_financial_decision_respects_toggle_and_free_package(): void
    {
        // 1. Paid package + issue_invoice = "0" (user turned off)
        $requestOff = Request::create('/reseller/tenants', 'POST', [
            'package_id' => $this->paidPackage->id,
            'issue_invoice' => '0',
        ]);
        $this->assertFalse($this->resolveControllerInvoiceDecision($requestOff));

        // 2. Paid package + issue_invoice = "1" (default / user turned on)
        $requestOn = Request::create('/reseller/tenants', 'POST', [
            'package_id' => $this->paidPackage->id,
            'issue_invoice' => '1',
        ]);
        $this->assertTrue($this->resolveControllerInvoiceDecision($requestOn));

        // 3. Free package + issue_invoice = "0" (default for free)
        $requestFreeOff = Request::create('/reseller/tenants', 'POST', [
            'package_id' => $this->freePackage->id,
            'issue_invoice' => '0',
        ]);
        $this->assertFalse($this->resolveControllerInvoiceDecision($requestFreeOff));

        // 4. Free package + issue_invoice = "1" (user opted to issue invoice for free package)
        $requestFreeOn = Request::create('/reseller/tenants', 'POST', [
            'package_id' => $this->freePackage->id,
            'issue_invoice' => '1',
        ]);
        $this->assertTrue($this->resolveControllerInvoiceDecision($requestFreeOn));

        // 5. Free package + omitted issue_invoice -> defaults to false
        $requestFreeOmitted = Request::create('/reseller/tenants', 'POST', [
            'package_id' => $this->freePackage->id,
        ]);
        $this->assertFalse($this->resolveControllerInvoiceDecision($requestFreeOmitted));
    }

    private function resolveControllerInvoiceDecision(Request $request): bool
    {
        $packageId = $request->input('package_id');
        $requestedPkg = $packageId ? ResellerPackage::find($packageId) : null;
        $isFreePackage = $requestedPkg && $requestedPkg->getPriceTypeEnum()->isFree();

        if ($request->has('issue_invoice')) {
            return $request->boolean('issue_invoice');
        } else {
            return $isFreePackage ? false : ($request->filled('package_id') || $request->filled('invoice_items'));
        }
    }
}
