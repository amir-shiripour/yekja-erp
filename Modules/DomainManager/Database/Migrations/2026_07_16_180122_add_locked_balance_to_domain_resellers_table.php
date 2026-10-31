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
        Schema::table('domain_resellers', function (Blueprint $table) {
            $table->decimal('locked_balance', 16, 2)->default(0.00)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_resellers', function (Blueprint $table) {
            $table->dropColumn('locked_balance');
        });
    }
};
