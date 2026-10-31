<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            $table->string('invoice_number', 50)->unique();
            $table->string('type', 20)->default('official');
            $table->string('status', 20)->default('unpaid');
            $table->string('client_name', 150);
            $table->string('client_mobile', 50)->nullable();
            $table->string('client_email', 150)->nullable();
            $table->date('issue_date');
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
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reseller_id')
                ->references('id')
                ->on('resellers')
                ->onDelete('cascade');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null');

            $table->foreign('package_id')
                ->references('id')
                ->on('reseller_packages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_invoices');
    }
};