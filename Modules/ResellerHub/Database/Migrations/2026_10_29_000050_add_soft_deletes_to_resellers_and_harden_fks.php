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
            $hasIndex = Schema::hasIndex('tenants', 'idx_tenants_reseller_status_expiry')
                || Schema::hasIndex('tenants', ['reseller_id', 'status', 'plan_expires_at']);

            if (!$hasIndex) {
                try {
                    Schema::table('tenants', function (Blueprint $table) {
                        $table->index(['reseller_id', 'status', 'plan_expires_at'], 'idx_tenants_reseller_status_expiry');
                    });
                } catch (Throwable $e) {
                    // Gracefully suppress if index was already created or duplicate key error occurs
                    if (!str_contains($e->getMessage(), 'Duplicate key name') && !str_contains($e->getMessage(), '1061')) {
                        throw $e;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasIndex('tenants', 'idx_tenants_reseller_status_expiry')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropIndex('idx_tenants_reseller_status_expiry');
            });
        }

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
