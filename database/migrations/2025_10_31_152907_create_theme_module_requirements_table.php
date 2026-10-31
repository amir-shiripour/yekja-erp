<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('theme_module_requirements')) {
            Schema::create('theme_module_requirements', function (Blueprint $table) {
                $table->id();

                $table->foreignId('theme_id')
                    ->constrained('themes')
                    ->onDelete('cascade');

                $table->foreignId('module_id')
                    ->constrained('modules')
                    ->onDelete('cascade');

                $table->unique(['theme_id', 'module_id']);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_module_requirements');
    }
};

