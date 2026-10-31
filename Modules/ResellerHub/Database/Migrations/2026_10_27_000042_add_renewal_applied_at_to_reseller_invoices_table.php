<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the point in time a renewal (or upgrade) invoice was applied to its
 * order. Without it, every replay of `syncOrdersForInvoice()` — which runs
 * from payment callbacks, "mark as paid" and manual status changes — extended
 * the subscription by another full period.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reseller_invoices', 'renewal_applied_at')) {
            return;
        }

        Schema::table('reseller_invoices', function (Blueprint $table): void {
            $table->timestamp('renewal_applied_at')->nullable()->after('paid_at');
            $table->index(['reseller_id', 'renewal_applied_at'], 'reseller_invoices_renewal_applied_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('reseller_invoices', 'renewal_applied_at')) {
            return;
        }

        Schema::table('reseller_invoices', function (Blueprint $table): void {
            $table->dropIndex('reseller_invoices_renewal_applied_idx');
            $table->dropColumn('renewal_applied_at');
        });
    }
};
