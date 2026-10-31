<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_module_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->nullable()->index();
            $table->string('module_slug', 100)->index();
            $table->string('feature_key', 150)->nullable()->index();
            $table->string('feature_title', 200)->nullable();
            $table->unsignedBigInteger('monthly_price')->default(0);
            $table->unsignedBigInteger('yearly_price')->default(0);
            $table->unsignedBigInteger('setup_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('reseller_id')
                ->references('id')
                ->on('resellers')
                ->onDelete('cascade');

            $table->unique(['reseller_id', 'module_slug', 'feature_key'], 'reseller_mod_feat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_module_pricings');
    }
};