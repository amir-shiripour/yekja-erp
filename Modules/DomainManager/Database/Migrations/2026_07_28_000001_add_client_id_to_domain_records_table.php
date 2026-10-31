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
            if (!Schema::hasColumn('domain_records', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('domain_reseller_id');
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_records', function (Blueprint $table) {
            if (Schema::hasColumn('domain_records', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });
    }
};
