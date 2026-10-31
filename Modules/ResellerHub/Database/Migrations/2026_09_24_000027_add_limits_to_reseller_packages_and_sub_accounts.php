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
            if (!Schema::hasColumn('reseller_packages', 'limits')) {
                $table->json('limits')->nullable()->after('features');
            }
        });

        Schema::table('reseller_sub_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_sub_accounts', 'limits')) {
                $table->json('limits')->nullable()->after('settings');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_packages', function (Blueprint $table) {
            if (Schema::hasColumn('reseller_packages', 'limits')) {
                $table->dropColumn('limits');
            }
        });

        Schema::table('reseller_sub_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('reseller_sub_accounts', 'limits')) {
                $table->dropColumn('limits');
            }
        });
    }
};
