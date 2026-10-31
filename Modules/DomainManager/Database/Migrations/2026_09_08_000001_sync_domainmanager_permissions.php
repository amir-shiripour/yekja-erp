<?php

use Illuminate\Database\Migrations\Migration;
use Modules\DomainManager\Installer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Installer::syncModulePermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
