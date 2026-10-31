<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A private, in-memory SQLite database for ResellerHub tests.
 *
 * Deliberately *not* RefreshDatabase: these tests must never be able to touch
 * the application's real database. Each test builds only the tables it needs.
 */
trait UsesIsolatedDatabase
{
    protected function bootIsolatedDatabase(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
    }

    protected function shutdownIsolatedDatabase(): void
    {
        DB::disconnect('sqlite');
    }

    protected function createInvoicesTable(): void
    {
        if (Schema::hasTable('reseller_invoices')) {
            return;
        }

        Schema::create('reseller_invoices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->default(1);
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('source_order_id')->nullable();
            $table->unsignedBigInteger('parent_invoice_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->string('type', 20)->default('official');
            $table->string('source', 64)->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->string('sub_account_name', 150)->default('');
            $table->string('sub_account_mobile', 50)->nullable();
            $table->string('sub_account_email', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->string('discount_type', 20)->default('fixed');
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_ref', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('renewal_applied_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createPaymentsTable(): void
    {
        if (Schema::hasTable('reseller_invoice_payments')) {
            return;
        }

        Schema::create('reseller_invoice_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('method');
            $table->string('gateway')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->unique(['invoice_id', 'idempotency_key'], 'reseller_invoice_payments_idempotency_unique');
            $table->unique(['gateway', 'transaction_id'], 'reseller_invoice_payments_gateway_tx_unique');
            $table->text('notes')->nullable();
            $table->string('status')->default('paid');
            $table->timestamps();
        });
    }

    protected function createOrdersTable(): void
    {
        if (Schema::hasTable('reseller_orders')) {
            return;
        }

        Schema::create('reseller_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->default(1);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->boolean('is_custom_package')->default(false);
            $table->string('order_number', 50)->unique();
            $table->string('sub_account_name', 150)->default('');
            $table->string('sub_account_mobile', 50)->nullable();
            $table->string('sub_account_email', 150)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('billing_cycle', 20)->default('yearly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('contract_value')->default(0);
            $table->unsignedBigInteger('renewal_price')->default(0);
            $table->string('renewal_price_type', 20)->default('auto');
            $table->json('modules_snapshot')->nullable();
            $table->string('provisioning_status', 30)->default('not_provisioned');
            $table->string('source', 64)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createInvoiceItemsTable(): void
    {
        if (Schema::hasTable('reseller_invoice_items')) {
            return;
        }

        Schema::create('reseller_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('item_type', 30)->default('module');
            $table->string('item_key', 100)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_months')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    protected function createTenantsTable(): void
    {
        if (Schema::hasTable('tenants')) {
            return;
        }

        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('sub_account_id')->nullable();
            $table->string('name');
            $table->string('subdomain')->nullable();
            $table->string('custom_domain')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamp('checkout_expires_at')->nullable();
            $table->json('branding')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createTenantModulesTable(): void
    {
        if (Schema::hasTable('tenant_modules')) {
            return;
        }

        Schema::create('tenant_modules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('module_slug');
            $table->boolean('is_active')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    protected function createSettingsTable(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    protected function createResellerSettingsTable(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            return;
        }

        Schema::create('reseller_settings', function (Blueprint $table): void {
            $table->unsignedBigInteger('reseller_id')->primary();
            $table->string('currency', 10)->default('toman');
            $table->string('tax_mode', 20)->default('invoice');
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    protected function createUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    protected function createSubAccountsTable(): void
    {
        if (Schema::hasTable('reseller_sub_accounts')) {
            return;
        }

        Schema::create('reseller_sub_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('active');
            $table->json('limits')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createResellersTable(): void
    {
        if (Schema::hasTable('resellers')) {
            return;
        }

        Schema::create('resellers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('brand_title')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Minimal Spatie tables so `hasRole()` / `can()` fail closed instead of
     * throwing "no such table" during policy resolution.
     */
    protected function createPermissionTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
            });
        }

        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
            });
        }
    }
}
