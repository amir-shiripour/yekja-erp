<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->string('gateway'); // zarinpal, etc.
                $table->string('authority')->nullable()->unique();
                $table->string('ref_id')->nullable()->unique();
                $table->string('status')->default('pending'); // pending, success, failed
                $table->string('description')->nullable();
                $table->text('callback_url')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
