<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename columns on reseller_invoices
        if (Schema::hasTable('reseller_invoices')) {
            if (Schema::hasColumn('reseller_invoices', 'client_name') && !Schema::hasColumn('reseller_invoices', 'sub_account_name')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `client_name` `sub_account_name` VARCHAR(150) NOT NULL");
            }
            if (Schema::hasColumn('reseller_invoices', 'client_mobile') && !Schema::hasColumn('reseller_invoices', 'sub_account_mobile')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `client_mobile` `sub_account_mobile` VARCHAR(50) NULL DEFAULT NULL");
            }
            if (Schema::hasColumn('reseller_invoices', 'client_email') && !Schema::hasColumn('reseller_invoices', 'sub_account_email')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `client_email` `sub_account_email` VARCHAR(150) NULL DEFAULT NULL");
            }
        }

        // 2. Rename columns on reseller_orders
        if (Schema::hasTable('reseller_orders')) {
            if (Schema::hasColumn('reseller_orders', 'client_name') && !Schema::hasColumn('reseller_orders', 'sub_account_name')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `client_name` `sub_account_name` VARCHAR(150) NOT NULL");
            }
            if (Schema::hasColumn('reseller_orders', 'client_mobile') && !Schema::hasColumn('reseller_orders', 'sub_account_mobile')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `client_mobile` `sub_account_mobile` VARCHAR(50) NULL DEFAULT NULL");
            }
            if (Schema::hasColumn('reseller_orders', 'client_email') && !Schema::hasColumn('reseller_orders', 'sub_account_email')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `client_email` `sub_account_email` VARCHAR(150) NULL DEFAULT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert columns on reseller_invoices
        if (Schema::hasTable('reseller_invoices')) {
            if (Schema::hasColumn('reseller_invoices', 'sub_account_name') && !Schema::hasColumn('reseller_invoices', 'client_name')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `sub_account_name` `client_name` VARCHAR(150) NOT NULL");
            }
            if (Schema::hasColumn('reseller_invoices', 'sub_account_mobile') && !Schema::hasColumn('reseller_invoices', 'client_mobile')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `sub_account_mobile` `client_mobile` VARCHAR(50) NULL DEFAULT NULL");
            }
            if (Schema::hasColumn('reseller_invoices', 'sub_account_email') && !Schema::hasColumn('reseller_invoices', 'client_email')) {
                DB::statement("ALTER TABLE `reseller_invoices` CHANGE COLUMN `sub_account_email` `client_email` VARCHAR(150) NULL DEFAULT NULL");
            }
        }

        // 2. Revert columns on reseller_orders
        if (Schema::hasTable('reseller_orders')) {
            if (Schema::hasColumn('reseller_orders', 'sub_account_name') && !Schema::hasColumn('reseller_orders', 'client_name')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `sub_account_name` `client_name` VARCHAR(150) NOT NULL");
            }
            if (Schema::hasColumn('reseller_orders', 'sub_account_mobile') && !Schema::hasColumn('reseller_orders', 'client_mobile')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `sub_account_mobile` `client_mobile` VARCHAR(50) NULL DEFAULT NULL");
            }
            if (Schema::hasColumn('reseller_orders', 'sub_account_email') && !Schema::hasColumn('reseller_orders', 'client_email')) {
                DB::statement("ALTER TABLE `reseller_orders` CHANGE COLUMN `sub_account_email` `client_email` VARCHAR(150) NULL DEFAULT NULL");
            }
        }
    }
};
