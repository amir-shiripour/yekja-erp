<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('da_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('da_server_id')->constrained('da_servers')->onDelete('cascade');
            $table->unsignedBigInteger('client_service_id')->nullable(); // Future service module integration hook
            $table->string('username');
            $table->string('domain');
            $table->string('email')->nullable();
            $table->string('package')->nullable();
            $table->string('ip')->nullable();
            $table->boolean('suspended')->default(false);
            
            $table->bigInteger('disk_usage_bytes')->default(0);
            $table->bigInteger('disk_limit_bytes')->default(0);
            $table->bigInteger('bandwidth_usage_bytes')->default(0);
            $table->bigInteger('bandwidth_limit_bytes')->default(0);
            
            $table->integer('domains_count')->default(0);
            $table->integer('email_accounts_count')->default(0);
            $table->integer('mysql_databases_count')->default(0);
            
            $table->string('status')->default('active');
            $table->date('date_created')->nullable();
            $table->json('raw_config')->nullable();
            $table->timestamp('synced_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['da_server_id', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('da_accounts');
    }
};
