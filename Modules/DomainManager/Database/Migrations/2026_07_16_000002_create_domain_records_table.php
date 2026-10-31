<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domain_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_reseller_id')
                ->constrained('domain_resellers')
                ->onDelete('cascade');
            $table->string('order_id')->nullable()->index(); // LogicBoxes order ID
            $table->string('domain_name')->index();
            $table->string('status')->default('active'); // active, expired, suspended, pending
            $table->date('expiry_date')->nullable();
            $table->date('creation_date')->nullable();
            $table->json('nameservers')->nullable();
            $table->boolean('privacy_protected')->default(false);
            $table->string('auth_code')->nullable(); // EPP code
            $table->json('raw_details')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_records');
    }
};
