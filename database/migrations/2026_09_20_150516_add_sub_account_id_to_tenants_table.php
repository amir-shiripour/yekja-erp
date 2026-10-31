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
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('sub_account_id')->nullable()->after('owner_id')->constrained('reseller_sub_accounts')->nullOnDelete();
        });

        // Backfill existing data
        if (Schema::hasTable('reseller_sub_accounts')) {
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
