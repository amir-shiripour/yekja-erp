<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All tables across 24 modules requiring tenant_id for full multi-tenant data isolation.
     */
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
        'cockpit_goals',

        // Workflows
        'workflows',
        'workflow_actions',
        'workflow_edges',
        'workflow_instances',
        'workflow_logs',
        'workflow_nodes',
        'workflow_stages',
        'workflow_triggers',

        // SmartBot & SMS & Wallet
        'bot_answers',
        'bot_messages',
        'bot_questions',
        'bot_sessions',
        'bot_settings',
        'sms_gateway_settings',
        'sms_messages',
        'sms_otps',
        'sms_reseller_daily_summaries',
        'sms_reseller_panel_payments',
        'sms_reseller_settings',
        'sms_reseller_user_credits',
        'sms_reseller_user_payments',
        'sms_templates',
        'wallets',
        'wallet_transactions',

        // Market
        'checkout_forms',
        'market_attribute_values',
        'market_attributes',
        'market_brands',
        'market_categories',
        'market_display_categories',
        'market_master_products',
        'market_order_items',
        'market_order_meta',
        'market_order_statuses',
        'market_orders',
        'market_product_display_category',
        'market_product_questions',
        'market_product_reviews',
        'market_product_variants',
        'market_settings',
        'market_shipping_methods',
        'market_shipping_rates',
        'market_shipping_rules',
        'market_shipping_slot_bookings',
        'market_shipping_slots',
        'market_shipping_zones',
        'market_vendor_addresses',
        'market_vendor_documents',
        'market_vendor_products',
        'market_vendors',
        'market_warehouse_stocks',
        'market_warehouse_transactions',
        'market_warehouse_transfers',
        'market_warehouses',

        // Properties
        'properties',
        'property_attribute_values',
        'property_attributes',
        'property_buildings',
        'property_categories',
        'property_hosts',
        'property_images',
        'property_owners',
        'property_rental_blocks',
        'property_rental_configs',
        'property_rental_prices',
        'property_settings',
        'property_statuses',

        // ContentForge & ContractForge
        'content_categories',
        'content_comments',
        'content_entities',
        'content_post_revisions',
        'content_posts',
        'content_redirects',
        'content_settings',
        'content_short_links',
        'content_tags',
        'contract_rules',
        'contract_settings',
        'contract_templates',
        'contracts',

        // Services extra
        'service_package_items',
        'service_templates',
        'services_activity_log',
        'services_catalog',
        'services_custom_field_values',
        'services_custom_fields',
        'services_projects',
        'services_statuses',

        // Domain & DirectAdmin
        'da_accounts',
        'da_settings',
        'domain_logs',
        'domain_records',

        // Activity & Notifications
        'activity_logs',
        'notifications',
        'notification_preferences',
        'notification_user_settings',
        'notification_broadcasts',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $hasId = Schema::hasColumn($tableName, 'id');
                    $col = $table->unsignedBigInteger('tenant_id')->nullable();
                    if ($hasId) {
                        $col->after('id');
                    }
                    $col->index();

                    // Foreign key constraint pointing to tenants(id)
                    $table->foreign('tenant_id')
                          ->references('id')
                          ->on('tenants')
                          ->nullOnDelete();

                    // Performance Composite Index
                    if (Schema::hasColumn($tableName, 'created_at')) {
                        $table->index(['tenant_id', 'created_at'], "{$tableName}_tenant_created_idx");
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Drop composite index if exists
                    if (Schema::hasColumn($tableName, 'created_at')) {
                        try {
                            $table->dropIndex("{$tableName}_tenant_created_idx");
                        } catch (\Throwable $e) {}
                    }

                    try {
                        $table->dropForeign([ 'tenant_id' ]);
                    } catch (\Throwable $e) {}

                    try {
                        $table->dropColumn('tenant_id');
                    } catch (\Throwable $e) {}
                });
            }
        }
    }
};
