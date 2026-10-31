<?php

namespace Modules\DirectAdmin\Database\Seeders;

use Illuminate\Database\Seeder;

class DirectAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Modules\DirectAdmin\Installer::syncModulePermissions();
    }
}
