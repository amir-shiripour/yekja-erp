<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'name')) {
            DB::statement('ALTER TABLE `tenants` MODIFY `name` VARCHAR(191) NULL DEFAULT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'name')) {
            DB::statement("ALTER TABLE `tenants` MODIFY `name` VARCHAR(191) NOT NULL DEFAULT ''");
        }
    }
};
