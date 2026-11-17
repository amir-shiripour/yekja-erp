<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Tenancy\Middleware\PropagateTenantContext;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantStorage;
use App\Tenancy\Traits\TenantAwareJob;
use App\Traits\FileUploadTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantArchitectureAdvancementsTest extends TestCase
{
    protected TenantManager $tenantManager;
    protected Tenant $testTenantA;
    protected Tenant $testTenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantManager = app(TenantManager::class);
        $this->tenantManager->setTenant(null);

        // Ensure two isolated test tenants exist
        $this->testTenantA = Tenant::firstOrCreate(
            ['subdomain' => 'archadv-tenant-a'],
            [
                'name' => 'Architecture Advance Tenant A',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        $this->testTenantB = Tenant::firstOrCreate(
            ['subdomain' => 'archadv-tenant-b'],
            [
                'name' => 'Architecture Advance Tenant B',
                'status' => 'active',
                'is_active' => true,
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->tenantManager->setTenant(null);
        parent::tearDown();
    }

    // ==========================================
    // 1. Tenant Storage Isolation Tests
    // ==========================================

    public function test_storage_path_resolves_system_when_no_tenant(): void
    {
        $this->tenantManager->setTenant(null);

        $path = TenantStorage::path('avatars/user.png');
        $this->assertEquals('avatars/user.png', $path);

        $folder = TenantStorage::path('');
        $this->assertEquals('', $folder);
    }

    public function test_storage_path_resolves_tenant_prefix_when_tenant_active(): void
    {
        $this->tenantManager->setTenant($this->testTenantA);

        $path = TenantStorage::path('documents/contract.pdf');
        $expected = "tenants/{$this->testTenantA->id}/documents/contract.pdf";
        $this->assertEquals($expected, $path);

        $folder = TenantStorage::path('');
        $this->assertEquals("tenants/{$this->testTenantA->id}", $folder);
    }

    public function test_storage_path_respects_explicit_tenant_id(): void
    {
        $this->tenantManager->setTenant(null);

        $path = TenantStorage::path('invoices/inv-101.pdf', $this->testTenantB->id);
        $expected = "tenants/{$this->testTenantB->id}/invoices/inv-101.pdf";
        $this->assertEquals($expected, $path);
    }

    public function test_tenant_storage_put_get_exists_delete_operations(): void
    {
        Storage::fake('public');
        $this->tenantManager->setTenant($this->testTenantA);

        $fileRelPath = 'notes/secret.txt';
        $content = 'Tenant A Isolated Content';

        $saved = TenantStorage::put($fileRelPath, $content, 'public');
        $this->assertTrue($saved);

        $this->assertTrue(TenantStorage::exists($fileRelPath, 'public'));
        $this->assertEquals($content, TenantStorage::get($fileRelPath, 'public'));

        // Verify underlying storage path has tenant isolation prefix
        Storage::disk('public')->assertExists("tenants/{$this->testTenantA->id}/{$fileRelPath}");

        // Tenant B should NOT see it via scoped TenantStorage
        $this->tenantManager->setTenant($this->testTenantB);
        $this->assertFalse(TenantStorage::exists($fileRelPath, 'public'));

        // Delete via Tenant A
        $this->tenantManager->setTenant($this->testTenantA);
        $deleted = TenantStorage::delete($fileRelPath, 'public');
        $this->assertTrue($deleted);
        $this->assertFalse(TenantStorage::exists($fileRelPath, 'public'));
    }

    public function test_file_upload_trait_automatically_scopes_uploads_in_tenant_context(): void
    {
        Storage::fake('public');

        $uploader = new class {
            use FileUploadTrait;

            public function performUpload($file, string $dir): string
            {
                return $this->uploadFile($file, $dir, 'public');
            }
        };

        $dummyFile = UploadedFile::fake()->create('profile.txt', 50, 'text/plain');

        // Central context upload
        $this->tenantManager->setTenant(null);
        $centralPath = $uploader->performUpload($dummyFile, 'uploads');
        $this->assertStringStartsWith('uploads/', $centralPath);

        // Tenant A context upload
        $this->tenantManager->setTenant($this->testTenantA);
        $tenantPath = $uploader->performUpload($dummyFile, 'uploads');
        $this->assertStringStartsWith("tenants/{$this->testTenantA->id}/uploads/", $tenantPath);
    }

    // ==========================================
    // 2. Queue Context Propagation Tests
    // ==========================================

    public function test_propagate_tenant_context_middleware_restores_and_cleans_tenant(): void
    {
        $this->tenantManager->setTenant(null);
        $this->assertFalse($this->tenantManager->hasTenant());

        $middleware = new PropagateTenantContext($this->testTenantA->id);

        $executedInsideTenant = false;
        $capturedTenantIdInside = null;

        $middleware->handle((object)[], function () use (&$executedInsideTenant, &$capturedTenantIdInside) {
            $executedInsideTenant = $this->tenantManager->hasTenant();
            $capturedTenantIdInside = $this->tenantManager->getTenantId();
            return true;
        });

        // Inside the closure, tenant context was active
        $this->assertTrue($executedInsideTenant);
        $this->assertEquals($this->testTenantA->id, $capturedTenantIdInside);

        // Outside, context was safely reverted to null
        $this->assertFalse($this->tenantManager->hasTenant());
        $this->assertNull($this->tenantManager->getTenantId());
    }

    public function test_tenant_aware_job_captures_context_and_executes_with_middleware(): void
    {
        $this->tenantManager->setTenant($this->testTenantA);

        $job = new TestSampleTenantJob();
        $job->captureTenantContext();

        $this->assertEquals($this->testTenantA->id, $job->getTenantId());

        // Reset context to simulate background queue worker starting cold
        $this->tenantManager->setTenant(null);
        $this->assertFalse($this->tenantManager->hasTenant());

        // Run job through its middleware
        $middlewareList = $job->middleware();
        $this->assertNotEmpty($middlewareList);

        $executedTenantId = null;
        $middlewareList[0]->handle($job, function ($j) use (&$executedTenantId) {
            $executedTenantId = app(TenantManager::class)->getTenantId();
            $j->handle();
        });

        $this->assertEquals($this->testTenantA->id, $executedTenantId);
        $this->assertTrue($job->handled);

        // Worker context left clean
        $this->assertNull($this->tenantManager->getTenantId());
    }

    public function test_zero_touch_queue_payload_and_event_lifecycle(): void
    {
        // 1. In active tenant request, payload creation includes tenant_id
        $this->tenantManager->setTenant($this->testTenantA);

        $reflector = new \ReflectionClass(\Illuminate\Queue\Queue::class);
        $callbacks = $reflector->getStaticPropertyValue('createPayloadCallbacks');

        $hasTenantPayloadCallback = false;
        foreach ($callbacks as $callback) {
            $data = $callback('sync', 'default', []);
            if (isset($data['tenant_id']) && $data['tenant_id'] === $this->testTenantA->id) {
                $hasTenantPayloadCallback = true;
                break;
            }
        }
        $this->assertTrue($hasTenantPayloadCallback, 'Queue payload callback captures tenant_id');

        // 2. Simulate worker receiving JobProcessing event with payload
        $this->tenantManager->setTenant(null);

        $fakeJob = new class($this->testTenantA->id) {
            public function __construct(private int $tId) {}
            public function payload(): array {
                return ['tenant_id' => $this->tId];
            }
        };

        // Fire JobProcessing
        event(new JobProcessing('sync', $fakeJob));
        $this->assertTrue($this->tenantManager->hasTenant());
        $this->assertEquals($this->testTenantA->id, $this->tenantManager->getTenantId());

        // Fire JobProcessed
        event(new JobProcessed('sync', $fakeJob));
        $this->assertFalse($this->tenantManager->hasTenant());
        $this->assertNull($this->tenantManager->getTenantId());
    }

    // ==========================================
    // 3. Tenant Cache Scoping Tests
    // ==========================================

    public function test_cache_prefix_resolves_system_when_no_tenant(): void
    {
        $this->tenantManager->setTenant(null);
        $key = TenantCache::prefixKey('company_name');
        $this->assertEquals('system:company_name', $key);
    }

    public function test_cache_prefix_resolves_tenant_when_tenant_active(): void
    {
        $this->tenantManager->setTenant($this->testTenantA);
        $key = TenantCache::prefixKey('crm_config');
        $this->assertEquals("tenant:{$this->testTenantA->id}:crm_config", $key);
    }

    public function test_cache_isolation_between_tenants_and_system(): void
    {
        // Store under Tenant A
        $this->tenantManager->setTenant($this->testTenantA);
        TenantCache::put('daily_metric', 'MetricA', 60);

        // Store under Tenant B with identical key
        $this->tenantManager->setTenant($this->testTenantB);
        TenantCache::put('daily_metric', 'MetricB', 60);

        // Verify Tenant A gets MetricA
        $this->tenantManager->setTenant($this->testTenantA);
        $this->assertEquals('MetricA', TenantCache::get('daily_metric'));

        // Verify Tenant B gets MetricB
        $this->tenantManager->setTenant($this->testTenantB);
        $this->assertEquals('MetricB', TenantCache::get('daily_metric'));

        // Verify Central System gets null or system value
        $this->tenantManager->setTenant(null);
        $this->assertNull(TenantCache::get('daily_metric'));

        TenantCache::put('daily_metric', 'MetricSystem', 60);
        $this->assertEquals('MetricSystem', TenantCache::get('daily_metric'));

        // Re-verify Tenant A is unaffected by System write
        $this->tenantManager->setTenant($this->testTenantA);
        $this->assertEquals('MetricA', TenantCache::get('daily_metric'));
    }

    public function test_cache_remember_and_forget(): void
    {
        $this->tenantManager->setTenant($this->testTenantA);

        $callCount = 0;
        $val1 = TenantCache::remember('lazy_calc', 60, function () use (&$callCount) {
            $callCount++;
            return 42;
        });

        $this->assertEquals(42, $val1);
        $this->assertEquals(1, $callCount);

        // Second call should come from cache
        $val2 = TenantCache::remember('lazy_calc', 60, function () use (&$callCount) {
            $callCount++;
            return 999;
        });

        $this->assertEquals(42, $val2);
        $this->assertEquals(1, $callCount);

        // Forget
        TenantCache::forget('lazy_calc');
        $this->assertFalse(TenantCache::has('lazy_calc'));
    }

    public function test_global_helper_functions_and_manager_accessors(): void
    {
        $this->tenantManager->setTenant($this->testTenantA);

        // Helper tenant_storage()
        $path = tenant_storage('export.csv');
        $this->assertEquals("tenants/{$this->testTenantA->id}/export.csv", $path);

        // Helper tenant_cache()
        tenant_cache()->put('helper_test', 'works', 60);
        $this->assertEquals('works', tenant_cache('helper_test'));

        // TenantManager shortcuts
        $this->assertInstanceOf(TenantStorage::class, $this->tenantManager->storage());
        $this->assertInstanceOf(TenantCache::class, $this->tenantManager->cache());
    }
}

// Helper stub class for testing TenantAwareJob
class TestSampleTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, TenantAwareJob;

    public bool $handled = false;

    public function handle(): void
    {
        $this->handled = true;
    }
}
