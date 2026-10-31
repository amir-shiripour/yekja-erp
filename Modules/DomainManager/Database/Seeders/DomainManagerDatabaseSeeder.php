<?php

namespace Modules\DomainManager\Database\Seeders;

use Illuminate\Database\Seeder;

class DomainManagerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DomainManagerPermissionsSeeder::class,
        ]);
    }
}
