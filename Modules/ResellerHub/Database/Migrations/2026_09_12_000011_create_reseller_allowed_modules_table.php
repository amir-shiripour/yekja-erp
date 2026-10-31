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
        if (!Schema::hasTable('reseller_allowed_modules')) {
            Schema::create('reseller_allowed_modules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
                $table->string('module_slug', 50)->index();
                $table->timestamps();

                $table->unique(['reseller_id', 'module_slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_allowed_modules');
    }
};
