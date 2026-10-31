<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('resellers') && Schema::hasColumn('resellers', 'max_tenants')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn('max_tenants');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('resellers') && !Schema::hasColumn('resellers', 'max_tenants')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->unsignedInteger('max_tenants')->default(10)->after('support_email');
            });
        }
    }
};
