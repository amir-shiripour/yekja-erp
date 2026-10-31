<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\File;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Regression guards for the ResellerHub admin UI.
 *
 * These are source-level assertions on purpose: each one encodes a real defect
 * that shipped and was invisible to the runtime test suite.
 *
 *  1. The settings form used to nest `<form>` elements (the tenancy tab).
 *     Browsers auto-close the outer form at the first nested one, which left
 *     the "ذخیره تنظیمات" button OUTSIDE the form — the click did nothing and
 *     no request was ever sent.
 *  2. The sub-accounts table used to show both the business name and the
 *     "owner / manager" name, duplicating a concept that no longer exists
 *     after the owner-id logic was removed.
 *  3. Sub-accounts used to allocate a "max CRMs" sales quota per buyer.
 *     That allocation logic was removed and must not creep back in.
 *  4. The global notice modal must stay minimal: no coloured accent bar and no
 *     bordered message box.
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
}
