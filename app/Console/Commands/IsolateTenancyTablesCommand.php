<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IsolateTenancyTablesCommand extends Command
{
    protected $signature = 'tenancy:isolate-tables';

    protected $description = 'Enforce multi-tenant isolation by ensuring all module tables have tenant_id column, foreign keys, and indexes';

    protected array $tables = [
        // Core & Clients
        'clients',
        'client_terms_acceptances',
        'client_calls',
        'treatment_plans',
        'treatment_plan_snapshots',
        'treatment_plan_workflow_bindings',
        'doctor_profiles',
        'doctor_media',
        'booking_audit_logs',
        'booking_day_locks',
        'booking_laboratory_daily_logs',
        'booking_laboratory_orders',
        'booking_laboratory_stages',
        'booking_slot_holds',
        'booking_slot_locks',
        'booking_statements',

        // Accounting
        'accounting_categories',
        'accounting_cheque_attachments',
        'accounting_cheques',
        'accounting_documents',
        'accounting_fund_accounts',
        'accounting_invoice_items',
        'accounting_invoice_payments',
        'accounting_invoices',
        'accounting_proforma_items',
        'accounting_proformas',
        'accounting_settings',
        'accounting_source_documents',
        'accounting_transactions',

        // Projects
        'projects',
        'projects_activities',
        'projects_categories',
        'projects_checklist_comments',
        'projects_checklist_item_users',
        'projects_checklist_items',
        'projects_documents',
        'projects_members',
        'projects_messages',
        'projects_phases',
        'projects_roles',
        'projects_settings',
        'projects_statuses',
        'projects_task_comments',
        'projects_tasks',
        'projects_templates',
        'projects_time_logs',

        // Tasks & Reminders & Calendar
        'tasks',
        'reminders',
        'reminder_snooze_logs',
        'calendar_events',

        // Sales & CRM
        'sales_calls',
        'sales_campaign_contacts',
        'sales_campaign_results',
        'sales_campaigns',
        'sales_deals',
        'sales_follow_ups',
        'sales_loss_reasons',
        'sales_pipelines',
        'sales_stages',

        // Services & Invoices
        'services',
        'service_categories',
        'services_custom_fields',
        'services_statuses',
        'service_invoices',
        'service_invoice_items',
        'service_invoice_payments',
        'service_packages',
        'service_package_items',
        'service_orders',

        // Booking & Appointments
        'booking_appointments',
        'booking_providers',
        'booking_categories',
        'booking_working_hours',
        'booking_payments',
        'booking_coupons',
        'booking_services',
        'booking_customers',
        'booking_settings',

        // Market & Orders
        'market_orders',
        'market_order_items',
        'market_order_meta',
        'market_order_statuses',
        'market_categories',
        'market_brands',
        'market_vendors',
        'market_vendor_documents',
        'market_master_products',
        'market_vendor_products',
        'market_product_variants',
        'market_warehouses',
        'market_warehouse_stocks',
        'market_warehouse_transactions',
        'market_warehouse_transfers',
        'market_shipping_zones',
        'market_shipping_methods',
        'market_shipping_rates',
        'market_shipping_slots',
        'market_shipping_slot_bookings',
        'market_product_reviews',
        'market_product_questions',
        'checkout_forms',

        // Wallet
        'wallets',
        'wallet_transactions',

        // ContentForge
        'content_forge_articles',
        'content_forge_campaigns',
        'content_forge_generated_contents',
        'content_forge_keywords',
        'content_forge_outlines',
        'content_forge_sources',

        // Core Forms & Custom Fields
        'client_forms',
        'custom_user_fields',
        'custom_user_field_values',
        'user_dashboard_settings',
        'user_relationships',
        'activity_logs',
        'notifications',
        'notification_preferences',
        'notification_user_settings',
        'notification_broadcasts',
    ];

    public function handle(): int
    {
        $this->info('Checking multi-tenancy isolation across all tables...');

        if (!Schema::hasTable('tenants')) {
            $this->warn('Table "tenants" does not exist yet. Please run base migrations first.');
            return self::FAILURE;
        }

        $addedCount = 0;
        $skippedCount = 0;
        $missingTableCount = 0;

        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                $missingTableCount++;
                continue;
            }

            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $hasId = Schema::hasColumn($tableName, 'id');
                    $col = $table->unsignedBigInteger('tenant_id')->nullable();
                    if ($hasId) {
                        $col->after('id');
                    }
                    $col->index();

                    $table->foreign('tenant_id')
                        ->references('id')
                        ->on('tenants')
                        ->nullOnDelete();

                    if (Schema::hasColumn($tableName, 'created_at')) {
                        $table->index(['tenant_id', 'created_at'], "{$tableName}_tenant_created_idx");
                    }
                });

                $this->line("<info>[ISOLATED]</info> Added tenant_id column, index, and FK to table: {$tableName}");
                $addedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->newLine();
        $this->info("Completed: {$addedCount} tables newly isolated, {$skippedCount} tables already isolated, {$missingTableCount} unmigrated module tables.");

        return self::SUCCESS;
    }
}
