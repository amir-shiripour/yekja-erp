<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix unique constraint on reseller_invoices (per-reseller unique invoice_number)
        if (Schema::hasTable('reseller_invoices')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM `reseller_invoices`"))->pluck('Key_name')->unique()->toArray();

            Schema::table('reseller_invoices', function (Blueprint $table) use ($existingIndexes) {
                if (in_array('reseller_invoices_invoice_number_unique', $existingIndexes, true)) {
                    $table->dropUnique('reseller_invoices_invoice_number_unique');
                }
                
                if (!in_array('reseller_invoices_reseller_invoice_unique', $existingIndexes, true)) {
                    $table->unique(['reseller_id', 'invoice_number'], 'reseller_invoices_reseller_invoice_unique');
                }
            });
        }

        // 2. Fix unique constraint on reseller_orders (per-reseller unique order_number)
        if (Schema::hasTable('reseller_orders')) {
            $existingOrderIndexes = collect(DB::select("SHOW INDEX FROM `reseller_orders`"))->pluck('Key_name')->unique()->toArray();

            Schema::table('reseller_orders', function (Blueprint $table) use ($existingOrderIndexes) {
                if (in_array('reseller_orders_order_number_unique', $existingOrderIndexes, true)) {
                    $table->dropUnique('reseller_orders_order_number_unique');
                }

                if (!in_array('reseller_orders_reseller_order_unique', $existingOrderIndexes, true)) {
                    $table->unique(['reseller_id', 'order_number'], 'reseller_orders_reseller_order_unique');
                }
            });
        }

        // 3. Clean up plaintext initial_password from reseller_sub_accounts settings
        if (Schema::hasTable('reseller_sub_accounts')) {
            $subAccounts = DB::table('reseller_sub_accounts')
                ->whereNotNull('settings')
                ->get(['id', 'settings']);

            foreach ($subAccounts as $sub) {
                $settings = json_decode($sub->settings, true);
                if (is_array($settings) && isset($settings['initial_password'])) {
                    unset($settings['initial_password']);
                    DB::table('reseller_sub_accounts')
                        ->where('id', $sub->id)
                        ->update(['settings' => json_encode($settings)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reseller_invoices')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM `reseller_invoices`"))->pluck('Key_name')->unique()->toArray();

            Schema::table('reseller_invoices', function (Blueprint $table) use ($existingIndexes) {
                if (in_array('reseller_invoices_reseller_invoice_unique', $existingIndexes, true)) {
                    $table->dropUnique('reseller_invoices_reseller_invoice_unique');
                }
                if (!in_array('reseller_invoices_invoice_number_unique', $existingIndexes, true)) {
                    $table->unique('invoice_number', 'reseller_invoices_invoice_number_unique');
                }
            });
        }

        if (Schema::hasTable('reseller_orders')) {
            $existingOrderIndexes = collect(DB::select("SHOW INDEX FROM `reseller_orders`"))->pluck('Key_name')->unique()->toArray();

            Schema::table('reseller_orders', function (Blueprint $table) use ($existingOrderIndexes) {
                if (in_array('reseller_orders_reseller_order_unique', $existingOrderIndexes, true)) {
                    $table->dropUnique('reseller_orders_reseller_order_unique');
                }
                if (!in_array('reseller_orders_order_number_unique', $existingOrderIndexes, true)) {
                    $table->unique('order_number', 'reseller_orders_order_number_unique');
                }
            });
        }
    }
};
