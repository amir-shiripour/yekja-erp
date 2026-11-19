<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $permission = Permission::firstOrCreate([
            'name'       => 'content.themes.manage',
            'guard_name' => 'web',
        ]);

        Role::whereIn('name', ['super-admin', 'admin'])
            ->whereNull('tenant_id')
            ->get()
            ->each(function (Role $role) use ($permission): void {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Non-destructive: keep permission intact on rollback
    }
};
