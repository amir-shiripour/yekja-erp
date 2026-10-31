<?php

namespace Modules\SmsReseller\Database\Seeders;

use Illuminate\Database\Seeder;

class SmsResellerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SmsResellerPermissionsSeeder::class,
        ]);
    }
}
