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
        if (Schema::hasTable('tenants') && !Schema::hasColumn('tenants', 'sub_account_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasTable('reseller_sub_accounts')) {
                    $table->foreignId('sub_account_id')->nullable()->constrained('reseller_sub_accounts')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('sub_account_id')->nullable()->index();
                }
            });
        }

        // Backfill existing data
        if (Schema::hasTable('reseller_sub_accounts') && Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'sub_account_id')) {
            $subAccounts = \Illuminate\Support\Facades\DB::table('reseller_sub_accounts')
                ->whereNotNull('tenant_id')
                ->get(['id', 'tenant_id']);

            foreach ($subAccounts as $sa) {
                \Illuminate\Support\Facades\DB::table('tenants')
                    ->where('id', $sa->tenant_id)
                    ->whereNull('sub_account_id')
                    ->update(['sub_account_id' => $sa->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['sub_account_id']);
            $table->dropColumn('sub_account_id');
        });
    }
};
