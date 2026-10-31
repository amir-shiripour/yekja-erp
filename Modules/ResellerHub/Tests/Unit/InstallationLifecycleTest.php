<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use App\Support\PermissionCatalog;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Modules\ResellerHub\App\Livewire\Dashboard\OnboardingWidget;
use Modules\ResellerHub\Installer;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * End-to-end Verification of the Module Installation Lifecycle and Zero-Crash Guarantees.
 */
class InstallationLifecycleTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootIsolatedDatabase();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    public function test_pre_install_views_do_not_throw_when_module_tables_are_missing(): void
    {
        // 1. Ensure none of the reseller tables exist
        $this->assertFalse(Schema::hasTable('resellers'));
        $this->assertFalse(Schema::hasTable('reseller_settings'));
        $this->assertFalse(Schema::hasTable('reseller_packages'));

        // 2. View Composer must not fail and should provide fallback currency
        $view = View::make('resellerhub::partials.quota-exceeded-modal');
        $rendered = $view->render();
        $this->assertIsString($rendered);

        $viewData = $view->getData();
        $this->assertSame('toman', $viewData['currency']);
        $this->assertSame('تومان', $viewData['currencyLabel']);

        // 3. Sidebar widget must not fail when unmigrated
        $sidebarView = View::make('resellerhub::partials.sidebar-subscription-widget');
        $this->assertIsString($sidebarView->render());

        // 4. OnboardingWidget must render without exception
        $widget = new OnboardingWidget();
        $renderedWidget = $widget->render()->render();
        $this->assertIsString($renderedWidget);
    }

    public function test_sync_permissions_creates_all_permissions_and_assigns_to_super_admin(): void
    {
        $this->createPermissionTables();

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        Installer::syncModulePermissions();

        $definedPermissions = Installer::getDefinedPermissions();
        $this->assertNotEmpty($definedPermissions);

        foreach ($definedPermissions as $permName) {
            $this->assertDatabaseHas('permissions', ['name' => $permName, 'guard_name' => 'web']);
            $this->assertTrue($superAdmin->hasPermissionTo($permName), "Super-admin missing permission [{$permName}]");
            $this->assertTrue($admin->hasPermissionTo($permName), "Admin missing permission [{$permName}]");
        }

        $resellerRole = Role::where('name', 'reseller')->first();
        $this->assertNotNull($resellerRole);
        $this->assertTrue($resellerRole->hasPermissionTo('resellerhub.view'));
        $this->assertFalse($resellerRole->hasPermissionTo('resellerhub.admin.view'));
    }

    public function test_permission_catalog_displays_all_resellerhub_groups_when_synced(): void
    {
        $this->createPermissionTables();
        Installer::syncModulePermissions();

        $allPerms = Permission::all();
        $catalog = PermissionCatalog::groupAndTranslate($allPerms);

        $expectedGroups = [
            'resellerhub',
            'resellerhub_admin',
            'resellerhub_tenants',
            'resellerhub_subaccounts',
            'resellerhub_packages',
            'resellerhub_invoices',
            'resellerhub_orders',
        ];

        foreach ($expectedGroups as $groupKey) {
            $this->assertArrayHasKey($groupKey, $catalog, "PermissionCatalog is missing group [{$groupKey}]");
            $this->assertNotEmpty($catalog[$groupKey]['items'], "Group [{$groupKey}] has empty items");
        }
    }

    public function test_sidebar_menu_is_visible_for_super_admin_after_permission_sync(): void
    {
        $this->createPermissionTables();
        $this->createUsersTable();

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Installer::syncModulePermissions();

        $user = User::forceCreate([
            'name' => 'Super Admin User',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($superAdminRole);
        $this->actingAs($user);

        // Ensure user can see resellerhub.view
        $this->assertTrue($user->can('resellerhub.view'));

        $menuFile = base_path('Modules/ResellerHub/resources/menu.php');
        $this->assertFileExists($menuFile);
        $menuItems = include $menuFile;

        $dashboardItem = collect($menuItems)->firstWhere('route', 'reseller.dashboard');
        $this->assertNotNull($dashboardItem);

        // 1. Permission check
        $this->assertTrue($user->can($dashboardItem['permission']));

        // 2. Show callback check
        $this->assertTrue(is_callable($dashboardItem['show']));
        $showResult = ($dashboardItem['show'])();
        $this->assertTrue($showResult, 'Dashboard menu item show closure returned false for super-admin');
    }
}
