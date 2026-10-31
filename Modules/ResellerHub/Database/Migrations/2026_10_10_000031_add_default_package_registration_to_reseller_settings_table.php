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
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_settings', 'enable_default_package_registration')) {
                    $table->boolean('enable_default_package_registration')->default(false)->after('allow_custom_domains');
                }
                if (!Schema::hasColumn('reseller_settings', 'default_package_id')) {
                    $table->unsignedBigInteger('default_package_id')->nullable()->after('enable_default_package_registration');
                    $table->foreign('default_package_id')->references('id')->on('reseller_packages')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (Schema::hasColumn('reseller_settings', 'default_package_id')) {
                    $table->dropForeign(['default_package_id']);
                    $table->dropColumn('default_package_id');
                }
                if (Schema::hasColumn('reseller_settings', 'enable_default_package_registration')) {
                    $table->dropColumn('enable_default_package_registration');
                }
            });
        }
    }
};
