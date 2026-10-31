<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_module_pricings', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_module_pricings', 'quarterly_price')) {
                $table->unsignedBigInteger('quarterly_price')->default(0)->after('monthly_price');
            }
            if (!Schema::hasColumn('reseller_module_pricings', 'semi_annual_price')) {
                $table->unsignedBigInteger('semi_annual_price')->default(0)->after('quarterly_price');
            }
        });

        Schema::table('reseller_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_invoices', 'parent_invoice_id')) {
                $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('reseller_invoices', 'meta')) {
                $table->json('meta')->nullable()->after('terms');
            }
        });

        Schema::table('reseller_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_orders', 'renewal_date')) {
                $table->date('renewal_date')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('reseller_orders', 'renewal_price')) {
                $table->unsignedBigInteger('renewal_price')->default(0)->after('renewal_date');
            }
            if (!Schema::hasColumn('reseller_orders', 'renewal_price_type')) {
                $table->string('renewal_price_type', 20)->default('auto')->after('renewal_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reseller_orders', function (Blueprint $table) {
            $cols = array_filter(['renewal_date', 'renewal_price', 'renewal_price_type'], fn($c) => Schema::hasColumn('reseller_orders', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('reseller_invoices', function (Blueprint $table) {
            $cols = array_filter(['parent_invoice_id', 'meta'], fn($c) => Schema::hasColumn('reseller_invoices', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('reseller_module_pricings', function (Blueprint $table) {
            $cols = array_filter(['quarterly_price', 'semi_annual_price'], fn($c) => Schema::hasColumn('reseller_module_pricings', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};