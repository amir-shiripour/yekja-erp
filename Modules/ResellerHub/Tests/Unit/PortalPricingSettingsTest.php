<?php

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Actions\Settings\UpdateResellerSettingsAction;
use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerSettingsRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Tests\TestCase;

class PortalPricingSettingsTest extends TestCase
{
    public function test_reseller_settings_table_has_portal_customization_columns(): void
    {
        $expectedColumns = [
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

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('reseller_settings', $column),
                "Missing expected column '{$column}' in reseller_settings table"
            );
        }
    }

    public function test_reseller_setting_model_casts_and_persists_portal_attributes(): void
    {
        $user = User::create([
            'name' => 'کاربر تستی ' . uniqid('', true),
            'email' => uniqid('', true) . '@example.test',
            'mobile' => '0912' . mt_rand(1000000, 9999999),
            'password' => 'Secret123!@#',
        ]);
        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company_name' => 'تست پرتال و تعرفه‌ها',
            'status' => Reseller::STATUS_ACTIVE,
        ]);

        $badges = ['ضمانت بازگشت وجه', 'راه‌اندازی فوری'];
        $faq = [
            ['q' => 'آیا امکان تغییر پکیج وجود دارد؟', 'a' => 'بله در هر زمان.'],
        ];

        ResellerSetting::setMany($reseller->id, [
            'portal_hero_enabled' => false,
            'portal_hero_badge' => 'جشنواره ویژه',
            'portal_hero_title' => 'تعرفه‌های استثنایی پاییز',
            'portal_hero_trust_badges' => $badges,
            'portal_grid_columns' => '4',
            'portal_package_sort' => 'price_asc',
            'portal_show_daily_price' => true,
            'portal_show_setup_fee' => false,
            'portal_cta_text' => 'سفارش فوری سامانه',
            'portal_faq_enabled' => true,
            'portal_faq_items' => $faq,
            'portal_comparison_mode' => 'expanded',
            'portal_checkout_fast_track' => true,
        ]);

        $loaded = ResellerSetting::findForReseller($reseller->id);
        $this->assertNotNull($loaded);
        $this->assertFalse((bool)$loaded->portal_hero_enabled);
        $this->assertEquals('جشنواره ویژه', $loaded->portal_hero_badge);
        $this->assertEquals('تعرفه‌های استثنایی پاییز', $loaded->portal_hero_title);
        $this->assertEquals($badges, $loaded->portal_hero_trust_badges);
        $this->assertEquals('4', $loaded->portal_grid_columns);
        $this->assertEquals('price_asc', $loaded->portal_package_sort);
        $this->assertTrue((bool)$loaded->portal_show_daily_price);
        $this->assertFalse((bool)$loaded->portal_show_setup_fee);
        $this->assertEquals('سفارش فوری سامانه', $loaded->portal_cta_text);
        $this->assertEquals('expanded', $loaded->portal_comparison_mode);
        $this->assertTrue((bool)$loaded->portal_checkout_fast_track);
        $this->assertCount(1, $loaded->portal_faq_items);
        $this->assertEquals('آیا امکان تغییر پکیج وجود دارد؟', $loaded->portal_faq_items[0]['q']);
    }

    public function test_settings_index_blade_contains_portal_tab_and_panel(): void
    {
        $viewContent = file_get_contents(resource_path('../Modules/ResellerHub/resources/views/user/settings/index.blade.php'));

        $this->assertStringContainsString("'portal'", $viewContent);
        $this->assertStringContainsString('پرتال و تعرفه‌ها', $viewContent);
        $this->assertStringContainsString("tab-portal", $viewContent);

        $tabPortalContent = file_get_contents(resource_path('../Modules/ResellerHub/resources/views/user/settings/tabs/tab-portal.blade.php'));
        $this->assertStringContainsString('id="tabpanel-portal"', $tabPortalContent);
        $this->assertStringContainsString("activeTab === 'portal'", $tabPortalContent);
        $this->assertStringContainsString('portal_hero_enabled', $tabPortalContent);
        $this->assertStringContainsString('portal_grid_columns', $tabPortalContent);
        $this->assertStringContainsString('portal_package_sort', $tabPortalContent);
        $this->assertStringContainsString('portal_cta_text', $tabPortalContent);
        $this->assertStringContainsString('portal_faq_enabled', $tabPortalContent);
    }

    public function test_public_pricing_view_integrates_portal_settings(): void
    {
        $pricingView = file_get_contents(resource_path('../Modules/ResellerHub/resources/views/public/pricing.blade.php'));

        $this->assertStringContainsString('$heroEnabled', $pricingView);
        $this->assertStringContainsString('$heroBadge', $pricingView);
        $this->assertStringContainsString('$heroTitle', $pricingView);
        $this->assertStringContainsString('$heroTrustBadges', $pricingView);
        $this->assertStringContainsString('$gridColsClass', $pricingView);
        $this->assertStringContainsString('$ctaText', $pricingView);
        $this->assertStringContainsString('enableFilters', $pricingView);
        $this->assertStringContainsString('filterPackage', $pricingView);
        $this->assertStringContainsString('$comparisonMode', $pricingView);
        $this->assertStringContainsString('$faqEnabled', $pricingView);
    }
}
