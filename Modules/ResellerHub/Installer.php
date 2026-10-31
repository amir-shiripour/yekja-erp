<?php

namespace Modules\ResellerHub;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'ResellerHub';

    protected array $tables = [
        'reseller_invoice_payments',
        'reseller_invoice_items',
        'reseller_invoices',
        'reseller_orders',
        'reseller_module_pricings',
        'reseller_packages',
        'reseller_sub_accounts',
        'reseller_allowed_modules',
        'reseller_settings',
        'resellers',
        'tenant_onboarding_states',
    ];

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    public function install(): void
    {
        parent::install();
        Log::info('ResellerHub Installer: Starting install process...');
        $this->syncPermissions();
        Log::info('ResellerHub Installer: Install process finished.');
    }

    public function enable(): void
    {
        parent::enable();
        $this->syncPermissions();
    }

    public function reset(): void
    {
        parent::reset();
        $this->syncPermissions();
    }

    public static function syncModulePermissions(): void
    {
        (new self())->syncPermissions();
    }

    /**
     * @return array<string>
     */
    public static function getDefinedPermissions(): array
    {
        return [
            'menu.see.resellerhub',
            'resellerhub.view',
            'resellerhub.manage',
            'resellerhub.provision',
            'resellerhub.branding',
            'resellerhub.settings.manage',
            'resellerhub.tenants.view',
            'resellerhub.tenants.create',
            'resellerhub.tenants.edit',
            'resellerhub.tenants.domain',
            'resellerhub.tenants.toggle-status',
            'resellerhub.tenants.renew',
            'resellerhub.tenants.login-as',
            'resellerhub.tenants.export',
            'resellerhub.tenants.export_sql',
            'resellerhub.sub-accounts.view',
            'resellerhub.sub-accounts.create',
            'resellerhub.sub-accounts.edit',
            'resellerhub.sub-accounts.toggle-status',
            'resellerhub.packages.view',
            'resellerhub.packages.create',
            'resellerhub.packages.edit',
            'resellerhub.packages.delete',
            'resellerhub.pricings.view',
            'resellerhub.pricings.manage',
            'resellerhub.invoices.view',
            'resellerhub.invoices.create',
            'resellerhub.invoices.delete',
            'resellerhub.invoices.print',
            'resellerhub.invoices.payments',
            'resellerhub.orders.view',
            'resellerhub.orders.manage',
            'resellerhub.orders.upgrade',
            'resellerhub.admin.view',
            'resellerhub.admin.manage',
        ];
    }

    public function syncPermissions(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $definedPermissions = self::getDefinedPermissions();

        foreach ($definedPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', $guard)->first()
            ?? Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($definedPermissions);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', $guard)->first()
            ?? Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($definedPermissions);
        }

        $resellerPermissions = array_values(array_filter($definedPermissions, function ($p) {
            return !str_starts_with($p, 'resellerhub.admin.');
        }));

        // 1. Reseller Role (Legacy/Default)
        $resellerRole = Role::firstOrCreate(
            ['name' => 'reseller', 'guard_name' => $guard],
            ['display_name' => 'نماینده فروش']
        );
        $resellerRole->givePermissionTo($resellerPermissions);

        // 2. Reseller Owner (Full Tenant & Reseller Scope)
        $ownerRole = Role::firstOrCreate(
            ['name' => 'reseller-owner', 'guard_name' => $guard],
            ['display_name' => 'مدیر ارشد نمایندگی']
        );
        $ownerRole->syncPermissions($resellerPermissions);

        // 3. Reseller Billing (Financial Scope)
        $billingRole = Role::firstOrCreate(
            ['name' => 'reseller-billing', 'guard_name' => $guard],
            ['display_name' => 'امور مالی نمایندگی']
        );
        $billingRole->syncPermissions([
            'menu.see.resellerhub',
            'resellerhub.view',
            'resellerhub.invoices.view',
            'resellerhub.invoices.create',
            'resellerhub.invoices.print',
            'resellerhub.invoices.payments',
            'resellerhub.orders.view',
        ]);

        // 4. Reseller Support (View-Only / Read-Only Support Scope)
        $supportRole = Role::firstOrCreate(
            ['name' => 'reseller-support', 'guard_name' => $guard],
            ['display_name' => 'پشتیبانی نمایندگی']
        );
        $supportRole->syncPermissions([
            'menu.see.resellerhub',
            'resellerhub.view',
            'resellerhub.tenants.view',
            'resellerhub.sub-accounts.view',
            'resellerhub.packages.view',
            'resellerhub.pricings.view',
            'resellerhub.invoices.view',
            'resellerhub.orders.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
