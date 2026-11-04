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
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_optional_reminder_enabled')) {
                    $table->boolean('onboarding_tour_optional_reminder_enabled')
                        ->default(true)
                        ->after('onboarding_tour_steps');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_optional_reminder_interval_days')) {
                    $table->unsignedSmallInteger('onboarding_tour_optional_reminder_interval_days')
                        ->default(3)
                        ->after('onboarding_tour_optional_reminder_enabled');
                }
                if (!Schema::hasColumn('reseller_settings', 'onboarding_tour_optional_reminder_mode')) {
                    $table->string('onboarding_tour_optional_reminder_mode', 30)
                        ->default('pill')
                        ->after('onboarding_tour_optional_reminder_interval_days');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $columns = [
                    'onboarding_tour_optional_reminder_enabled',
                    'onboarding_tour_optional_reminder_interval_days',
                    'onboarding_tour_optional_reminder_mode',
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
