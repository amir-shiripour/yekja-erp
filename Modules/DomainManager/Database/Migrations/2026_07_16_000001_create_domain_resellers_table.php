<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domain_resellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('auth_userid');
            $table->text('api_key');
            $table->boolean('is_active')->default(true);
            $table->decimal('balance', 16, 4)->default(0.0000);
            $table->string('currency')->default('USD');
            $table->timestamp('last_connected_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_resellers');
    }
};
