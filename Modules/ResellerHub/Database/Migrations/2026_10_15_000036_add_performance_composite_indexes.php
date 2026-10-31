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
        $this->addIndexIfNotExists('reseller_invoices', 'reseller_invoices_reseller_status_idx', ['reseller_id', 'status']);
        $this->addIndexIfNotExists('reseller_invoices', 'reseller_invoices_reseller_created_idx', ['reseller_id', 'created_at']);
        $this->addIndexIfNotExists('reseller_invoices', 'reseller_invoices_tenant_status_idx', ['tenant_id', 'status']);
        $this->addIndexIfNotExists('reseller_invoices', 'reseller_invoices_reseller_type_idx', ['reseller_id', 'type']);

        $this->addIndexIfNotExists('reseller_orders', 'reseller_orders_reseller_status_idx', ['reseller_id', 'status']);
        $this->addIndexIfNotExists('reseller_orders', 'reseller_orders_reseller_created_idx', ['reseller_id', 'created_at']);
        $this->addIndexIfNotExists('reseller_orders', 'reseller_orders_tenant_status_idx', ['tenant_id', 'status']);

        $this->addIndexIfNotExists('reseller_invoice_payments', 'reseller_payments_invoice_status_idx', ['invoice_id', 'status']);

        $this->addIndexIfNotExists('reseller_invoice_items', 'reseller_invoice_items_inv_type_idx', ['invoice_id', 'item_type']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('reseller_invoices', 'reseller_invoices_reseller_status_idx');
        $this->dropIndexIfExists('reseller_invoices', 'reseller_invoices_reseller_created_idx');
        $this->dropIndexIfExists('reseller_invoices', 'reseller_invoices_tenant_status_idx');
        $this->dropIndexIfExists('reseller_invoices', 'reseller_invoices_reseller_type_idx');

        $this->dropIndexIfExists('reseller_orders', 'reseller_orders_reseller_status_idx');
        $this->dropIndexIfExists('reseller_orders', 'reseller_orders_reseller_created_idx');
        $this->dropIndexIfExists('reseller_orders', 'reseller_orders_tenant_status_idx');

        $this->dropIndexIfExists('reseller_invoice_payments', 'reseller_payments_invoice_status_idx');

        $this->dropIndexIfExists('reseller_invoice_items', 'reseller_invoice_items_inv_type_idx');
    }

    protected function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($existing)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        }
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $existing = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (!empty($existing)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }
};
