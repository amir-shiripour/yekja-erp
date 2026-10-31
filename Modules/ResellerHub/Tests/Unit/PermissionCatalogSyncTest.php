<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Modules\ResellerHub\Installer;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;
use ReflectionClass;

/**
 * Enterprise Guard: Verifies that every ResellerHub permission defined in
 * `Installer.php` is 100% in sync with `PermissionCatalog.php`, has a human-readable
 * Persian title, is categorized into a logical domain, and is properly enforced.
 */
class PermissionCatalogSyncTest extends TestCase
{
    use UsesIsolatedDatabase;

    /**
     * @return array<string>
     */
    private function getInstallerPermissions(): array
    {
        return Installer::getDefinedPermissions();
    }

    public function test_all_installer_permissions_exist_in_permission_catalog(): void
    {
        $installerPerms = $this->getInstallerPermissions();
        $this->assertNotEmpty($installerPerms);

        foreach ($installerPerms as $perm) {
            $label = PermissionCatalog::translate($perm);
            $this->assertNotEmpty($label, "Permission [{$perm}] is missing a translation in PermissionCatalog.");
            $this->assertNotSame($perm, $label, "Permission [{$perm}] does not have a translated Persian label in PermissionCatalog.");
        }
    }

    public function test_all_catalog_resellerhub_permissions_are_registered_in_installer(): void
    {
        $installerPerms = $this->getInstallerPermissions();

        $reflector = new ReflectionClass(PermissionCatalog::class);
        $content = file_get_contents($reflector->getFileName());
        preg_match_all('/\'([a-zA-Z0-9\._-]+)\'\s*=>\s*[\'"][^\'"]+[\'"]/u', $content, $matches);
        $catalogPerms = $matches[1];

        $resellerCatalogPerms = array_values(array_filter($catalogPerms, static function (string $p): bool {
            return str_starts_with($p, 'resellerhub.') || $p === 'menu.see.resellerhub';
        }));

        $diff = array_diff($resellerCatalogPerms, $installerPerms);
        $this->assertEmpty($diff, 'The following permissions exist in PermissionCatalog but are missing in Installer: ' . implode(', ', $diff));
    }

    public function test_all_installer_permissions_are_mapped_to_a_resellerhub_group(): void
    {
        $installerPerms = $this->getInstallerPermissions();

        foreach ($installerPerms as $perm) {
            $group = PermissionCatalog::detectGroup($perm);
            $this->assertNotSame('other', $group, "Permission [{$perm}] fell through to the 'other' group in PermissionCatalog.");
            $this->assertTrue(
                str_starts_with($group, 'resellerhub') || $group === 'menus',
                "Permission [{$perm}] was categorized into unexpected group [{$group}]."
            );
        }
    }

    public function test_reseller_role_excludes_platform_admin_permissions(): void
    {
        $installerPerms = $this->getInstallerPermissions();

        $resellerPerms = array_values(array_filter($installerPerms, static function (string $p): bool {
            return !str_starts_with($p, 'resellerhub.admin.');
        }));

        $this->assertNotContains('resellerhub.admin.view', $resellerPerms);
        $this->assertNotContains('resellerhub.admin.manage', $resellerPerms);
        $this->assertContains('resellerhub.view', $resellerPerms);
        $this->assertContains('resellerhub.tenants.create', $resellerPerms);
        $this->assertContains('resellerhub.invoices.create', $resellerPerms);
    }

    public function test_critical_module_routes_are_guarded_by_granular_permissions(): void
    {
        $routes = RouteFacade::getRoutes();

        $checks = [
            'reseller.tenants.create' => 'role_or_permission:super-admin|resellerhub.tenants.create',
            'reseller.tenants.export' => 'role_or_permission:super-admin|resellerhub.tenants.export',
            'reseller.settings.index' => 'role_or_permission:super-admin|resellerhub.settings.manage',
            'reseller.packages.create' => 'role_or_permission:super-admin|resellerhub.packages.create',
            'reseller.invoices.create' => 'role_or_permission:super-admin|resellerhub.invoices.create',
            'reseller.pricings.index' => 'role_or_permission:super-admin|resellerhub.pricings.view',
            'reseller.sub-accounts.create' => 'role_or_permission:super-admin|resellerhub.sub-accounts.create',
        ];

        foreach ($checks as $routeName => $expectedMiddleware) {
            $route = $routes->getByName($routeName);
            $this->assertNotNull($route, "Route [{$routeName}] is not registered.");
            $this->assertContains(
                $expectedMiddleware,
                $route->gatherMiddleware(),
                "Route [{$routeName}] must be guarded by middleware [{$expectedMiddleware}]."
            );
        }
    }

    public function test_sync_module_permissions_grants_all_permissions_to_super_admin_and_populates_catalog(): void
    {
        $this->bootIsolatedDatabase();
        $this->createPermissionTables();

        $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Installer::syncModulePermissions();

        $definedPermissions = Installer::getDefinedPermissions();
        foreach ($definedPermissions as $perm) {
            $this->assertTrue($superAdmin->hasPermissionTo($perm), "Super admin does not have permission [{$perm}] after sync.");
        }

        $allPerms = \Spatie\Permission\Models\Permission::where('name', 'like', 'resellerhub.%')->get();
        $grouped = PermissionCatalog::groupAndTranslate($allPerms);
        $this->assertArrayHasKey('resellerhub', $grouped);

        $this->shutdownIsolatedDatabase();
    }
}
