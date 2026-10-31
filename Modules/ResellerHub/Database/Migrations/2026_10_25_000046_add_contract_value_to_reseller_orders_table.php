<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `reseller_orders.total_amount` was being incremented on every renewal, which
 * turned it into "lifetime revenue" while the rest of the code (and the orders
 * list KPI) still read it as "value of this order". Both numbers are useful, so
 * they are now separated instead of overloading one column:
 *
 *   total_amount   → lifetime contracted value across all invoices (unchanged)
 *   contract_value → value of the CURRENT billing term (updated per invoice)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reseller_orders')) {
            return;
        }

        if (!Schema::hasColumn('reseller_orders', 'contract_value')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('contract_value')->default(0)->after('total_amount');
            });

            // Best-effort backfill: the current term value is the latest renewal
            // price when present, otherwise the order's total.
            DB::table('reseller_orders')->update([
                'contract_value' => DB::raw('GREATEST(COALESCE(renewal_price, 0), 0)'),
            ]);

            DB::table('reseller_orders')
                ->where('contract_value', 0)
                ->update(['contract_value' => DB::raw('COALESCE(total_amount, 0)')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_orders') && Schema::hasColumn('reseller_orders', 'contract_value')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                $table->dropColumn('contract_value');
            });
        }
    }
};
