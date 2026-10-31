<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenancyModelRegistry;
use App\Tenancy\TenantManager;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * Enterprise Guard: Tests Zero-Touch Dynamic Tenancy.
 *
 * Verifies that any model from any module can be dynamically scoped and guarded
 * without modifying its PHP source code on disk.
 */
class ZeroTouchTenancyTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TenancyModelRegistry::flushBound();
        $this->bootIsolatedDatabase();

        // Create temporary table for dynamic test model
        Schema::create('dummy_booking_services', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        TenancyModelRegistry::flushBound();
        Schema::dropIfExists('dummy_booking_services');
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    public function test_default_models_catalog_covers_key_crm_modules(): void
    {
        $catalog = TenancyModelRegistry::defaultModels();

        $this->assertContains('Modules\Booking\Entities\BookingService', $catalog);
        $this->assertContains('Modules\Clients\Entities\Client', $catalog);
        $this->assertContains('Modules\Services\App\Http\Models\Service', $catalog);
        $this->assertContains('Modules\Market\App\Models\Order', $catalog);
        $this->assertContains('Modules\Accounting\Entities\Invoice', $catalog);
    }

    public function test_dynamic_binding_attaches_tenant_scope_without_model_traits(): void
    {
        // Define clean dummy model representing an untouched external module model
        $modelClass = DummyExternalModel::class;

        // Bind tenancy dynamically
        $this->assertTrue(TenancyModelRegistry::bind($modelClass));

        // Mock TenantManager with an active tenant
        $tenantManager = $this->app->make(TenantManager::class);
        $tenant = new Tenant(['id' => 42, 'name' => 'Acme Corp']);
        $tenant->id = 42;
        $tenantManager->setTenant($tenant);

        // Verify Global Scope was applied to query builder
        $query = $modelClass::query();
        $this->assertStringContainsString('"tenant_id" = ?', $query->toSql());
        $this->assertContains(42, $query->getBindings());
    }

    public function test_creating_automatically_populates_tenant_id_in_active_context(): void
    {
        $modelClass = DummyExternalModel::class;
        TenancyModelRegistry::bind($modelClass);

        $tenantManager = $this->app->make(TenantManager::class);
        $tenant = new Tenant(['id' => 77, 'name' => 'Test Tenant']);
        $tenant->id = 77;
        $tenantManager->setTenant($tenant);

        // Create record without explicitly passing tenant_id
        $record = $modelClass::create(['name' => 'Consultation Service']);

        $this->assertSame(77, (int) $record->tenant_id);

        // Verify in DB directly
        $raw = DB::table('dummy_booking_services')->where('id', $record->id)->first();
        $this->assertSame(77, (int) $raw->tenant_id);
    }

    public function test_anti_tampering_blocks_cross_tenant_update(): void
    {
        $modelClass = DummyExternalModel::class;
        TenancyModelRegistry::bind($modelClass);

        $tenantManager = $this->app->make(TenantManager::class);
        $tenant = new Tenant(['id' => 10, 'name' => 'Tenant 10']);
        $tenant->id = 10;
        $tenantManager->setTenant($tenant);

        $record = $modelClass::create(['name' => 'Original Record']);

        // Now attempt cross-tenant tampering
        $record->tenant_id = 999;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cross-tenant data tampering detected');

        $record->save();
    }

    public function test_health_report_generates_accurate_overview(): void
    {
        $report = TenancyModelRegistry::getHealthReport();

        $this->assertIsArray($report);
        $this->assertNotEmpty($report);

        // Every entry should have required schema keys
        foreach ($report as $entry) {
            $this->assertArrayHasKey('model', $entry);
            $this->assertArrayHasKey('module', $entry);
            $this->assertArrayHasKey('table', $entry);
            $this->assertArrayHasKey('protection', $entry);
            $this->assertArrayHasKey('status', $entry);
        }
    }

    public function test_registry_can_dynamically_add_and_remove_models(): void
    {
        $dummyClass = DummyExternalModel::class;

        // Add model
        $added = TenancyModelRegistry::addModel($dummyClass);
        $this->assertTrue($added);
        $this->assertContains($dummyClass, TenancyModelRegistry::defaultModels());

        // Remove model
        $removed = TenancyModelRegistry::removeModel($dummyClass);
        $this->assertTrue($removed);
        $this->assertNotContains($dummyClass, TenancyModelRegistry::defaultModels());
    }

    public function test_system_models_are_strictly_protected_from_deletion(): void
    {
        $systemModel = 'Modules\Booking\Entities\BookingService';

        $this->assertTrue(TenancyModelRegistry::isSystemModel($systemModel));

        // Attempting to remove system model must return false and fail
        $removed = TenancyModelRegistry::removeModel($systemModel);
        $this->assertFalse($removed);
        $this->assertContains($systemModel, TenancyModelRegistry::defaultModels());
    }

    public function test_discover_available_models_scans_modules(): void
    {
        $discovered = TenancyModelRegistry::discoverAvailableModels();

        $this->assertIsArray($discovered);
        // Each discovered item should have class, model, module, module_label, label
        foreach ($discovered as $item) {
            $this->assertArrayHasKey('class', $item);
            $this->assertArrayHasKey('model', $item);
            $this->assertArrayHasKey('module', $item);
            $this->assertArrayHasKey('module_label', $item);
            $this->assertArrayHasKey('label', $item);
            // Must NOT contain already registered system models
            $this->assertFalse(TenancyModelRegistry::isSystemModel($item['class']));
        }
    }

    public function test_get_module_label_resolves_dynamically_from_resolver_or_catalog(): void
    {
        $this->assertSame('نوبت‌دهی', TenancyModelRegistry::getModuleLabel('Booking'));
        $this->assertSame('حسابداری', TenancyModelRegistry::getModuleLabel('Accounting'));
        $this->assertSame('مشتریان', TenancyModelRegistry::getModuleLabel('Clients'));
        $this->assertSame('فروشگاه', TenancyModelRegistry::getModuleLabel('Market'));
        $this->assertSame('سرویس و خدمات', TenancyModelRegistry::getModuleLabel('Services'));
    }

    public function test_get_entity_label_resolves_dynamically_without_static_arrays(): void
    {
        // Test key entities across CRM modules
        $this->assertSame('سرویس‌ها', TenancyModelRegistry::getEntityLabel('BookingService', 'Booking'));
        $this->assertSame('نوبت‌ها', TenancyModelRegistry::getEntityLabel('Appointment', 'Booking'));
        $this->assertSame('مشتریان', TenancyModelRegistry::getEntityLabel('Client', 'Clients'));
        $this->assertSame('فرم ساز مشتریان', TenancyModelRegistry::getEntityLabel('ClientForm', 'Clients'));
        $this->assertSame('اقلام و سطرهای فاکتورها و پیش‌فاکتورها', TenancyModelRegistry::getEntityLabel('InvoiceItem', 'Accounting'));
        $this->assertSame('فاکتورها و پیش‌فاکتورها', TenancyModelRegistry::getEntityLabel('Invoice', 'Accounting'));
        $this->assertSame('محصولات و برندها', TenancyModelRegistry::getEntityLabel('MasterProduct', 'Market'));
        $this->assertSame('انبارها', TenancyModelRegistry::getEntityLabel('Warehouse', 'Market'));
        $this->assertSame('فروشندگان', TenancyModelRegistry::getEntityLabel('Vendor', 'Market'));
    }
}

/**
 * Concrete Dummy Model simulating a model in another module that has NOT been edited
 */
class DummyExternalModel extends Model
{
    protected $table = 'dummy_booking_services';
    protected $fillable = ['name']; // Intentionally does NOT have tenant_id in fillable!
}
