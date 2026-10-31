<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('order_number', 50)->unique();
            $table->string('client_name', 150);
            $table->string('client_mobile', 50)->nullable();
            $table->string('client_email', 150)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('billing_cycle', 20)->default('yearly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->json('modules_snapshot')->nullable();
            $table->string('provisioning_status', 30)->default('not_provisioned');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reseller_id')
                ->references('id')
                ->on('resellers')
                ->onDelete('cascade');

            $table->foreign('invoice_id')
                ->references('id')
                ->on('reseller_invoices')
                ->onDelete('set null');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_orders');
    }
};