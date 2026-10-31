<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Compound indexes ensure sub-millisecond query performance even with millions of records.
     */
    public function up(): void
    {
        $this->addCompoundIndex('service_invoices', ['tenant_id', 'created_at'], 'idx_srv_inv_tenant_created');
        $this->addCompoundIndex('service_invoices', ['tenant_id', 'status_id'], 'idx_srv_inv_tenant_status');
        $this->addCompoundIndex('service_invoices', ['tenant_id', 'customer_id'], 'idx_srv_inv_tenant_customer');

        $this->addCompoundIndex('service_orders', ['tenant_id', 'created_at'], 'idx_srv_ord_tenant_created');
        $this->addCompoundIndex('service_orders', ['tenant_id', 'status_id'], 'idx_srv_ord_tenant_status');

        $this->addCompoundIndex('clients', ['tenant_id', 'created_at'], 'idx_clients_tenant_created');
        $this->addCompoundIndex('clients', ['tenant_id', 'status_id'], 'idx_clients_tenant_status');

        $this->addCompoundIndex('appointments', ['tenant_id', 'created_at'], 'idx_appts_tenant_created');

        $this->addCompoundIndex('market_orders', ['tenant_id', 'created_at'], 'idx_mkt_ord_tenant_created');

        $this->addCompoundIndex('accounting_invoices', ['tenant_id', 'created_at'], 'idx_acc_inv_tenant_created');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('service_invoices', 'idx_srv_inv_tenant_created');
        $this->dropIndexIfExists('service_invoices', 'idx_srv_inv_tenant_status');
        $this->dropIndexIfExists('service_invoices', 'idx_srv_inv_tenant_customer');

        $this->dropIndexIfExists('service_orders', 'idx_srv_ord_tenant_created');
        $this->dropIndexIfExists('service_orders', 'idx_srv_ord_tenant_status');

        $this->dropIndexIfExists('clients', 'idx_clients_tenant_created');
        $this->dropIndexIfExists('clients', 'idx_clients_tenant_status');

        $this->dropIndexIfExists('appointments', 'idx_appts_tenant_created');

        $this->dropIndexIfExists('market_orders', 'idx_mkt_ord_tenant_created');

        $this->dropIndexIfExists('accounting_invoices', 'idx_acc_inv_tenant_created');
    }

    private function addCompoundIndex(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }

        if (!$this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (Schema::hasTable($table) && $this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        $exists = DB::select(
            "SELECT COUNT(1) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        return !empty($exists) && (int)$exists[0]->cnt > 0;
    }
};
