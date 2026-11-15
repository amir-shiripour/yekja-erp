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
                if (!Schema::hasColumn('reseller_settings', 'portal_hero_enabled')) {
                    $table->boolean('portal_hero_enabled')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_hero_badge')) {
                    $table->string('portal_hero_badge', 150)->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_hero_title')) {
                    $table->string('portal_hero_title', 255)->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_hero_subtitle')) {
                    $table->text('portal_hero_subtitle')->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_hero_trust_badges')) {
                    $table->json('portal_hero_trust_badges')->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_default_period')) {
                    $table->string('portal_default_period', 50)->default('monthly_price');
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_allowed_periods')) {
                    $table->json('portal_allowed_periods')->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_grid_columns')) {
                    $table->string('portal_grid_columns', 20)->default('3');
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_package_sort')) {
                    $table->string('portal_package_sort', 30)->default('features_desc');
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_featured_package_id')) {
                    $table->unsignedBigInteger('portal_featured_package_id')->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_show_daily_price')) {
                    $table->boolean('portal_show_daily_price')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_show_setup_fee')) {
                    $table->boolean('portal_show_setup_fee')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_cta_text')) {
                    $table->string('portal_cta_text', 100)->default('انتخاب و شروع');
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_enable_filters')) {
                    $table->boolean('portal_enable_filters')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_enable_module_filter')) {
                    $table->boolean('portal_enable_module_filter')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_comparison_mode')) {
                    $table->string('portal_comparison_mode', 30)->default('collapsible');
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_faq_enabled')) {
                    $table->boolean('portal_faq_enabled')->default(true);
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_faq_items')) {
                    $table->json('portal_faq_items')->nullable();
                }
                if (!Schema::hasColumn('reseller_settings', 'portal_checkout_fast_track')) {
                    $table->boolean('portal_checkout_fast_track')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $columns = [
                    'portal_hero_enabled',
                    'portal_hero_badge',
                    'portal_hero_title',
                    'portal_hero_subtitle',
                    'portal_hero_trust_badges',
                    'portal_default_period',
                    'portal_allowed_periods',
                    'portal_grid_columns',
                    'portal_package_sort',
                    'portal_featured_package_id',
                    'portal_show_daily_price',
                    'portal_show_setup_fee',
                    'portal_cta_text',
                    'portal_enable_filters',
                    'portal_enable_module_filter',
                    'portal_comparison_mode',
                    'portal_faq_enabled',
                    'portal_faq_items',
                    'portal_checkout_fast_track',
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
