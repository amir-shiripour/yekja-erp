<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_settings') && !Schema::hasColumn('reseller_settings', 'subdomain_pattern')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $table->string('subdomain_pattern', 100)->nullable()->default('crm-{rand}')->after('allow_custom_domains');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings') && Schema::hasColumn('reseller_settings', 'subdomain_pattern')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $table->dropColumn('subdomain_pattern');
            });
        }
    }
};
