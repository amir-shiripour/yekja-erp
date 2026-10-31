<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-2: Adds an idempotency key to invoice payments so replayed requests
 * (gateway retries, double-clicks, queued job replays) can never create a
 * second settled payment row for the same logical payment.
 *
 * Unique per invoice so the same key can be replayed across invoices safely.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded: environments where the column was provisioned out-of-band
        // (e.g. hotfix applied directly to production) stay idempotent.
        if (Schema::hasColumn('reseller_invoice_payments', 'idempotency_key')) {
            return;
        }

        Schema::table('reseller_invoice_payments', function (Blueprint $table): void {
            $table->string('idempotency_key')->nullable()->after('transaction_id');
            $table->unique(['invoice_id', 'idempotency_key'], 'reseller_invoice_payments_idempotency_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('reseller_invoice_payments', 'idempotency_key')) {
            return;
        }

        Schema::table('reseller_invoice_payments', function (Blueprint $table): void {
            $table->dropUnique('reseller_invoice_payments_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
