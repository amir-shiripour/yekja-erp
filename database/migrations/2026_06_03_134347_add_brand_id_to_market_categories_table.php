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
        if (Schema::hasTable('market_categories') && Schema::hasTable('market_brands')) {
            if (!Schema::hasColumn('market_categories', 'brand_id')) {
                Schema::table('market_categories', function (Blueprint $table) {
                    $table->foreignId('brand_id')->nullable()->constrained('market_brands')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_categories', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
