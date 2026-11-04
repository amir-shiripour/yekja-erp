<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\CustomUserField;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Modules\ResellerHub\App\Data\Onboarding\StepData;
use Modules\ResellerHub\App\Enums\OnboardingStepPreset;
use Modules\ResellerHub\App\Livewire\Dashboard\OnboardingWidget;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Regression guards for the ResellerHub admin UI.
 * @noinspection SqlResolve
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
            if (!empty($matches[1])) {
                array_push($used, ...$matches[1]);
            }
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
        $tenant = new Tenant();
        $tenant->status = Tenant::STATUS_ACTIVE;
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
        // 1. Check copy button in tenant subscription card (smooth checkmark + toast, no ugly black tooltip)
        $subCard = $this->moduleView('user/tenants/partials/show/subscription-card.blade.php');
        $this->assertStringContainsString('x-data="{ copied: false }"', $subCard);
        $this->assertStringNotContainsString('bg-gray-900 text-white', $subCard);
        $this->assertStringContainsString(':title="copied ? \'کپی شد!\' : \'کپی آدرس اختصاصی سامانه\'"', $subCard);

        // 2. Check wizard step 3 translation keys
        app()->setLocale('fa');
        $this->assertEquals('تولید خودکار (از تنظیمات)', __('resellerhub::messages.wizard.step3.invoice_number_label'));
        $this->assertEquals('ورود دستی', __('resellerhub::messages.wizard.step3.invoice_number_manual'));

        // 3. Check Persian validation strings
        $this->assertStringContainsString('حروف کوچک', __('validation.lowercase', ['attribute' => 'ایمیل']));
        $this->assertStringContainsString('عدد', __('validation.password.numbers', ['attribute' => 'رمز عبور']));
        $this->assertEquals('آدرس اختصاصی (ساب‌دامین)', __('validation.attributes.subdomain'));
        $this->assertEquals('پکیج انتخابی', __('validation.attributes.package_id'));

        // 3.5 Check tenants table renders dynamic package icon and color
        $tableBlade = $this->moduleView('user/dashboard/partials/tenants-table.blade.php');
        $this->assertStringContainsString('PackageColor::fromOrDefault', $tableBlade);
        $this->assertStringContainsString('PackageIcon::fromOrDefault', $tableBlade);
        $this->assertStringContainsString('$pkgColor->lightClass()', $tableBlade);
        $this->assertStringContainsString('$pkgIcon->svg(\'w-4 h-4\')', $tableBlade);

        // 4. Check dynamic discount percentage badge in package card-pricing
        $createPricing = $this->moduleView('user/packages/partials/create/card-pricing.blade.php');
        $this->assertStringContainsString('calcPeriodDiscountPercent(row.months)', $createPricing);
        $this->assertStringContainsString('٪<span ' . 'x-text="faNum(calcPeriodDiscountPercent(row.months))"></span> تخفیف', $createPricing);

        $editPricing = $this->moduleView('user/packages/partials/edit/card-pricing.blade.php');
        $this->assertStringContainsString('calcPeriodDiscountPercent(row.months)', $editPricing);
        $this->assertStringContainsString('٪<span ' . 'x-text="faNum(calcPeriodDiscountPercent(row.months))"></span> تخفیف', $editPricing);

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

    public function test_package_create_and_edit_have_step_by_step_wizard_with_stepper_and_preserved_logic(): void
    {
        // 1. Verify Stepper Partial
        $stepper = $this->moduleView('user/packages/partials/stepper.blade.php');
        $this->assertStringContainsString('role="progressbar"', $stepper);
        $this->assertStringContainsString('goToStep(1)', $stepper);
        $this->assertStringContainsString('goToStep(2)', $stepper);
        $this->assertStringContainsString('goToStep(3)', $stepper);
        $this->assertStringContainsString('مشخصات و هویت پکیج', $stepper);
        $this->assertStringContainsString('ماژول‌ها و دسترسی‌ها', $stepper);
        $this->assertStringContainsString('تعرفه‌گذاری و قیمت', $stepper);

        // 2. Verify Create View includes stepper and step containers
        $createBlade = $this->moduleView('user/packages/create.blade.php');
        $this->assertStringContainsString("user.packages.partials.stepper", $createBlade);
        $this->assertStringContainsString('x-show="currentStep === 1"', $createBlade);
        $this->assertStringContainsString('x-show="currentStep === 2"', $createBlade);
        $this->assertStringContainsString('x-show="currentStep === 3"', $createBlade);
        $this->assertStringContainsString("route('reseller.packages.store')", $createBlade);

        // 3. Verify Edit View includes stepper and step containers
        $editBlade = $this->moduleView('user/packages/edit.blade.php');
        $this->assertStringContainsString("user.packages.partials.stepper", $editBlade);
        $this->assertStringContainsString('x-show="currentStep === 1"', $editBlade);
        $this->assertStringContainsString('x-show="currentStep === 2"', $editBlade);
        $this->assertStringContainsString('x-show="currentStep === 3"', $editBlade);
        $this->assertStringContainsString("route('reseller.packages.update'", $editBlade);

        // 4. Verify step navigation methods in scripts
        $createScript = $this->moduleView('user/packages/partials/create/script.blade.php');
        $this->assertStringContainsString('currentStep:', $createScript);
        $this->assertStringContainsString('goToStep(step)', $createScript);
        $this->assertStringContainsString('nextStep()', $createScript);
        $this->assertStringContainsString('prevStep()', $createScript);

        $editScript = $this->moduleView('user/packages/partials/edit/script.blade.php');
        $this->assertStringContainsString('currentStep:', $editScript);
        $this->assertStringContainsString('goToStep(step)', $editScript);
        $this->assertStringContainsString('nextStep()', $editScript);
        $this->assertStringContainsString('prevStep()', $editScript);

        // 5. Verify action bars have step navigation
        $createActions = $this->moduleView('user/packages/partials/create/actions.blade.php');
        $this->assertStringContainsString('nextStep()', $createActions);
        $this->assertStringContainsString('prevStep()', $createActions);
        $this->assertStringContainsString('currentStep === 3', $createActions);

        $editActions = $this->moduleView('user/packages/partials/edit/actions.blade.php');
        $this->assertStringContainsString('nextStep()', $editActions);
        $this->assertStringContainsString('prevStep()', $editActions);
        $this->assertStringContainsString('currentStep === 3', $editActions);

        // 6. Verify duplicate buttons were completely removed from individual card partials
        foreach (['create', 'edit'] as $mode) {
            foreach (['card-basic', 'card-modules', 'card-pricing'] as $partial) {
                $cardContent = $this->moduleView("user/packages/partials/{$mode}/{$partial}.blade.php");
                $this->assertStringNotContainsString('nextStep()', $cardContent, "Duplicate nextStep found in {$mode}/{$partial}");
                $this->assertStringNotContainsString('prevStep()', $cardContent, "Duplicate prevStep found in {$mode}/{$partial}");
            }
        }
    }

    public function test_package_show_has_responsive_tab_navigation_with_deep_linking(): void
    {
        $showBlade = $this->moduleView('user/packages/show.blade.php');

        // Tab state and method declarations
        $this->assertStringContainsString('activeTab:', $showBlade);
        $this->assertStringContainsString('setTab(tab)', $showBlade);

        // Tab buttons with hash keys
        $this->assertStringContainsString("setTab('pricing')", $showBlade);
        $this->assertStringContainsString("setTab('modules')", $showBlade);
        $this->assertStringContainsString("setTab('tenants')", $showBlade);

        // Tab titles
        $this->assertStringContainsString('تعرفه‌گذاری و دوره‌ها', $showBlade);
        $this->assertStringContainsString('ماژول‌ها و امکانات', $showBlade);
        $this->assertStringContainsString('سامانه‌های متصل CRM', $showBlade);

        // Tab content containers
        $this->assertStringContainsString('x-show="activeTab === \'pricing\'"', $showBlade);
        $this->assertStringContainsString('x-show="activeTab === \'modules\'"', $showBlade);
        $this->assertStringContainsString('x-show="activeTab === \'tenants\'"', $showBlade);
    }

    public function test_database_export_is_integrated_into_settings_tab_and_super_admin_only(): void
    {
        // 1. Verify standalone menu item was removed
        $menuPath = module_path('ResellerHub', 'resources/menu.php');
        $menuContent = File::get($menuPath);
        $this->assertStringNotContainsString("'route' => 'reseller.tenants.export'", $menuContent, 'Database export must no longer exist as a standalone menu item.');

        // 2. Verify settings index tab definitions and super-admin protection
        $settingsBlade = $this->moduleView('user/settings/index.blade.php');
        $this->assertStringContainsString("if (\$isSuperAdmin)", $settingsBlade);
        $this->assertStringContainsString("\$tabs['database'] = ['label' => 'استخراج دیتابیس', 'icon' => 'database'];", $settingsBlade);
        $this->assertStringContainsString("if (\$initialTab === 'database' && !\$isSuperAdmin)", $settingsBlade);
        $this->assertStringContainsString("activeTab !== 'database'", $settingsBlade);

        // 3. Verify tab-database is rendered OUTSIDE the main settings form
        $formClose = strpos($settingsBlade, '</form>');
        $databaseInclude = strpos($settingsBlade, "user.settings.tabs.tab-database");
        $this->assertNotFalse($databaseInclude, 'user.settings.tabs.tab-database is not included.');
        $this->assertGreaterThan(
            $formClose,
            $databaseInclude,
            'tab-database owns its own search/filter form and MUST be rendered OUTSIDE the main settings form.'
        );

        // 4. Verify tab-database.blade.php exists and closes its forms cleanly
        $tabBlade = $this->moduleView('user/settings/tabs/tab-database.blade.php');
        $this->assertStringContainsString('role="tabpanel"', $tabBlade);
        $this->assertStringContainsString('id="tabpanel-database"', $tabBlade);
        $this->assertStringContainsString('tenantExportTabManager()', $tabBlade);
        $this->assertSame(
            substr_count($tabBlade, '<form'),
            substr_count($tabBlade, '</form>'),
            'Every <form> in tab-database must be properly closed.'
        );

        // 5. Verify settings script validTabs includes database
        $scriptBlade = $this->moduleView('user/settings/partials/settings-script.blade.php');
        $this->assertStringContainsString("'database'", $scriptBlade);

        // 6. Verify standalone view export.blade.php was completely removed
        $this->assertFileDoesNotExist(
            module_path('ResellerHub', 'resources/views/user/tenants/export.blade.php'),
            'Standalone export.blade.php must be deleted since it was moved into user/settings/tabs/tab-database.blade.php'
        );
    }

    public function test_database_export_access_control_via_http(): void
    {
        $superAdmin = User::role('super-admin')->first();
        if ($superAdmin) {
            $response = $this->actingAs($superAdmin)->get(route('reseller.settings.index', ['tab' => 'database']));
            $response->assertOk();
            $response->assertSee('استخراج دیتابیس');
            $response->assertSee('SQL Dump', false);
            $response->assertSee('tabpanel-database');

            // Legacy route redirects to settings tab
            $redirectResponse = $this->actingAs($superAdmin)->get(route('reseller.tenants.export'));
            $redirectResponse->assertRedirect(route('reseller.settings.index', ['tab' => 'database']));
        }

        // Test non-superadmin user
        $regularUser = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super-admin');
        })->first();

        if ($regularUser) {
            $response = $this->actingAs($regularUser)->get(route('reseller.tenants.export'));
            $this->assertContains($response->status(), [403, 302], 'Non-superadmin must be denied or redirected away from tenants export.');
        }
    }

    public function test_optional_tour_reminder_settings_and_postpone_controls(): void
    {
        // 1. Verify tab-tour has reminder settings inputs and UI
        $tourBlade = $this->moduleView('user/settings/tabs/tab-tour.blade.php');
        $this->assertStringContainsString('onboarding_tour_optional_reminder_enabled', $tourBlade);
        $this->assertStringContainsString('onboarding_tour_optional_reminder_interval_days', $tourBlade);
        $this->assertStringContainsString('onboarding_tour_optional_reminder_mode', $tourBlade);
        $this->assertStringContainsString('مدیریت یادآوری و نمایش مراحل اختیاری تور', $tourBlade);
        $this->assertStringContainsString('فاصله زمانی تکرار یادآوری', $tourBlade);

        // 2. Verify onboarding-widget has postpone CTA and button
        $widgetBlade = $this->moduleView('livewire/dashboard/onboarding-widget.blade.php');
        $this->assertStringContainsString('postponeOptionalTour', $widgetBlade);
        $this->assertStringContainsString('پر کردن بعداً و ورود به پیشخوان', $widgetBlade);

        // 3. Verify settings script initializes the reminder config
        $scriptBlade = $this->moduleView('user/settings/partials/settings-script.blade.php');
        $this->assertStringContainsString('tourOptionalReminderEnabled', $scriptBlade);
        $this->assertStringContainsString('tourOptionalReminderIntervalDays', $scriptBlade);
        $this->assertStringContainsString('tourOptionalReminderMode', $scriptBlade);
    }

    public function test_optional_tour_reminder_and_snooze_service_logic(): void
    {
        $stateService = app(\Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService::class);
        $tenant = Tenant::first();

        if ($tenant) {
            // Ensure tenant is in an active (in_progress) tour state for testing snooze
            $tenant->setOnboardingStatus(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::IN_PROGRESS)->save();
            $settings = $tenant->settings ?? [];
            unset($settings['onboarding_completed_at']);
            $tenant->settings = $settings;
            $tenant->save();

            // Test 1: When tenant has postponed_at within interval (e.g. 1 hour ago)
            $tenant->settings = array_merge($tenant->settings ?? [], [
                'onboarding_postponed_at' => now()->subHour()->toIso8601String(),
            ]);
            $tenant->save();

            // Set reminder interval to 3 days (72 hours)
            \Modules\ResellerHub\App\Models\ResellerSetting::set(
                $tenant->reseller_id ?: 1,
                \Modules\ResellerHub\App\Enums\ResellerSettingKey::OnboardingTourOptionalReminderEnabled,
                1
            );
            \Modules\ResellerHub\App\Models\ResellerSetting::set(
                $tenant->reseller_id ?: 1,
                \Modules\ResellerHub\App\Enums\ResellerSettingKey::OnboardingTourOptionalReminderIntervalDays,
                3
            );

            // Within interval: must be dismissed/postponed (true)
            $this->assertTrue(
                $stateService->isTourDismissedOrPostponed($tenant),
                'Tour must remain postponed while within reminder interval.'
            );

            // Test 2: When postponed_at was 4 days ago (interval elapsed)
            $tenant->settings = array_merge($tenant->settings ?? [], [
                'onboarding_postponed_at' => now()->subDays(4)->toIso8601String(),
            ]);
            $tenant->save();

            // Elapsed: reminder should trigger! (false)
            $this->assertFalse(
                $stateService->isTourDismissedOrPostponed($tenant),
                'Tour must trigger re-prompt when reminder interval has elapsed.'
            );

            // Test 3: When reminder is disabled completely
            \Modules\ResellerHub\App\Models\ResellerSetting::set(
                $tenant->reseller_id ?: 1,
                \Modules\ResellerHub\App\Enums\ResellerSettingKey::OnboardingTourOptionalReminderEnabled,
                0
            );

            $this->assertTrue(
                $stateService->isTourDismissedOrPostponed($tenant),
                'Tour must permanently stay dismissed when optional reminders are disabled.'
            );
        }
    }

    public function test_onboarding_tour_status_lifecycle_and_update_immunity(): void
    {
        $tenant = Tenant::first();
        $this->assertNotNull($tenant);

        // 1. Initial/Progress lifecycle
        $tenant->setOnboardingStatus(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::IN_PROGRESS)->save();
        $this->assertEquals(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::IN_PROGRESS, $tenant->getOnboardingStatus());
        $this->assertFalse($tenant->isOnboardingCompleted());

        // 2. Postponed lifecycle
        $tenant->setOnboardingStatus(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::POSTPONED)->save();
        $this->assertEquals(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::POSTPONED, $tenant->getOnboardingStatus());
        $this->assertFalse($tenant->isOnboardingCompleted());

        // 3. Completed lifecycle
        $tenant->setOnboardingStatus(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::COMPLETED)->save();
        $this->assertEquals(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::COMPLETED, $tenant->getOnboardingStatus());
        $this->assertTrue($tenant->isOnboardingCompleted());
        $this->assertNotNull($tenant->getOnboardingCompletedAt());

        $stateService = app(\Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService::class);
        $stateService->flushCache($tenant->id);

        // 4. Completed tenant is immune to newly added tour steps
        $progress = $stateService->getTenantProgress($tenant);
        $this->assertTrue($progress['is_finished']);
        $this->assertEquals(100, $progress['percentage']);
        $this->assertEquals(\Modules\ResellerHub\App\Enums\OnboardingTourStatus::COMPLETED->value, $progress['status']);

        // 5. Verify dashboard views hide tour widget completely when completed or postponed
        $dashboardBlade = file_get_contents(resource_path('views/user/dashboard.blade.php'));
        $this->assertStringContainsString('$shouldRenderTourWidget', $dashboardBlade);
        $this->assertStringContainsString('@if($shouldRenderTourWidget)', $dashboardBlade);
        $this->assertStringContainsString('isOnboardingCompleted()', $dashboardBlade);

        $widgetBlade = $this->moduleView('livewire/dashboard/onboarding-widget.blade.php');
        $this->assertStringContainsString('$isCompleted', $widgetBlade);
        $this->assertStringContainsString('$isTourDismissedOrPostponed', $widgetBlade);

        // 6. Verify Tab Tour toggle switch has proper sliding knob in RTL
        $tourBlade = $this->moduleView('user/settings/tabs/tab-tour.blade.php');
        $this->assertStringContainsString('peer-checked:-translate-x-6', $tourBlade);
    }

    public function test_custom_fields_in_tour_steps_and_categorized_profile_persistence(): void
    {
        // 1. Verify CustomUserField category resolution
        $this->assertEquals(
            CustomUserField::CATEGORY_CONTACT,
            CustomUserField::resolveCategoryGroup('office_fax')
        );
        $this->assertEquals(
            CustomUserField::CATEGORY_CONTACT,
            CustomUserField::resolveCategoryGroup('postal_code')
        );
        $this->assertEquals(
            CustomUserField::CATEGORY_IDENTITY,
            CustomUserField::resolveCategoryGroup('melli_code')
        );
        $this->assertEquals(
            CustomUserField::CATEGORY_BUSINESS,
            CustomUserField::resolveCategoryGroup('monthly_target')
        );

        // 2. Verify OnboardingStepPreset for custom fields
        $officePreset = OnboardingStepPreset::OFFICE_DETAILS;
        $this->assertEquals('custom_fields', $officePreset->actionType());
        $this->assertEquals('contact', $officePreset->fieldGroup());
        $this->assertNotEmpty($officePreset->customFields());
        $this->assertEquals('fax', $officePreset->customFields()[0]['name']);

        $targetPreset = OnboardingStepPreset::BUSINESS_TARGET;
        $this->assertEquals('custom_fields', $targetPreset->actionType());
        $this->assertEquals('business', $targetPreset->fieldGroup());
        $this->assertEquals('tenant', $targetPreset->targetScope());

        // 3. Verify StepData can carry custom fields and field groups
        $stepData = new StepData(
            slug: 'custom_fax_step',
            journey: 'crm_setup',
            title: 'اطلاعات فکس',
            description: 'توضیحات فکس',
            routeAction: 'user.dashboard',
            actionType: 'custom_fields',
            customFields: [['name' => 'fax', 'label' => 'فکس', 'type' => 'tel', 'required' => true]],
            fieldGroup: 'contact',
        );
        $this->assertEquals('custom_fields', $stepData->actionType);
        $this->assertEquals('contact', $stepData->fieldGroup);
        $this->assertCount(1, $stepData->customFields);

        // 4. Verify OnboardingWidget modal and saving logic
        $tenant = Tenant::first();
        $this->assertNotNull($tenant);
        $user = $tenant->owner ?? User::first();
        $this->assertNotNull($user);

        // Mock Livewire component execution
        $widget = new OnboardingWidget();
        $widget->activeCustomStepSlug = 'custom_office_step';
        $widget->activeCustomStepTitle = 'مشخصات اداری';
        $widget->activeCustomStepFields = [
            ['name' => 'test_fax', 'label' => 'شماره فکس تست', 'type' => 'tel', 'required' => false, 'target_scope' => 'user'],
            ['name' => 'test_postal', 'label' => 'کد پستی تست', 'type' => 'number', 'required' => false, 'target_scope' => 'tenant'],
        ];
        $widget->customFieldsForm = [
            'test_fax' => '021-88776655',
            'test_postal' => '1998877665',
        ];

        // Ensure session/auth context
        $this->actingAs($user);
        session(['tenant_id' => $tenant->id]);

        $widget->saveCustomFields();

        // 5. Verify UserCustomValue was saved for user
        $savedFax = \App\Models\UserCustomValue::where('user_id', $user->id)
            ->where('field_name', 'test_fax')
            ->value('value');
        $this->assertEquals('021-88776655', $savedFax);

        // 6. Verify CustomUserField record was automatically created with category group
        $cuf = CustomUserField::where('field_name', 'test_fax')->first();
        $this->assertNotNull($cuf);
        $this->assertEquals(CustomUserField::CATEGORY_CONTACT, $cuf->category_group);
        $this->assertEquals(CustomUserField::ENTITY_SUB_ACCOUNT, $cuf->entity_type);

        // 7. Verify tenant settings saved tenant-scoped custom fields
        $tenant->refresh();
        $this->assertEquals('1998877665', data_get($tenant->settings, 'custom_fields.test_postal'));

        // 8. Verify profile blade has categorized groups markup and synced with messages.php
        $profileBlade = file_get_contents(resource_path('views/profile/update-profile-information-form.blade.php'));
        $this->assertStringContainsString('groupedFields', $profileBlade);
        $this->assertStringContainsString('resolveCategoryGroup', $profileBlade);
        $this->assertStringContainsString('resellerhub::messages.profile.categories.contact_title', $profileBlade);
        $this->assertStringContainsString('resellerhub::messages.profile.categories.identity_title', $profileBlade);
        $this->assertStringContainsString('resellerhub::messages.profile.categories.business_title', $profileBlade);
        $this->assertEquals('اطلاعات تماس، فکس و نشانی اداری', __('resellerhub::messages.profile.categories.contact_title'));
        $this->assertEquals('مشخصات هویتی و حقوقی', __('resellerhub::messages.profile.categories.identity_title'));
        $this->assertEquals('اهداف و اطلاعات کسب‌وکار', __('resellerhub::messages.profile.categories.business_title'));

        // 9. Verify tab-tour has custom_fields action type and extended field builder with all 12 types and presets catalog
        $tourBlade = $this->moduleView('user/settings/tabs/tab-tour.blade.php');
        $this->assertStringContainsString('action_type = \'custom_fields\'', $tourBlade);
        $this->assertStringContainsString('فیلدهای سفارشی', $tourBlade);
        $this->assertStringContainsString('addCustomFieldToNewStep', $tourBlade);
        $this->assertStringContainsString('newStep.field_group', $tourBlade);
        $this->assertStringContainsString('newStep.target_scope', $tourBlade);
        $this->assertStringContainsString('fieldPresetsCatalog', $tourBlade);
        $this->assertStringContainsString('الگوهای آماده و فیلدهای قابل انتخاب', $tourBlade);
        $this->assertStringContainsString('value="currency"', $tourBlade);
        $this->assertStringContainsString('value="mobile"', $tourBlade);
        $this->assertStringContainsString('value="select"', $tourBlade);
        $this->assertStringContainsString('value="checkbox"', $tourBlade);
        $this->assertStringContainsString('value="time"', $tourBlade);
        $this->assertStringContainsString('value="url"', $tourBlade);
        $this->assertStringContainsString('options_str', $tourBlade);

        // 10. Verify onboarding-widget has custom fields modal and specialized input renderers
        $widgetBlade = $this->moduleView('livewire/dashboard/onboarding-widget.blade.php');
        $this->assertStringContainsString('showCustomFieldsModal', $widgetBlade);
        $this->assertStringContainsString('openCustomFieldsModal', $widgetBlade);
        $this->assertStringContainsString('saveCustomFields', $widgetBlade);
        $this->assertStringContainsString('$fType === \'currency\'', $widgetBlade);
        $this->assertStringContainsString('$fType === \'checkbox\'', $widgetBlade);
        $this->assertStringContainsString('$fType === \'select\'', $widgetBlade);
        $this->assertStringContainsString('$fType === \'time\'', $widgetBlade);
        $this->assertStringContainsString('$fType === \'mobile\'', $widgetBlade);
        $this->assertStringContainsString('$fType === \'url\'', $widgetBlade);
    }
}
