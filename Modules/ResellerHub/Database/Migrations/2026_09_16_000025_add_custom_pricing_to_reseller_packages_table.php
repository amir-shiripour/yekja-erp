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
        Schema::table('reseller_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_packages', 'custom_pricing')) {
                $table->json('custom_pricing')->nullable()->after('discount_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_packages', function (Blueprint $table) {
            if (Schema::hasColumn('reseller_packages', 'custom_pricing')) {
                $table->dropColumn('custom_pricing');
            }
        });
    }
};
