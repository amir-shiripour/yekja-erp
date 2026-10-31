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
        if (!Schema::hasTable('reseller_packages')) {
            Schema::create('reseller_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon', 50)->default('cube');
                $table->string('color', 50)->default('indigo');
                $table->json('modules');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['reseller_id', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_packages');
    }
};
