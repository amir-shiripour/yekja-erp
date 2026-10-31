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
        Schema::create('domain_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('domain_reseller_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('domain_name')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('status')->default('success');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_logs');
    }
};
