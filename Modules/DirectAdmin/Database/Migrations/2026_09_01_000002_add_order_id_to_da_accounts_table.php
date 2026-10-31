<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('da_accounts') && !Schema::hasColumn('da_accounts', 'order_id')) {
            Schema::table('da_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('client_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('da_accounts') && Schema::hasColumn('da_accounts', 'order_id')) {
            Schema::table('da_accounts', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }
    }
};
