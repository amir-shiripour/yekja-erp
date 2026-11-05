<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SoftDeletes for Reseller (TEN-01)
        if (Schema::hasTable('resellers') && !Schema::hasColumn('resellers', 'deleted_at')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. Explicit renewal order tracking (TEN-07)
        if (Schema::hasTable('reseller_orders') && !Schema::hasColumn('reseller_orders', 'renewal_of_order_id')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('reseller_invoices') && !Schema::hasColumn('reseller_invoices', 'renewal_of_order_id')) {
            Schema::table('reseller_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
            });
        }

        // 3. Payment expiration tracking (OPS-04)
        if (Schema::hasTable('reseller_invoice_payments') && !Schema::hasColumn('reseller_invoice_payments', 'expires_at')) {
            Schema::table('reseller_invoice_payments', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->index();
            });
        }

        // 4. Missing composite index on tenants (TEN-05)
        if (Schema::hasTable('tenants')) {
            $connection = Schema::getConnection();
            $dbName = $connection->getDatabaseName();
            $exists = DB::select(
                "SELECT COUNT(1) AS cnt FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$dbName, 'tenants', 'idx_tenants_reseller_status_expiry']
            );

            if (empty($exists) || (int)$exists[0]->cnt === 0) {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->index(['reseller_id', 'status', 'plan_expires_at'], 'idx_tenants_reseller_status_expiry');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('resellers') && Schema::hasColumn('resellers', 'deleted_at')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('reseller_orders') && Schema::hasColumn('reseller_orders', 'renewal_of_order_id')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                $table->dropColumn('renewal_of_order_id');
            });
        }

        if (Schema::hasTable('reseller_invoices') && Schema::hasColumn('reseller_invoices', 'renewal_of_order_id')) {
            Schema::table('reseller_invoices', function (Blueprint $table) {
                $table->dropColumn('renewal_of_order_id');
            });
        }

        if (Schema::hasTable('reseller_invoice_payments') && Schema::hasColumn('reseller_invoice_payments', 'expires_at')) {
            Schema::table('reseller_invoice_payments', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
