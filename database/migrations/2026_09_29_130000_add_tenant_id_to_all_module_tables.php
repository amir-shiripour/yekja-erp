<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables requiring tenant_id for complete multi-tenant data isolation.
     */
    protected array $tables = [
        // Services Module
        'service_invoices',
        'service_orders',
        'service_invoice_payments',
        'service_invoice_items',
        'services',
        'service_categories',
        'service_packages',

        // Booking Module
        'appointments',
        'booking_services',
        'booking_categories',
        'booking_availability_rules',
        'booking_availability_exceptions',
        'booking_payments',
        'booking_settings',
        'booking_waitlists',
        'booking_service_providers',

        // Market Module
        'market_orders',
        'market_order_items',
        'market_master_products',
        'market_product_variants',
        'market_vendors',
        'market_warehouses',
        'market_brands',
        'market_categories',

        // Accounting Module
        'accounting_invoices',
        'accounting_transactions',
        'accounting_cheques',
        'accounting_documents',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
                    $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
                });
            }
        }

        // Scope invoice_number uniqueness to (tenant_id, invoice_number) instead of global
        if (Schema::hasTable('service_invoices')) {
            $this->dropIndexIfExists('service_invoices', 'service_invoices_invoice_number_unique');

            if (!$this->hasIndex('service_invoices', 'service_invoices_tenant_id_invoice_number_unique')) {
                Schema::table('service_invoices', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'invoice_number'], 'service_invoices_tenant_id_invoice_number_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_invoices') && $this->hasIndex('service_invoices', 'service_invoices_tenant_id_invoice_number_unique')) {
            Schema::table('service_invoices', function (Blueprint $table) {
                $table->dropUnique('service_invoices_tenant_id_invoice_number_unique');
            });
            Schema::table('service_invoices', function (Blueprint $table) {
                $table->unique('invoice_number', 'service_invoices_invoice_number_unique');
            });
        }

        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign([ 'tenant_id' ]);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropUnique($indexName);
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
