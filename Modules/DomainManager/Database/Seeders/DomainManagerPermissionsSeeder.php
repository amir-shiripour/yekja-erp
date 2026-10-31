<?php

namespace Modules\DomainManager\Database\Seeders;

use Illuminate\Database\Seeder;

class DomainManagerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Modules\DomainManager\Installer::syncModulePermissions();
    }
}
