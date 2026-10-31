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
        Schema::table('domain_records', function (Blueprint $table) {
            if (!Schema::hasColumn('domain_records', 'service_order_id')) {
                $table->unsignedBigInteger('service_order_id')->nullable()->after('client_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_records', function (Blueprint $table) {
            if (Schema::hasColumn('domain_records', 'service_order_id')) {
                $table->dropColumn('service_order_id');
            }
        });
    }
};
