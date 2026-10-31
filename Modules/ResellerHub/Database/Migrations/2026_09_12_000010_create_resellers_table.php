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
        if (!Schema::hasTable('resellers')) {
            Schema::create('resellers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('company_name');
                $table->string('brand_title')->nullable();
                $table->string('brand_logo')->nullable();
                $table->string('brand_favicon')->nullable();
                $table->text('brand_footer_text')->nullable();
                $table->string('support_phone')->nullable();
                $table->string('support_email')->nullable();
                $table->integer('max_tenants')->default(10);
                $table->string('status', 20)->default('active')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};
