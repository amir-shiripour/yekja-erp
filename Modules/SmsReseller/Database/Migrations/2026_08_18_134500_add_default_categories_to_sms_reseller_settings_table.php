<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sms_reseller_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('default_income_category_id')->nullable()->after('base_url');
            $table->unsignedBigInteger('default_expense_category_id')->nullable()->after('default_income_category_id');
            $table->unsignedBigInteger('default_tax_category_id')->nullable()->after('default_expense_category_id');
            $table->unsignedBigInteger('default_fund_account_id')->nullable()->after('default_tax_category_id');
            $table->unsignedBigInteger('default_wallet_fund_account_id')->nullable()->after('default_fund_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_reseller_settings', function (Blueprint $table) {
            $table->dropColumn([
                'default_income_category_id',
                'default_expense_category_id',
                'default_tax_category_id',
                'default_fund_account_id',
                'default_wallet_fund_account_id',
            ]);
        });
    }
};
