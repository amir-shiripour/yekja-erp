<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to normalize legacy order and invoice statuses.
     */
    public function up(): void
    {
        if (Schema::hasTable('reseller_invoices')) {
            DB::table('reseller_invoices')
                ->where('status', 'canceled')
                ->update(['status' => 'cancelled']);

            DB::table('reseller_invoices')
                ->where('status', 'pending')
                ->update(['status' => 'unpaid']);
        }

        if (Schema::hasTable('reseller_orders')) {
            DB::table('reseller_orders')
                ->where('status', 'canceled')
                ->update(['status' => 'cancelled']);

            DB::table('reseller_orders')
                ->where('status', 'completed')
                ->update(['status' => 'active']);

            DB::table('reseller_orders')
                ->where('status', 'suspended')
                ->update(['status' => 'inactive']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: normalized statuses are fully compatible with both legacy and new representations.
    }
};
