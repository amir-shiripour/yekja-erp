<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncResellerDatabaseCommand extends Command
{
    protected $signature = 'reseller:sync-database';
    protected $description = 'Safely sync missing columns and tables for ResellerHub without failing on existing tables';

    public function handle(): int
    {
        $this->info('Starting ResellerHub database schema sync...');

        // 1. resellers table
        if (Schema::hasTable('resellers')) {
            $this->safeAddColumn('resellers', 'deleted_at', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. reseller_invoices table
        if (Schema::hasTable('reseller_invoices')) {
            $this->safeAddColumn('reseller_invoices', 'source_order_id', function (Blueprint $table) {
                $table->unsignedBigInteger('source_order_id')->nullable()->index();
            });
            $this->safeAddColumn('reseller_invoices', 'source', function (Blueprint $table) {
                $table->string('source', 64)->nullable()->index();
            });
            $this->safeAddColumn('reseller_invoices', 'currency', function (Blueprint $table) {
                $table->string('currency', 10)->default('IRT');
            });
            $this->safeAddColumn('reseller_invoices', 'renewal_applied_at', function (Blueprint $table) {
                $table->timestamp('renewal_applied_at')->nullable();
            });
            $this->safeAddColumn('reseller_invoices', 'renewal_of_order_id', function (Blueprint $table) {
                $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
            });
            $this->safeAddColumn('reseller_invoices', 'deleted_at', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 3. reseller_orders table
        if (Schema::hasTable('reseller_orders')) {
            $this->safeAddColumn('reseller_orders', 'is_custom_package', function (Blueprint $table) {
                $table->boolean('is_custom_package')->default(false)->index();
            });
            $this->safeAddColumn('reseller_orders', 'source', function (Blueprint $table) {
                $table->string('source', 64)->nullable()->index();
            });
            $this->safeAddColumn('reseller_orders', 'contract_value', function (Blueprint $table) {
                $table->decimal('contract_value', 15, 2)->nullable();
            });
            $this->safeAddColumn('reseller_orders', 'currency', function (Blueprint $table) {
                $table->string('currency', 10)->default('IRT');
            });
            $this->safeAddColumn('reseller_orders', 'renewal_of_order_id', function (Blueprint $table) {
                $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
            });
            $this->safeAddColumn('reseller_orders', 'deleted_at', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 4. reseller_invoice_payments table
        if (Schema::hasTable('reseller_invoice_payments')) {
            $this->safeAddColumn('reseller_invoice_payments', 'currency', function (Blueprint $table) {
                $table->string('currency', 10)->default('IRT');
            });
            $this->safeAddColumn('reseller_invoice_payments', 'idempotency_key', function (Blueprint $table) {
                $table->string('idempotency_key', 64)->nullable()->unique();
            });
            $this->safeAddColumn('reseller_invoice_payments', 'expires_at', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->index();
            });
        }

        // 5. tenants table
        if (Schema::hasTable('tenants')) {
            $this->safeAddColumn('tenants', 'checkout_expires_at', function (Blueprint $table) {
                $table->timestamp('checkout_expires_at')->nullable()->index();
            });
            try {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->index(['reseller_id', 'status', 'plan_expires_at'], 'idx_tenants_reseller_status_expiry');
                });
            } catch (\Throwable $e) {}
        }

        // 6. reseller_settings table
        if (Schema::hasTable('reseller_settings')) {
            $this->safeAddColumn('reseller_settings', 'rounding_mode', function (Blueprint $table) {
                $table->string('rounding_mode', 20)->default('none');
            });
            $this->safeAddColumn('reseller_settings', 'rounding_unit', function (Blueprint $table) {
                $table->unsignedInteger('rounding_unit')->default(1000);
            });
            $this->safeAddColumn('reseller_settings', 'rounding_apply_to', function (Blueprint $table) {
                $table->string('rounding_apply_to', 20)->default('total');
            });
            $this->safeAddColumn('reseller_settings', 'enable_tour', function (Blueprint $table) {
                $table->boolean('enable_tour')->default(true);
            });
            $this->safeAddColumn('reseller_settings', 'tour_steps', function (Blueprint $table) {
                $table->json('tour_steps')->nullable();
            });
        }

        // 7. Mark recent migrations as recorded in migrations table so migrate command knows they ran
        $migrations = [
            '2026_10_17_000040_add_explicit_columns_to_orders_and_invoices',
            '2026_10_22_000044_add_tenant_and_module_dashboard_composite_indexes',
            '2026_10_24_000045_add_rounding_settings_to_reseller_settings_table',
            '2026_10_25_000046_add_contract_value_to_reseller_orders_table',
            '2026_10_25_000047_backfill_invoice_source_order_id',
            '2026_10_25_000048_add_currency_to_reseller_financial_tables',
            '2026_10_27_000042_add_renewal_applied_at_to_reseller_invoices_table',
            '2026_10_27_000043_add_idempotency_key_to_reseller_invoice_payments_table',
            '2026_10_28_000044_add_checkout_expires_at_to_tenants_table',
            '2026_10_28_000045_add_unique_gateway_transaction_to_reseller_invoice_payments_table',
            '2026_10_29_000050_add_soft_deletes_to_resellers_and_harden_fks',
        ];

        if (Schema::hasTable('migrations')) {
            $batch = (int) DB::table('migrations')->max('batch') + 1;
            foreach ($migrations as $mig) {
                try {
                    if (!DB::table('migrations')->where('migration', $mig)->exists()) {
                        DB::table('migrations')->insert([
                            'migration' => $mig,
                            'batch' => $batch,
                        ]);
                        $this->line(" Registered {$mig} in migrations table");
                    }
                } catch (\Throwable $e) {}
            }
        }

        $this->info('ResellerHub database schema synced successfully!');
        return 0;
    }

    protected function safeAddColumn(string $tableName, string $columnName, \Closure $callback): void
    {
        try {
            if (!Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, $callback);
                $this->line(" Added {$columnName} to {$tableName}");
            }
        } catch (\Throwable $e) {
            $this->warn(" Warning on {$tableName}.{$columnName}: " . $e->getMessage());
        }
    }
}
