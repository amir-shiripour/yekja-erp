<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_sub_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_sub_accounts', 'max_invoices')) {
                $table->unsignedInteger('max_invoices')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('reseller_sub_accounts', 'max_tenants')) {
                $table->unsignedInteger('max_tenants')->nullable()->after('max_invoices');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reseller_sub_accounts', function (Blueprint $table) {
            $cols = array_filter(['max_invoices', 'max_tenants'], fn($c) => Schema::hasColumn('reseller_sub_accounts', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
