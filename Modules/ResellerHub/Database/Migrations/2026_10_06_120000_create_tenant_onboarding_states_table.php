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
        Schema::create('tenant_onboarding_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('step_slug', 100);
            $table->string('journey_slug', 100)->default('core');
            $table->string('status', 30)->default('pending'); // pending, completed, skipped
            $table->timestamp('completed_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'step_slug'], 'tenant_step_unique');
            $table->index(['tenant_id', 'status'], 'tenant_status_idx');
            $table->index(['tenant_id', 'journey_slug'], 'tenant_journey_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_states');
    }
};
