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
            Schema::table('resellers', function (Blueprint $table) {
                if (!Schema::hasColumn('resellers', 'deleted_at')) {
                    $table->softDeletes();
                    $this->line(' Added deleted_at to resellers');
                }
            });
        }

        // 2. reseller_invoices table
        if (Schema::hasTable('reseller_invoices')) {
            Schema::table('reseller_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_invoices', 'source_order_id')) {
                    $table->unsignedBigInteger('source_order_id')->nullable()->after('tenant_id')->index();
                    $this->line(' Added source_order_id to reseller_invoices');
                }
                if (!Schema::hasColumn('reseller_invoices', 'source')) {
                    $table->string('source', 64)->nullable()->after('type')->index();
                    $this->line(' Added source to reseller_invoices');
                }
                if (!Schema::hasColumn('reseller_invoices', 'currency')) {
                    $table->string('currency', 10)->default('IRT')->after('amount');
                    $this->line(' Added currency to reseller_invoices');
                }
                if (!Schema::hasColumn('reseller_invoices', 'renewal_applied_at')) {
                    $table->timestamp('renewal_applied_at')->nullable()->after('due_date');
                    $this->line(' Added renewal_applied_at to reseller_invoices');
                }
                if (!Schema::hasColumn('reseller_invoices', 'renewal_of_order_id')) {
                    $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
                    $this->line(' Added renewal_of_order_id to reseller_invoices');
                }
                if (!Schema::hasColumn('reseller_invoices', 'deleted_at')) {
                    $table->softDeletes();
                    $this->line(' Added deleted_at to reseller_invoices');
                }
            });
        }

        // 3. reseller_orders table
        if (Schema::hasTable('reseller_orders')) {
            Schema::table('reseller_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_orders', 'is_custom_package')) {
                    $table->boolean('is_custom_package')->default(false)->after('tenant_id')->index();
                    $this->line(' Added is_custom_package to reseller_orders');
                }
                if (!Schema::hasColumn('reseller_orders', 'source')) {
                    $table->string('source', 64)->nullable()->after('provisioning_status')->index();
                    $this->line(' Added source to reseller_orders');
                }
                if (!Schema::hasColumn('reseller_orders', 'contract_value')) {
                    $table->decimal('contract_value', 15, 2)->nullable()->after('total_amount');
                    $this->line(' Added contract_value to reseller_orders');
                }
                if (!Schema::hasColumn('reseller_orders', 'currency')) {
                    $table->string('currency', 10)->default('IRT')->after('total_amount');
                    $this->line(' Added currency to reseller_orders');
                }
                if (!Schema::hasColumn('reseller_orders', 'renewal_of_order_id')) {
                    $table->unsignedBigInteger('renewal_of_order_id')->nullable()->index();
                    $this->line(' Added renewal_of_order_id to reseller_orders');
                }
                if (!Schema::hasColumn('reseller_orders', 'deleted_at')) {
                    $table->softDeletes();
                    $this->line(' Added deleted_at to reseller_orders');
                }
            });
        }

        // 4. reseller_invoice_payments table
        if (Schema::hasTable('reseller_invoice_payments')) {
            Schema::table('reseller_invoice_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_invoice_payments', 'currency')) {
                    $table->string('currency', 10)->default('IRT')->after('amount');
                    $this->line(' Added currency to reseller_invoice_payments');
                }
                if (!Schema::hasColumn('reseller_invoice_payments', 'idempotency_key')) {
                    $table->string('idempotency_key', 64)->nullable()->unique()->after('status');
                    $this->line(' Added idempotency_key to reseller_invoice_payments');
                }
                if (!Schema::hasColumn('reseller_invoice_payments', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->index()->after('paid_at');
                    $this->line(' Added expires_at to reseller_invoice_payments');
                }
            });
        }

        // 5. tenants table
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'checkout_expires_at')) {
                    $table->timestamp('checkout_expires_at')->nullable()->after('plan_expires_at')->index();
                    $this->line(' Added checkout_expires_at to tenants');
                }
                try {
                    $table->index(['reseller_id', 'status', 'plan_expires_at'], 'idx_tenants_reseller_status_expiry');
                } catch (\Throwable $e) {}
            });
        }

        // 6. reseller_settings table
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_settings', 'rounding_mode')) {
                    $table->string('rounding_mode', 20)->default('none');
                    $this->line(' Added rounding_mode to reseller_settings');
                }
                if (!Schema::hasColumn('reseller_settings', 'rounding_unit')) {
                    $table->unsignedInteger('rounding_unit')->default(1000);
                    $this->line(' Added rounding_unit to reseller_settings');
                }
                if (!Schema::hasColumn('reseller_settings', 'rounding_apply_to')) {
                    $table->string('rounding_apply_to', 20)->default('total');
                    $this->line(' Added rounding_apply_to to reseller_settings');
                }
                if (!Schema::hasColumn('reseller_settings', 'enable_tour')) {
                    $table->boolean('enable_tour')->default(true);
                    $this->line(' Added enable_tour to reseller_settings');
                }
                if (!Schema::hasColumn('reseller_settings', 'tour_steps')) {
                    $table->json('tour_steps')->nullable();
                    $this->line(' Added tour_steps to reseller_settings');
                }
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
                if (!DB::table('migrations')->where('migration', $mig)->exists()) {
                    DB::table('migrations')->insert([
                        'migration' => $mig,
                        'batch' => $batch,
                    ]);
                    $this->line(" Registered {$mig} in migrations table");
                }
            }
        }

        $this->info('ResellerHub database schema synced successfully!');
        return 0;
    }
}
