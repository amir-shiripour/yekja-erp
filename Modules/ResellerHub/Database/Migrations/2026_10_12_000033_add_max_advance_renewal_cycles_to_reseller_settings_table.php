<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_settings') && !Schema::hasColumn('reseller_settings', 'max_advance_renewal_cycles')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $table->unsignedInteger('max_advance_renewal_cycles')->nullable()->default(1)->after('default_plan_duration_months');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings') && Schema::hasColumn('reseller_settings', 'max_advance_renewal_cycles')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $table->dropColumn('max_advance_renewal_cycles');
            });
        }
    }
};
