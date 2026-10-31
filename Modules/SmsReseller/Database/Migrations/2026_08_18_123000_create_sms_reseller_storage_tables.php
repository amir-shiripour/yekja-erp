<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_reseller_user_payments', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->string('user_name')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->string('bank')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('state')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('jalali_date', 30)->nullable()->index();
            $table->string('jalali_ym', 10)->nullable()->index(); // 1405/05
            $table->date('gregorian_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('sms_reseller_panel_payments', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->string('account_name')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('state')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('jalali_date', 30)->nullable()->index();
            $table->string('jalali_ym', 10)->nullable()->index(); // 1405/05
            $table->date('gregorian_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('sms_reseller_user_credits', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->string('user_name')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('current_credit', 18, 2)->default(0);
            $table->boolean('increase')->default(false)->index();
            $table->string('type')->nullable()->index();
            $table->string('jalali_date', 30)->nullable()->index();
            $table->string('jalali_ym', 10)->nullable()->index();
            $table->date('gregorian_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('sms_reseller_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('jalali_date', 10)->unique(); // 1405/05/27
            $table->string('jalali_ym', 7)->index(); // 1405/05
            $table->string('jalali_year', 4)->index(); // 1405
            $table->date('gregorian_date')->index();
            $table->decimal('user_income_amount', 18, 2)->default(0);
            $table->decimal('user_income_tax', 18, 2)->default(0);
            $table->decimal('user_income_total', 18, 2)->default(0);
            $table->unsignedInteger('user_payments_count')->default(0);
            $table->decimal('panel_charge_amount', 18, 2)->default(0);
            $table->decimal('panel_charge_tax', 18, 2)->default(0);
            $table->decimal('panel_charge_total', 18, 2)->default(0);
            $table->unsignedInteger('panel_charges_count')->default(0);
            $table->unsignedInteger('user_credit_tx_count')->default(0);
            $table->decimal('user_credit_increase_sum', 18, 2)->default(0);
            $table->decimal('user_credit_decrease_sum', 18, 2)->default(0);
            $table->decimal('net_profit', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_reseller_daily_summaries');
        Schema::dropIfExists('sms_reseller_user_credits');
        Schema::dropIfExists('sms_reseller_panel_payments');
        Schema::dropIfExists('sms_reseller_user_payments');
    }
};
