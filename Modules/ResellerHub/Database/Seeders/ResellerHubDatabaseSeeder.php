<?php

namespace Modules\ResellerHub\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Modules\ResellerHub\Installer;

class ResellerHubDatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        Role::firstOrCreate(
            ['name' => 'reseller', 'guard_name' => $guard],
            ['display_name' => 'نماینده فروش']
        );

        Installer::syncModulePermissions();

        $this->call(ResellerWorkflowsSeeder::class);
    }
}
