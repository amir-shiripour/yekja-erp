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
        if (Schema::hasTable('reseller_invoices')) {
            Schema::table('reseller_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_invoices', 'source_order_id')) {
                    $table->unsignedBigInteger('source_order_id')->nullable()->after('tenant_id')->index();
                }
                if (!Schema::hasColumn('reseller_invoices', 'source')) {
                    $table->string('source', 64)->nullable()->after('type')->index();
                }
            });

            // Backfill existing invoices from meta JSON
            try {
                DB::table('reseller_invoices')
                    ->whereNotNull('meta->source_order_id')
                    ->whereNull('source_order_id')
                    ->update([
                        'source_order_id' => DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.source_order_id')) AS UNSIGNED)")
                    ]);

                DB::table('reseller_invoices')
                    ->whereNotNull('meta->source')
                    ->whereNull('source')
                    ->update([
                        'source' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.source'))")
                    ]);
            } catch (Throwable $e) {
                // Ignore backfill errors on environments without JSON functions
            }
        }

        if (Schema::hasTable('reseller_orders')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_orders', 'is_custom_package')) {
                    $table->boolean('is_custom_package')->default(false)->after('tenant_id')->index();
                }
                if (!Schema::hasColumn('reseller_orders', 'source')) {
                    $table->string('source', 64)->nullable()->after('provisioning_status')->index();
                }
            });

            // Backfill existing orders from meta JSON
            try {
                DB::table('reseller_orders')
                    ->where(function ($q) {
                        $q->where('meta->is_custom', true)
                          ->orWhere('meta->is_item_addition', true);
                    })
                    ->update(['is_custom_package' => true]);

                DB::table('reseller_orders')
                    ->whereNotNull('meta->source')
                    ->whereNull('source')
                    ->update([
                        'source' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.source'))")
                    ]);
            } catch (Throwable $e) {
                // Ignore backfill errors on environments without JSON functions
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reseller_invoices')) {
            Schema::table('reseller_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('reseller_invoices', 'source_order_id')) {
                    $table->dropColumn('source_order_id');
                }
                if (Schema::hasColumn('reseller_invoices', 'source')) {
                    $table->dropColumn('source');
                }
            });
        }

        if (Schema::hasTable('reseller_orders')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                if (Schema::hasColumn('reseller_orders', 'is_custom_package')) {
                    $table->dropColumn('is_custom_package');
                }
                if (Schema::hasColumn('reseller_orders', 'source')) {
                    $table->dropColumn('source');
                }
            });
        }
    }
};
