<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantUserManagementTest extends TestCase
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
            ['display_name' => 'مدیر سیستم']
        );

        $this->tenantA = Tenant::firstOrCreate(
            ['subdomain' => 'tenant-a-team'],
            ['name' => 'Tenant A Clinic', 'status' => 'active']
        );

        $this->tenantB = Tenant::firstOrCreate(
            ['subdomain' => 'tenant-b-team'],
            ['name' => 'Tenant B Salon', 'status' => 'active']
        );

        $this->adminA = User::firstOrCreate(
            ['email' => 'admin-a@teamtest.com'],
            [
                'name' => 'Admin A',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenantA->id,
            ]
        );
        if (!$this->adminA->hasRole('admin')) {
            $this->adminA->assignRole($adminRole);
        }

        $this->adminB = User::firstOrCreate(
            ['email' => 'admin-b@teamtest.com'],
            [
                'name' => 'Admin B',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenantB->id,
            ]
        );
        User::whereIn('email', [
            'sara.ahmadi@teamtest.com',
            'hacker@teamtest.com',
            'random.reseller@teamtest.com',
            'salon.stylist@teamtest.com',
        ])->delete();
    }

    public function test_tenant_manager_can_access_users_index_and_create(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $resIndex = $this->actingAs($this->adminA)->get(route('user.users.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('مدیریت کاربران');

        $resCreate = $this->actingAs($this->adminA)->get(route('user.users.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('ایجاد کاربر جدید');
    }

    public function test_tenant_manager_can_create_user_strictly_scoped_to_tenant(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $payload = [
            'name' => 'دکتر سارا احمدی',
            'email' => 'sara.ahmadi@teamtest.com',
            'mobile' => '09120000001',
            'password' => 'Secret123456!',
            'password_confirmation' => 'Secret123456!',
            'roles' => ['admin'],
        ];

        $res = $this->actingAs($this->adminA)->post(route('user.users.store'), $payload);
        $res->assertRedirect(route('user.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sara.ahmadi@teamtest.com',
            'tenant_id' => $this->tenantA->id,
        ]);
    }

    public function test_tenant_manager_cannot_assign_forbidden_roles(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $payload = [
            'name' => 'هکر تستی',
            'email' => 'hacker@teamtest.com',
            'password' => 'Secret123456!',
            'password_confirmation' => 'Secret123456!',
            'roles' => ['super-admin'],
        ];

        $res = $this->actingAs($this->adminA)->post(route('user.users.store'), $payload);
        $res->assertSessionHasErrors(['roles.0']);

        $this->assertDatabaseMissing('users', [
            'email' => 'hacker@teamtest.com',
        ]);
    }

    public function test_cross_tenant_isolation_blocks_editing_another_tenants_user(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        // adminA attempts to edit adminB (who belongs to tenantB)
        $res = $this->actingAs($this->adminA)->get(route('user.users.edit', $this->adminB));
        $res->assertStatus(404);
    }

    public function test_tenant_role_boundary_dynamically_blocks_new_roles_with_platform_permissions(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        // Even with a completely custom random role name, if it holds platform capability it must be blocked
        $customPlatformRole = Role::firstOrCreate(
            ['name' => 'reselllerrrrrrrrr', 'guard_name' => 'web'],
            ['display_name' => 'نماینده تستی خاص']
        );
        $customPlatformRole->syncPermissions(['resellerhub.view']);

        $resCreate = $this->actingAs($this->adminA)->get(route('user.users.create'));
        $resCreate->assertOk();
        $resCreate->assertDontSee('reselllerrrrrrrrr');
        $resCreate->assertDontSee('نماینده تستی خاص');

        $payload = [
            'name' => 'کاربر غیرمجاز نمایندگی',
            'email' => 'random.reseller@teamtest.com',
            'password' => 'Secret123456!',
            'password_confirmation' => 'Secret123456!',
            'roles' => ['reselllerrrrrrrrr'],
        ];

        $resStore = $this->actingAs($this->adminA)->post(route('user.users.store'), $payload);
        $resStore->assertSessionHasErrors(['roles.0']);

        $this->assertDatabaseMissing('users', [
            'email' => 'random.reseller@teamtest.com',
        ]);
    }

    public function test_tenant_role_boundary_dynamically_allows_new_tenant_operational_roles(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $staffRole = Role::firstOrCreate(
            ['name' => 'hairdresser_stylist', 'guard_name' => 'web'],
            ['display_name' => 'استایلیست سالن']
        );
        $staffRole->syncPermissions(['booking.view']);

        $resCreate = $this->actingAs($this->adminA)->get(route('user.users.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('hairdresser_stylist');
        $resCreate->assertSee('استایلیست سالن');

        $payload = [
            'name' => 'استایلیست جدید',
            'email' => 'salon.stylist@teamtest.com',
            'mobile' => '09129998877',
            'password' => 'Secret123456!',
            'password_confirmation' => 'Secret123456!',
            'roles' => ['hairdresser_stylist'],
        ];

        $resStore = $this->actingAs($this->adminA)->post(route('user.users.store'), $payload);
        $resStore->assertRedirect(route('user.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'salon.stylist@teamtest.com',
            'tenant_id' => $this->tenantA->id,
        ]);
    }

    public function test_tenant_manager_cannot_delete_themselves(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $res = $this->actingAs($this->adminA)->delete(route('user.users.destroy', $this->adminA));
        $res->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminA->id,
        ]);
    }

    public function test_tenant_manager_cannot_edit_or_delete_user_outside_boundary(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $resellerRole = Role::firstOrCreate(
            ['name' => 'reseller-owner', 'guard_name' => 'web'],
            ['display_name' => 'مدیر نمایندگی']
        );
        $resellerRole->syncPermissions(['resellerhub.view']);

        $resellerUser = User::firstOrCreate(
            ['email' => 'elevated@teamtest.com'],
            [
                'name' => 'Elevated User',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenantA->id,
            ]
        );
        $resellerUser->syncRoles([$resellerRole]);

        // Attempting to edit must abort 403
        $resEdit = $this->actingAs($this->adminA)->get(route('user.users.edit', $resellerUser));
        $resEdit->assertForbidden();

        // Attempting to update must abort 403
        $resUpdate = $this->actingAs($this->adminA)->put(route('user.users.update', $resellerUser), [
            'name' => 'Attempted Edit',
            'email' => 'elevated@teamtest.com',
            'roles' => ['admin'],
        ]);
        $resUpdate->assertForbidden();

        // Attempting to delete must abort 403
        $resDelete = $this->actingAs($this->adminA)->delete(route('user.users.destroy', $resellerUser));
        $resDelete->assertForbidden();
    }
}
