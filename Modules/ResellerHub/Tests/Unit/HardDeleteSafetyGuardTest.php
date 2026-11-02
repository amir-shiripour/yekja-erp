<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Http\Controllers\ResellerSubAccountController;
use Modules\ResellerHub\App\Http\Controllers\TenantProvisioningController;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\TenantDeletionService;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;
use Spatie\Permission\Models\Role;

class HardDeleteSafetyGuardTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected TenantDeletionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createUsersTable();
        $this->createTenantsTable();
        $this->createSubAccountsTable();
        $this->createTenantModulesTable();
        $this->createResellersTable();
        $this->createPermissionTables();

        $this->service = app(TenantDeletionService::class);
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    private function createReseller(): Reseller
    {
        return Reseller::create([
            'id' => 1,
            'user_id' => 1,
            'company_name' => 'Test Reseller Co',
            'status' => 'active',
        ]);
    }

    public function test_hard_delete_tenant_completely_removes_tenant_and_cascades_modules_and_users(): void
    {
        $reseller = $this->createReseller();

        $tenant = Tenant::create([
            'reseller_id' => $reseller->id,
            'name' => 'Clinic Alpha',
            'subdomain' => 'alpha',
            'status' => 'active',
        ]);
        $tenantId = (int)$tenant->id;

        TenantModule::create([
            'tenant_id' => $tenantId,
            'module_slug' => 'crm',
            'is_active' => true,
        ]);

        $tenantUser = User::forceCreate([
            'name' => 'Clinic Admin',
            'email' => 'admin@alpha.test',
            'tenant_id' => $tenantId,
        ]);

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'tenant_id' => $tenantId,
            'name' => 'Alpha Org',
            'status' => 'active',
        ]);

        $this->service->hardDeleteTenant($tenant);

        // 1. Tenant is permanently force-deleted
        $this->assertNull(Tenant::find($tenantId));
        $this->assertNull(Tenant::withTrashed()->find($tenantId));

        // 2. Tenant modules are purged
        $this->assertSame(0, TenantModule::where('tenant_id', $tenantId)->count());

        // 3. Tenant users are purged
        $this->assertNull(User::find($tenantUser->id));

        // 4. SubAccount reference to tenant is nullified
        $subAccount->refresh();
        $this->assertNull($subAccount->tenant_id);
    }

    public function test_hard_delete_sub_account_without_tenants_succeeds_directly(): void
    {
        $reseller = $this->createReseller();

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'name' => 'Standalone Buyer',
            'status' => 'active',
        ]);
        $subAccountId = (int)$subAccount->id;

        $result = $this->service->hardDeleteSubAccount($subAccount, false);

        $this->assertSame($subAccountId, $result['deleted_sub_account_id']);
        $this->assertSame(0, $result['deleted_tenants_count']);
        $this->assertNull(ResellerSubAccount::find($subAccountId));
        $this->assertNull(ResellerSubAccount::withTrashed()->find($subAccountId));
    }

    public function test_hard_delete_sub_account_with_connected_tenants_throws_exception_when_not_forced(): void
    {
        $reseller = $this->createReseller();

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'name' => 'Parent Buyer',
            'status' => 'active',
        ]);
        $subAccountId = (int)$subAccount->id;

        $tenant1 = Tenant::create([
            'reseller_id' => $reseller->id,
            'sub_account_id' => $subAccountId,
            'name' => 'System One',
            'subdomain' => 'sys1',
            'status' => 'active',
        ]);
        $tenantId = (int)$tenant1->id;

        $this->expectException(ResellerHubException::class);
        $this->service->hardDeleteSubAccount($subAccount, false);

        // Neither record should be deleted
        $this->assertNotNull(ResellerSubAccount::find($subAccountId));
        $this->assertNotNull(Tenant::find($tenantId));
    }

    public function test_hard_delete_sub_account_with_force_flag_cascades_and_hard_deletes_all_connected_tenants(): void
    {
        $reseller = $this->createReseller();

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'name' => 'Multi-system Owner',
            'status' => 'active',
        ]);
        $subAccountId = (int)$subAccount->id;

        $tenant1 = Tenant::create([
            'reseller_id' => $reseller->id,
            'sub_account_id' => $subAccountId,
            'name' => 'CRM Branch 1',
            'subdomain' => 'crm1',
            'status' => 'active',
        ]);
        $tenant1Id = (int)$tenant1->id;

        $tenant2 = Tenant::create([
            'reseller_id' => $reseller->id,
            'sub_account_id' => $subAccountId,
            'name' => 'CRM Branch 2',
            'subdomain' => 'crm2',
            'status' => 'active',
        ]);
        $tenant2Id = (int)$tenant2->id;

        $result = $this->service->hardDeleteSubAccount($subAccount, true);

        $this->assertSame($subAccountId, $result['deleted_sub_account_id']);
        $this->assertSame(2, $result['deleted_tenants_count']);

        // SubAccount is permanently force deleted
        $this->assertNull(ResellerSubAccount::find($subAccountId));
        $this->assertNull(ResellerSubAccount::withTrashed()->find($subAccountId));

        // Both tenants are permanently force deleted
        $this->assertNull(Tenant::find($tenant1Id));
        $this->assertNull(Tenant::withTrashed()->find($tenant1Id));
        $this->assertNull(Tenant::find($tenant2Id));
        $this->assertNull(Tenant::withTrashed()->find($tenant2Id));
    }

    public function test_controller_sub_account_destroy_returns_409_when_tenants_exist_without_force_flag(): void
    {
        $reseller = $this->createReseller();
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($superAdminRole);
        $this->actingAs($admin);

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'name' => 'Guarded Buyer',
            'status' => 'active',
        ]);
        $subAccountId = (int)$subAccount->id;

        Tenant::create([
            'reseller_id' => $reseller->id,
            'sub_account_id' => $subAccountId,
            'name' => 'Guarded Tenant',
            'subdomain' => 'guarded',
            'status' => 'active',
        ]);

        $controller = app(ResellerSubAccountController::class);
        $request = Request::create("/reseller/sub-accounts/{$subAccountId}", 'DELETE');
        $request->headers->set('Accept', 'application/json');

        $response = $controller->destroy($request, $subAccount, $this->service);

        $this->assertSame(409, $response->getStatusCode());
        $payload = json_decode((string)$response->getContent(), true);

        $this->assertFalse($payload['success']);
        $this->assertTrue($payload['has_connected_tenants']);
        $this->assertSame(1, $payload['connected_tenants_count']);
        $this->assertCount(1, $payload['connected_tenants']);
        $this->assertSame('guarded', $payload['connected_tenants'][0]['subdomain']);
        $this->assertSame(
            __('resellerhub::messages.subaccounts.delete_conflict_has_tenants', ['count' => '۱']),
            $payload['message']
        );
    }

    public function test_controller_sub_account_destroy_with_force_flag_succeeds(): void
    {
        $reseller = $this->createReseller();
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'admin2@admin.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($superAdminRole);
        $this->actingAs($admin);

        $subAccount = ResellerSubAccount::create([
            'reseller_id' => $reseller->id,
            'name' => 'Cascade Buyer',
            'status' => 'active',
        ]);
        $subAccountId = (int)$subAccount->id;

        $tenant = Tenant::create([
            'reseller_id' => $reseller->id,
            'sub_account_id' => $subAccountId,
            'name' => 'Cascade Tenant',
            'subdomain' => 'cascade',
            'status' => 'active',
        ]);
        $tenantId = (int)$tenant->id;

        $controller = app(ResellerSubAccountController::class);
        $request = Request::create("/reseller/sub-accounts/{$subAccountId}", 'DELETE', ['force_delete_tenants' => 1]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->destroy($request, $subAccount, $this->service);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string)$response->getContent(), true);

        $this->assertTrue($payload['success']);
        $this->assertSame($subAccountId, $payload['deleted_sub_account_id']);
        $this->assertSame(1, $payload['deleted_tenants_count']);
        $this->assertSame(
            __('resellerhub::messages.subaccounts.deleted_with_tenants_success', ['name' => 'Cascade Buyer', 'count' => '۱']),
            $payload['message']
        );

        $this->assertNull(ResellerSubAccount::find($subAccountId));
        $this->assertNull(Tenant::find($tenantId));
    }

    public function test_controller_tenant_destroy_succeeds_for_authorized_user(): void
    {
        $reseller = $this->createReseller();
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'admin3@admin.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($superAdminRole);
        $this->actingAs($admin);

        $tenant = Tenant::create([
            'reseller_id' => $reseller->id,
            'name' => 'Independent Tenant',
            'subdomain' => 'independent',
            'status' => 'active',
        ]);
        $tenantId = (int)$tenant->id;

        $controller = app(TenantProvisioningController::class);
        $request = Request::create("/reseller/tenants/{$tenantId}", 'DELETE');
        $request->headers->set('Accept', 'application/json');

        $response = $controller->destroy($request, $tenant, $this->service);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string)$response->getContent(), true);

        $this->assertTrue($payload['success']);
        $this->assertSame($tenantId, $payload['deleted_tenant_id']);
        $this->assertSame(
            __('resellerhub::messages.tenants.deleted_success', ['name' => 'Independent Tenant']),
            $payload['message']
        );
        $this->assertNull(Tenant::find($tenantId));
    }
}
