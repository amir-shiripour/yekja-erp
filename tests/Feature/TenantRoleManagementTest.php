<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TenantRoleManagementTest extends TestCase
{
    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $adminA;
    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['display_name' => 'مدیر سیستم', 'tenant_id' => null]
        );

        // Core permissions
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clients.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'modules.manage', 'guard_name' => 'web']); // platform infrastructure

        $this->tenantA = Tenant::firstOrCreate(
            ['subdomain' => 'clinic-alpha-test'],
            ['name' => 'Clinic Alpha', 'status' => 'active']
        );

        $this->tenantB = Tenant::firstOrCreate(
            ['subdomain' => 'clinic-beta-test'],
            ['name' => 'Clinic Beta', 'status' => 'active']
        );

        $this->adminA = User::firstOrCreate(
            ['email' => 'admin-alpha@roletest.com'],
            [
                'name' => 'Admin Alpha',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenantA->id,
            ]
        );
        if (!$this->adminA->hasRole('admin')) {
            $this->adminA->assignRole($adminRole);
        }

        $this->adminB = User::firstOrCreate(
            ['email' => 'admin-beta@roletest.com'],
            [
                'name' => 'Admin Beta',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenantB->id,
            ]
        );
        if (!$this->adminB->hasRole('admin')) {
            $this->adminB->assignRole($adminRole);
        }

        // Clean up test roles
        Role::where('name', 'like', 't' . $this->tenantA->id . '_%')->delete();
        Role::where('name', 'like', 't' . $this->tenantB->id . '_%')->delete();
        User::whereIn('email', ['staff.alpha@roletest.com'])->delete();
        User::whereIn('mobile', ['09121112233'])->delete();
    }

    public function test_tenant_admin_can_access_roles_index_and_create(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $resIndex = $this->actingAs($this->adminA)->get(route('user.roles.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('مدیریت نقش‌ها');
        $resIndex->assertSee('افزودن نقش جدید');

        $resCreate = $this->actingAs($this->adminA)->get(route('user.roles.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('ایجاد نقش جدید');
        $resCreate->assertSee('مشخصات اصلی نقش');
    }

    public function test_tenant_admin_can_create_custom_role_scoped_to_tenant(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $payload = [
            'display_name' => 'منشی پذیرش کلینیک',
            'name' => 'receptionist',
            'permissions' => ['users.view', 'clients.view'],
        ];

        $res = $this->actingAs($this->adminA)->post(route('user.roles.store'), $payload);
        $res->assertRedirect(route('user.roles.index'));

        $this->assertDatabaseHas('roles', [
            'display_name' => 'منشی پذیرش کلینیک',
            'tenant_id' => $this->tenantA->id,
        ]);

        $customRole = Role::where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($customRole);
        $this->assertTrue($customRole->hasPermissionTo('users.view'));
        $this->assertTrue($customRole->hasPermissionTo('clients.view'));
    }

    public function test_tenant_custom_roles_are_strictly_isolated_from_other_tenants(): void
    {
        // 1. Create custom role in Tenant A
        app(TenantManager::class)->setTenant($this->tenantA);
        $roleA = Role::create([
            'name' => 't' . $this->tenantA->id . '_specialist',
            'display_name' => 'متخصص فیزیوتراپی',
            'guard_name' => 'web',
            'tenant_id' => $this->tenantA->id,
        ]);

        // 2. Tenant B accesses roles index
        app(TenantManager::class)->setTenant($this->tenantB);
        $resB = $this->actingAs($this->adminB)->get(route('user.roles.index'));
        $resB->assertOk();
        $resB->assertDontSee('متخصص فیزیوتراپی');

        // 3. Tenant B tries to edit Tenant A's role -> 403
        $editRes = $this->actingAs($this->adminB)->get(route('user.roles.edit', $roleA));
        $editRes->assertStatus(403);

        // 4. Tenant B tries to update Tenant A's role -> 403
        $updateRes = $this->actingAs($this->adminB)->put(route('user.roles.update', $roleA), [
            'display_name' => 'هک شده',
        ]);
        $updateRes->assertStatus(403);

        // 5. Tenant B tries to delete Tenant A's role -> 403
        $deleteRes = $this->actingAs($this->adminB)->delete(route('user.roles.destroy', $roleA));
        $deleteRes->assertStatus(403);
    }

    public function test_tenant_admin_cannot_edit_or_delete_system_global_roles(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);
        $systemAdminRole = Role::whereNull('tenant_id')->where('name', 'admin')->first();
        $this->assertNotNull($systemAdminRole);

        // Cannot edit system role
        $editRes = $this->actingAs($this->adminA)->get(route('user.roles.edit', $systemAdminRole));
        $editRes->assertStatus(403);

        // Cannot delete system role
        $delRes = $this->actingAs($this->adminA)->delete(route('user.roles.destroy', $systemAdminRole));
        $delRes->assertStatus(403);
    }

    public function test_platform_permissions_are_filtered_out_from_tenant_role(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $payload = [
            'display_name' => 'تلاش نفوذ',
            'name' => 'exploit-role',
            'permissions' => ['users.view', 'modules.manage'], // modules.manage should be filtered out
        ];

        $res = $this->actingAs($this->adminA)->post(route('user.roles.store'), $payload);
        $res->assertRedirect(route('user.roles.index'));

        $role = Role::where('tenant_id', $this->tenantA->id)->where('display_name', 'تلاش نفوذ')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertFalse($role->hasPermissionTo('modules.manage'));
    }

    public function test_tenant_admin_can_assign_custom_role_to_new_user(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customRole = Role::create([
            'name' => 't' . $this->tenantA->id . '_nurse',
            'display_name' => 'پرستار بخش',
            'guard_name' => 'web',
            'tenant_id' => $this->tenantA->id,
        ]);
        $customRole->givePermissionTo('users.view');

        $userPayload = [
            'name' => 'پرستار مریم',
            'email' => 'staff.alpha@roletest.com',
            'mobile' => '09121112233',
            'password' => 'PassWord123!',
            'password_confirmation' => 'PassWord123!',
            'roles' => [$customRole->name],
        ];

        $res = $this->actingAs($this->adminA)->post(route('user.users.store'), $userPayload);
        $res->assertRedirect(route('user.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'staff.alpha@roletest.com',
            'tenant_id' => $this->tenantA->id,
        ]);

        $createdUser = User::where('email', 'staff.alpha@roletest.com')->first();
        $this->assertTrue($createdUser->hasRole($customRole->name));
        $this->assertTrue($createdUser->can('users.view'));
    }
}
