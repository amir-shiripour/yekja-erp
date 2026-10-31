<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_enabled')) {
                    $table->boolean('onboarding_tour_enabled')->default(true)->after('subdomain_pattern');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_display_mode')) {
                    $table->string('onboarding_tour_display_mode', 30)->default('bento')->after('onboarding_tour_enabled');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_enforcement')) {
                    $table->string('onboarding_tour_enforcement', 30)->default('soft')->after('onboarding_tour_display_mode');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_grace_period_days')) {
                    $table->unsignedSmallInteger('onboarding_tour_grace_period_days')->default(3)->after('onboarding_tour_enforcement');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_reward_enabled')) {
                    $table->boolean('onboarding_tour_reward_enabled')->default(false)->after('onboarding_tour_grace_period_days');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_reward_days')) {
                    $table->unsignedSmallInteger('onboarding_tour_reward_days')->default(0)->after('onboarding_tour_reward_enabled');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_celebration_title')) {
                    $table->string('onboarding_tour_celebration_title')->nullable()->after('onboarding_tour_reward_days');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_celebration_message')) {
                    $table->text('onboarding_tour_celebration_message')->nullable()->after('onboarding_tour_celebration_title');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_steps')) {
                    $table->json('onboarding_tour_steps')->nullable()->after('onboarding_tour_celebration_message');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $columns = [
                    'onboarding_tour_enabled',
                    'onboarding_tour_display_mode',
                    'onboarding_tour_enforcement',
                    'onboarding_tour_grace_period_days',
                    'onboarding_tour_reward_enabled',
                    'onboarding_tour_reward_days',
                    'onboarding_tour_celebration_title',
                    'onboarding_tour_celebration_message',
                    'onboarding_tour_steps',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('reseller_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
