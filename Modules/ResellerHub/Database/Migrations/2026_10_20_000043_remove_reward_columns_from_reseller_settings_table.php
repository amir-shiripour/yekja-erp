<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_settings', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'onboarding_tour_reward_enabled',
                'onboarding_tour_reward_days',
            ] as $column) {
                if (Schema::hasColumn('reseller_settings', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reseller_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_reward_enabled')) {
                $table->boolean('onboarding_tour_reward_enabled')->default(false)->after('onboarding_tour_grace_period_days');
            }
            if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_reward_days')) {
                $table->unsignedSmallInteger('onboarding_tour_reward_days')->default(0)->after('onboarding_tour_reward_enabled');
            }
        });
    }
};
