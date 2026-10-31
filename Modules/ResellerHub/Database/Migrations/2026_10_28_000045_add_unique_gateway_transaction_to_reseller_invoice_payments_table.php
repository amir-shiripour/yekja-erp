<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P1-3: Security Hardening - Cross-Invoice Replay & Double Spending Prevention.
 *
 * Adds a unique index on [gateway, transaction_id] across reseller_invoice_payments.
 * Ensures no two settled payments can ever share the same bank reference on the
 * same gateway across different invoices.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reseller_invoice_payments')) {
            return;
        }

        // 1. Sanitize any legacy rows where transaction_id was saved as empty string
        // so they don't violate the unique constraint in MySQL.
        DB::table('reseller_invoice_payments')
            ->where('transaction_id', '')
            ->update(['transaction_id' => null]);

        try {
            Schema::table('reseller_invoice_payments', function (Blueprint $table): void {
                $table->unique(['gateway', 'transaction_id'], 'reseller_invoice_payments_gateway_tx_unique');
            });
        } catch (\Throwable $e) {
            // Index may already exist
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('reseller_invoice_payments')) {
            return;
        }

        try {
            Schema::table('reseller_invoice_payments', function (Blueprint $table): void {
                $table->dropUnique('reseller_invoice_payments_gateway_tx_unique');
            });
        } catch (\Throwable $e) {
            // Index may not exist
        }
    }
};
