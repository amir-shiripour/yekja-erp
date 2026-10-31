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
        // Composite indexes for tenants table to accelerate dashboard aggregate stats & expiring list
        $this->addIndexIfNotExists('tenants', 'tenants_reseller_status_idx', ['reseller_id', 'status']);
        $this->addIndexIfNotExists('tenants', 'tenants_reseller_plan_expires_idx', ['reseller_id', 'plan_expires_at']);

        // Composite indexes for tenant_modules table to accelerate module adoption aggregation
        $this->addIndexIfNotExists('tenant_modules', 'tenant_modules_tenant_active_idx', ['tenant_id', 'is_active']);
        $this->addIndexIfNotExists('tenant_modules', 'tenant_modules_active_slug_idx', ['is_active', 'module_slug']);

        // Composite index for reseller invoices ordering by issue date under status
        $this->addIndexIfNotExists('reseller_invoices', 'reseller_invoices_reseller_status_issue_idx', ['reseller_id', 'status', 'issue_date']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('tenants', 'tenants_reseller_status_idx');
        $this->dropIndexIfExists('tenants', 'tenants_reseller_plan_expires_idx');

        $this->dropIndexIfExists('tenant_modules', 'tenant_modules_tenant_active_idx');
        $this->dropIndexIfExists('tenant_modules', 'tenant_modules_active_slug_idx');

        $this->dropIndexIfExists('reseller_invoices', 'reseller_invoices_reseller_status_issue_idx');
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
