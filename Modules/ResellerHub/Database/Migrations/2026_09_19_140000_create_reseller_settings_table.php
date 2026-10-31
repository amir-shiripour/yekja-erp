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
        Schema::create('reseller_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('resellers')->onDelete('cascade');
            
            // General Settings
            $table->integer('default_plan_duration_months')->default(12);
            $table->boolean('auto_suspend_expired')->default(false);
            $table->integer('grace_period_days')->default(0);
            $table->integer('notify_days_before_expiry')->default(0);
            $table->string('notification_email')->nullable();
            $table->string('notification_mobile')->nullable();
            $table->boolean('allow_custom_domains')->default(false);
            
            // Financial Settings
            $table->string('currency')->default('toman');
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->string('tax_mode')->nullable();
            
            // Invoice Settings
            $table->string('invoice_prefix')->nullable();
            $table->string('invoice_middle_prefix')->nullable();
            $table->string('invoice_suffix')->nullable();
            $table->integer('invoice_padding')->default(0);
            $table->boolean('invoice_auto')->default(false);
            
            // Proforma Settings
            $table->string('proforma_invoice_prefix')->nullable();
            $table->string('proforma_invoice_middle_prefix')->nullable();
            $table->string('proforma_invoice_suffix')->nullable();
            $table->integer('proforma_invoice_padding')->default(0);
            $table->boolean('proforma_invoice_auto')->default(false);
            
            $table->text('invoice_terms')->nullable();
            $table->text('invoice_footer_note')->nullable();
            $table->boolean('reseller_use_global_payment_settings')->default(false);
            
            // Bank Settings
            $table->string('bank_name')->nullable();
            $table->string('bank_card_number')->nullable();
            $table->string('bank_shaba_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            
            $table->timestamps();
            
            $table->unique('reseller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_settings');
    }
};
