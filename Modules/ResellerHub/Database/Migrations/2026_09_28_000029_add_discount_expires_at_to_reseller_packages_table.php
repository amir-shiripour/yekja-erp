<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_packages') && !Schema::hasColumn('reseller_packages', 'discount_expires_at')) {
            Schema::table('reseller_packages', function (Blueprint $table) {
                $table->timestamp('discount_expires_at')->nullable()->after('discount_value');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_packages') && Schema::hasColumn('reseller_packages', 'discount_expires_at')) {
            Schema::table('reseller_packages', function (Blueprint $table) {
                $table->dropColumn('discount_expires_at');
            });
        }
    }
};
