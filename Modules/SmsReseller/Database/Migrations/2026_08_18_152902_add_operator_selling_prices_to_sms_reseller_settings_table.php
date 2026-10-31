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
            $table->json('operator_selling_prices')->nullable()->after('default_wallet_fund_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_reseller_settings', function (Blueprint $table) {
            $table->dropColumn('operator_selling_prices');
        });
    }
};
