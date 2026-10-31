<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants') || Schema::hasColumn('tenants', 'checkout_expires_at')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->timestamp('checkout_expires_at')->nullable()->after('plan_expires_at');
            // Cleanup queries always filter by status = 'pending_payment' AND checkout_expires_at < ?
            $table->index(['status', 'checkout_expires_at'], 'tenants_status_checkout_expires_index');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenants') || !Schema::hasColumn('tenants', 'checkout_expires_at')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropIndex('tenants_status_checkout_expires_index');
            $table->dropColumn('checkout_expires_at');
        });
    }
};
