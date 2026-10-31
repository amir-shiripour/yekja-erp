<?php

namespace Modules\DirectAdmin\Database\Seeders;

use Illuminate\Database\Seeder;

class DirectAdminDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DirectAdminPermissionsSeeder::class,
        ]);
    }
}
