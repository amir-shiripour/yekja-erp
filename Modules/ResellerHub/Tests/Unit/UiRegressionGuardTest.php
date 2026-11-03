<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\File;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Regression guards for the ResellerHub admin UI.
 */
class UiRegressionGuardTest extends TestCase
{
    private function moduleView(string $relative): string
    {
        $path = module_path('ResellerHub', 'resources/views/' . $relative);

        $this->assertFileExists($path, "Expected view [{$relative}] to exist.");

        return File::get($path);
    }

    public function test_settings_save_button_lives_inside_the_main_form(): void
    {
        $blade = $this->moduleView('user/settings/index.blade.php');

        $formClose = strpos($blade, '</form>');
        $saveBar = strpos($blade, 'ذخیره تنظیمات');
        $tenancyInclude = strpos($blade, "user.settings.tabs.tab-tenancy");

        $this->assertNotFalse($formClose, 'The settings view has no closing </form>.');
        $this->assertNotFalse($saveBar, 'The settings view has no save button.');
        $this->assertNotFalse($tenancyInclude, 'The tenancy tab is no longer included.');

        $this->assertLessThan(
            $formClose,
            $saveBar,
            'The save button must be rendered before the main form is closed, otherwise it is orphaned.'
        );

        $this->assertGreaterThan(
            $formClose,
            $tenancyInclude,
            'The tenancy tab owns its own <form> elements and must be rendered OUTSIDE the main settings form.'
        );
    }

    public function test_settings_tenancy_tab_is_not_nested_in_another_form(): void
    {
        $blade = $this->moduleView('user/settings/tabs/tab-tenancy.blade.php');

        $this->assertStringContainsString(
            '<form',
            $blade,
            'The tenancy tab is expected to keep its own standalone forms.'
        );

        // A single tab must never open a form and leave it unclosed: that is what
        // silently swallowed the outer settings form.
        $this->assertSame(
            substr_count($blade, '<form'),
            substr_count($blade, '</form>'),
            'Every <form> in the tenancy tab must be closed.'
        );
    }

    public function test_views_have_no_executor_reseller_column(): void
    {
        $subIndex = $this->moduleView('user/subaccounts/index.blade.php');
        $subPartial = $this->moduleView('user/subaccounts/partials/subaccounts-table.blade.php');
        $tenantsIndex = $this->moduleView('user/tenants/index.blade.php');
        $createBlade = $this->moduleView('user/tenants/create.blade.php');
        $createStep1 = $this->moduleView('user/tenants/partials/create-wizard/step-1-identity.blade.php');
        $createStep2 = $this->moduleView('user/tenants/partials/create-wizard/step-2-packages.blade.php');
        $editStep1 = $this->moduleView('user/tenants/partials/edit-wizard/step-1-identity.blade.php');

        $this->assertStringNotContainsString('نماینده مجری', $subIndex, 'Subaccounts index view must not contain "نماینده مجری"');
        $this->assertStringNotContainsString('نماینده مجری', $subPartial, 'Subaccounts partial table must not contain "نماینده مجری"');
        $this->assertStringNotContainsString('نماینده مجری', $tenantsIndex, 'Tenants index view must not contain "نماینده مجری"');
        $this->assertStringNotContainsString('نماینده مجری', $createStep1, 'Create wizard step-1 must not contain "نماینده مجری"');
        $this->assertStringNotContainsString('نماینده مجری', $createStep2, 'Create wizard step-2 must not contain "نماینده مجری"');
        $this->assertStringNotContainsString('نماینده مجری', $editStep1, 'Edit wizard step-1 must not contain "نماینده مجری"');

        $this->assertStringNotContainsString('ظرفیت ساخت سامانه', $createBlade, 'Create view must not contain "ظرفیت ساخت سامانه"');
        $this->assertStringNotContainsString('ظرفیت ساخت سامانه', $createStep2, 'Create wizard step-2 must not contain "ظرفیت ساخت سامانه"');
        $this->assertStringContainsString('selectedSubAccountId', $createBlade, 'Create view config payload must include selectedSubAccountId');
    }

    public function test_tenants_index_has_unified_hub_elements(): void
    {
        $blade = $this->moduleView('user/tenants/index.blade.php');
        $tableBlade = $this->moduleView('user/dashboard/partials/tenants-table.blade.php');

        // Verify removed elements
        $this->assertStringNotContainsString('Fleet & Clients Hub', $blade);
        $this->assertStringNotContainsString('tab-btn-tenants', $blade);
        $this->assertStringNotContainsString('tab-btn-subaccounts', $blade);
        $this->assertStringNotContainsString('استخراج دیتابیس (SQL Dump)', $blade);
        $this->assertStringNotContainsString('reseller_id', $tableBlade);

        // Verify unified stats & panel
        $this->assertStringContainsString('unified-tenants-panel', $blade);
        $this->assertStringContainsString('totalTenants', $blade);
        $this->assertStringContainsString('totalSubAccounts', $blade);

        // Verify table columns & delete actions
        $this->assertStringContainsString('نام CRM', $tableBlade);
        $this->assertStringContainsString('وضعیت دسترسی', $tableBlade);
        $this->assertStringContainsString('اعتبار لایسنس', $tableBlade);
        $this->assertStringContainsString('طرح و پکیج', $tableBlade);
        $this->assertStringContainsString('آدرس دسترسی', $tableBlade);
        $this->assertStringContainsString('openTenantDeleteModal', $tableBlade);
        $this->assertStringContainsString('openSubAccountDeleteModal', $tableBlade);
    }

    public function test_sub_accounts_table_has_no_owner_or_manager_column(): void
    {
        $blade = $this->moduleView('user/subaccounts/index.blade.php');

        $this->assertStringNotContainsString('خریدار / مالک زیرمجموعه', $blade);
        $this->assertStringNotContainsString('مالک زیرمجموعه', $blade);
        $this->assertStringContainsString('کسب‌وکار / زیرمجموعه', $blade);
    }

    public function test_sub_account_forms_do_not_allocate_a_sales_quota(): void
    {
        foreach (['user/subaccounts/create.blade.php', 'user/subaccounts/edit.blade.php'] as $view) {
            $blade = $this->moduleView($view);

            $this->assertStringNotContainsString(
                'name="limits[tenants]"',
                $blade,
                "[{$view}] still renders the removed per-buyer CRM (sales) quota field."
            );
            $this->assertStringNotContainsString('حداکثر سامانه‌های فعال (CRM)', $blade);
        }

        $show = $this->moduleView('user/subaccounts/show.blade.php');
        $this->assertStringNotContainsString('remainingTenantQuota', $show);
        $this->assertStringNotContainsString('hasTenantQuota', $show);
    }

    public function test_notice_modal_stays_minimal(): void
    {
        $blade = $this->moduleView('partials/notice-modal.blade.php');

        $this->assertStringNotContainsString('bg-gradient-to-r', $blade, 'The notice modal must not use the coloured accent bar again.');
        $this->assertStringNotContainsString('border-rose-200', $blade, 'The notice modal must not use a red bordered message box.');
        $this->assertStringContainsString('max-w-[420px]', $blade);
    }

    public function test_subscription_partials_do_not_leak_raw_translation_keys(): void
    {
        $files = File::glob(module_path('ResellerHub', 'resources/views/tenant/partials/subscription/*.blade.php'));

        $this->assertNotEmpty($files);

        $used = [];
        foreach ($files as $file) {
            preg_match_all('/messages\.subscription\.views\.([a-zA-Z0-9_]+)/', File::get($file), $matches);
            $used = array_merge($used, $matches[1]);
        }

        $used = array_values(array_unique($used));
        $this->assertNotEmpty($used, 'No subscription translation keys were found to verify.');

        $missing = array_values(array_filter(
            $used,
            static fn(string $key): bool => trans('resellerhub::messages.subscription.views.' . $key)
                === 'resellerhub::messages.subscription.views.' . $key
        ));

        $this->assertSame(
            [],
            $missing,
            'These subscription keys render as raw keys in the UI: ' . implode(', ', $missing)
        );
    }

    public function test_reseller_dashboard_does_not_contain_fleet_tab(): void
    {
        $blade = $this->moduleView('user/dashboard/index.blade.php');

        $this->assertStringNotContainsString('id="tab-fleet"', $blade);
        $this->assertStringNotContainsString('id="tabpanel-fleet"', $blade);
        $this->assertStringNotContainsString('سامانه‌ها و مشترکین', $blade);

        $this->assertStringContainsString('id="tab-operations"', $blade);
        $this->assertStringContainsString('id="tab-analytics"', $blade);
        $this->assertStringContainsString('id="tab-intelligence"', $blade);

        $scripts = $this->moduleView('user/dashboard/partials/scripts.blade.php');
        $this->assertStringContainsString("activeTab: (window.location.hash && ['operations', 'analytics', 'intelligence'].includes(window.location.hash.substring(1))) ? window.location.hash.substring(1) : 'operations'", $scripts);
    }

    public function test_tenant_show_and_subaccount_show_have_clean_tabs_and_no_sales_reseller(): void
    {
        // 1. Check tenant show view
        $tenantShow = $this->moduleView('user/tenants/show.blade.php');
        $this->assertStringContainsString("setTab('overview')", $tenantShow);
        $this->assertStringContainsString("setTab('modules')", $tenantShow);
        $this->assertStringContainsString("setTab('quotas')", $tenantShow);
        $this->assertStringContainsString("setTab('branding')", $tenantShow);
        $this->assertStringContainsString("setTab('invoices')", $tenantShow);

        // Check manager card has NO "نماینده فروش ارائه‌دهنده"
        $managerCard = $this->moduleView('user/tenants/partials/show/manager-card.blade.php');
        $this->assertStringNotContainsString('نماینده فروش ارائه‌دهنده', $managerCard);
        $this->assertStringNotContainsString('نماینده فروش', $managerCard);

        // 2. Check subaccount show view
        $subaccountShow = $this->moduleView('user/subaccounts/show.blade.php');
        $this->assertStringContainsString("setTab('tenants')", $subaccountShow);
        $this->assertStringContainsString("setTab('profile')", $subaccountShow);
        $this->assertStringContainsString("setTab('modules')", $subaccountShow);
        $this->assertStringContainsString("setTab('invoices')", $subaccountShow);
        $this->assertStringContainsString("setTab('orders')", $subaccountShow);

        $this->assertStringNotContainsString('نماینده ارائه‌دهنده سامانه:', $subaccountShow);
        $this->assertStringNotContainsString('ثبت دستی نماینده', $subaccountShow);
        $this->assertStringNotContainsString('نماینده فروش', $subaccountShow);
    }

    public function test_tenant_active_modules_include_package_modules(): void
    {
        $tenant = new \App\Models\Tenant();
        $tenant->status = \App\Models\Tenant::STATUS_ACTIVE;
        $tenant->plan_expires_at = now()->addYear();

        $pkg = new \Modules\ResellerHub\App\Models\ResellerPackage();
        $pkg->name = 'Test Plan';
        $pkg->modules = ['clients', 'booking', 'invoices'];
        $tenant->setRelation('package', $pkg);

        $slugs = $tenant->activeModuleSlugs();
        $this->assertContains('clients', $slugs);
        $this->assertContains('booking', $slugs);
        $this->assertContains('invoices', $slugs);
    }

    public function test_tenant_overview_copy_button_and_translations_and_package_discount_badges(): void
    {
        // 1. Check copy button in tenant subscription card
        $subCard = $this->moduleView('user/tenants/partials/show/subscription-card.blade.php');
        $this->assertStringContainsString('x-data="{ copied: false }"', $subCard);
        $this->assertStringContainsString('کپی شد!', $subCard);

        // 2. Check wizard step 3 translation keys
        app()->setLocale('fa');
        $this->assertEquals('تولید خودکار (از تنظیمات)', __('resellerhub::messages.wizard.step3.invoice_number_label'));
        $this->assertEquals('ورود دستی', __('resellerhub::messages.wizard.step3.invoice_number_manual'));

        // 3. Check Persian validation strings
        $this->assertStringContainsString('حروف کوچک', __('validation.lowercase', ['attribute' => 'ایمیل']));
        $this->assertStringContainsString('عدد', __('validation.password.numbers', ['attribute' => 'رمز عبور']));
        $this->assertEquals('آدرس اختصاصی (ساب‌دامین)', __('validation.attributes.subdomain'));
        $this->assertEquals('پکیج انتخابی', __('validation.attributes.package_id'));

        // 4. Check dynamic discount percentage badge in package card-pricing
        $createPricing = $this->moduleView('user/packages/partials/create/card-pricing.blade.php');
        $this->assertStringContainsString('calcPeriodDiscountPercent(row.months)', $createPricing);
        $this->assertStringContainsString('٪<span x-text="faNum(calcPeriodDiscountPercent(row.months))"></span> تخفیف', $createPricing);

        $editPricing = $this->moduleView('user/packages/partials/edit/card-pricing.blade.php');
        $this->assertStringContainsString('calcPeriodDiscountPercent(row.months)', $editPricing);
        $this->assertStringContainsString('٪<span x-text="faNum(calcPeriodDiscountPercent(row.months))"></span> تخفیف', $editPricing);

        // 5. Check script has calculation helpers
        $createScript = $this->moduleView('user/packages/partials/create/script.blade.php');
        $this->assertStringContainsString('calcDiscountPercent(basePrice, discountValue)', $createScript);
        $this->assertStringContainsString('calcPeriodDiscountPercent(months)', $createScript);
    }

    public function test_package_requests_validate_discount_cannot_exceed_base_price(): void
    {
        // 1. Subscription period discount > period base price (e.g. 20 quadrillion on 6 million)
        $req = new \Modules\ResellerHub\App\Http\Requests\StorePackageRequest();
        $payload = [
            'name' => 'پکیج تست',
            'modules' => ['clients'],
            'price_type' => 'subscription',
            'period_prices' => ['3' => '6000000'],
            'period_discounts' => [
                '3' => [
                    'has_custom' => '1',
                    'discount_value' => '20000000000000000',
                ],
            ],
        ];

        $req->merge($payload);
        $req->setContainer(app());
        $req->setRedirector(app('redirect'));

        // Run validation
        $validator = \Illuminate\Support\Facades\Validator::make($req->all(), $req->rules(), $req->messages());
        $req->withValidator($validator);

        $this->assertTrue($validator->fails(), 'Validation should fail when period discount > base price.');
        $this->assertTrue(
            $validator->errors()->has('period_discounts.3.discount_value'),
            'Error should be present for period_discounts.3.discount_value'
        );
        $this->assertStringContainsString(
            'نمی‌تواند بیشتر از تعرفه قیمت',
            $validator->errors()->first('period_discounts.3.discount_value')
        );

        // 2. Subscription period discount with 0 base price
        $reqZero = new \Modules\ResellerHub\App\Http\Requests\StorePackageRequest();
        $reqZero->merge([
            'name' => 'پکیج تست',
            'modules' => ['clients'],
            'price_type' => 'subscription',
            'period_prices' => ['3' => '0'],
            'period_discounts' => [
                '3' => [
                    'has_custom' => '1',
                    'discount_value' => '500000',
                ],
            ],
        ]);
        $validatorZero = \Illuminate\Support\Facades\Validator::make($reqZero->all(), $reqZero->rules(), $reqZero->messages());
        $reqZero->withValidator($validatorZero);
        $this->assertTrue($validatorZero->fails());
        $this->assertStringContainsString('تعرفه قیمت', $validatorZero->errors()->first('period_discounts.3.discount_value'));

        // 3. One-time discount > one_time_price
        $reqOneTime = new \Modules\ResellerHub\App\Http\Requests\StorePackageRequest();
        $reqOneTime->merge([
            'name' => 'پکیج تست',
            'modules' => ['clients'],
            'price_type' => 'one_time',
            'one_time_price' => '1000000',
            'discount_value' => '5000000',
        ]);
        $validatorOneTime = \Illuminate\Support\Facades\Validator::make($reqOneTime->all(), $reqOneTime->rules(), $reqOneTime->messages());
        $reqOneTime->withValidator($validatorOneTime);
        $this->assertTrue($validatorOneTime->fails());
        $this->assertTrue($validatorOneTime->errors()->has('discount_value'));
        $this->assertStringContainsString('تعرفه خرید یک‌باره', $validatorOneTime->errors()->first('discount_value'));
    }

    public function test_package_views_have_no_clutter_and_strict_ui_ux_controls(): void
    {
        $createPricing = $this->moduleView('user/packages/partials/create/card-pricing.blade.php');
        $editPricing = $this->moduleView('user/packages/partials/edit/card-pricing.blade.php');

        // Check that redundant clutter badge (profit_label / سود:) has been completely removed
        $this->assertStringNotContainsString('profit_label', $createPricing);
        $this->assertStringNotContainsString('profit_label', $editPricing);

        // Check that inline validation error feedback is present
        $this->assertStringContainsString('getPeriodDiscountError(row.months)', $createPricing);
        $this->assertStringContainsString('getPeriodDiscountError(row.months)', $editPricing);
        $this->assertStringContainsString('getGlobalDiscountError()', $createPricing);
        $this->assertStringContainsString('getGlobalDiscountError()', $editPricing);

        // Check that actions submit button disables when pricing has errors
        $createActions = $this->moduleView('user/packages/partials/create/actions.blade.php');
        $editActions = $this->moduleView('user/packages/partials/edit/actions.blade.php');
        $this->assertStringContainsString('hasPricingErrors()', $createActions);
        $this->assertStringContainsString('hasPricingErrors()', $editActions);

        // Check script has error calculation logic
        $createScript = $this->moduleView('user/packages/partials/create/script.blade.php');
        $this->assertStringContainsString('getPeriodDiscountError(months)', $createScript);
        $this->assertStringContainsString('getGlobalDiscountError()', $createScript);
        $this->assertStringContainsString('hasPricingErrors()', $createScript);
    }
}
