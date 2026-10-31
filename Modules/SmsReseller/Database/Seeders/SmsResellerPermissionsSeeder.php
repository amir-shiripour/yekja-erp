<?php

namespace Modules\SmsReseller\Database\Seeders;

use Illuminate\Database\Seeder;

class SmsResellerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Modules\SmsReseller\Installer::syncModulePermissions();
    }
}
