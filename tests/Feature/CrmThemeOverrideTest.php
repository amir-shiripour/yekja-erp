<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ThemeManager;
use App\Services\ViewOverrideResolver;
use Illuminate\Support\Facades\File;
use Modules\ContentForge\Entities\ContentSetting;
use Tests\TestCase;

class CrmThemeOverrideTest extends TestCase
{
    protected string $testTheme = 'test-crm-theme';
    protected string $testThemeDir = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->testThemeDir = resource_path("views/themes/{$this->testTheme}");
        if (File::isDirectory($this->testThemeDir)) {
            File::deleteDirectory($this->testThemeDir);
        }
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->testThemeDir)) {
            File::deleteDirectory($this->testThemeDir);
        }
        ContentSetting::setValue('crm.theme_key', 'default');
        ContentSetting::setValue('crm.enable_overrides', 'true');
        parent::tearDown();
    }

    public function test_crm_theme_key_resolves_from_content_settings(): void
    {
        ContentSetting::setValue('crm.theme_key', 'drfit');
        $themeManager = app(ThemeManager::class);
        $themeManager->clearCache();

        $this->assertEquals('drfit', $themeManager->getActiveCrmTheme());
    }

    public function test_view_override_resolver_identifies_crm_views_correctly(): void
    {
        $resolver = app(ViewOverrideResolver::class);

        $this->assertTrue($resolver->isCrmView('user.dashboard'));
        $this->assertTrue($resolver->isCrmView('user.partials.sidebar'));
        $this->assertTrue($resolver->isCrmView('layouts.user'));
        $this->assertTrue($resolver->isCrmView('layouts.auth'));
        $this->assertTrue($resolver->isCrmView('resellerhub::user.dashboard'));
        $this->assertTrue($resolver->isCrmView('contentforge::user.dashboard'));
        $this->assertFalse($resolver->isCrmView('contentforge::web.archive'));
        $this->assertFalse($resolver->isCrmView('themes.default.index'));
    }

    public function test_find_override_path_resolves_crm_view_override_when_file_exists(): void
    {
        $overrideDir = "{$this->testThemeDir}/overrides/user";
        File::makeDirectory($overrideDir, 0755, true);
        $overrideFile = "{$overrideDir}/dashboard.blade.php";
        File::put($overrideFile, '<div>CUSTOM_OVERRIDDEN_CRM_DASHBOARD</div>');

        ContentSetting::setValue('crm.theme_key', $this->testTheme);
        ContentSetting::setValue('crm.enable_overrides', 'true');

        $themeManager = app(ThemeManager::class);
        $themeManager->clearCache();

        $resolver = app(ViewOverrideResolver::class);
        $resolvedPath = $resolver->findOverridePath('user.dashboard');

        $this->assertNotNull($resolvedPath);
        $this->assertFileExists($resolvedPath);
        $this->assertStringContainsString('CUSTOM_OVERRIDDEN_CRM_DASHBOARD', File::get($resolvedPath));

        // Verify via Laravel's View Finder
        $foundByFinder = app('view.finder')->find('user.dashboard');
        $this->assertEquals(realpath($overrideFile), realpath($foundByFinder));
    }

    public function test_find_override_path_resolves_namespaced_module_crm_view_override(): void
    {
        $overrideDir = "{$this->testThemeDir}/overrides/resellerhub/user";
        File::makeDirectory($overrideDir, 0755, true);
        $overrideFile = "{$overrideDir}/dashboard.blade.php";
        File::put($overrideFile, '<div>CUSTOM_OVERRIDDEN_RESELLER_DASHBOARD</div>');

        ContentSetting::setValue('crm.theme_key', $this->testTheme);
        ContentSetting::setValue('crm.enable_overrides', 'true');

        $themeManager = app(ThemeManager::class);
        $themeManager->clearCache();

        $resolver = app(ViewOverrideResolver::class);
        $resolvedPath = $resolver->findOverridePath('resellerhub::user.dashboard');

        $this->assertNotNull($resolvedPath);
        $this->assertFileExists($resolvedPath);
        $this->assertStringContainsString('CUSTOM_OVERRIDDEN_RESELLER_DASHBOARD', File::get($resolvedPath));

        // Test with accounting module view
        $accOverrideDir = "{$this->testThemeDir}/overrides/accounting";
        File::makeDirectory($accOverrideDir, 0755, true);
        $accOverrideFile = "{$accOverrideDir}/index.blade.php";
        File::put($accOverrideFile, '<div>CUSTOM_ACCOUNTING_VIEW</div>');

        $accResolved = $resolver->findOverridePath('accounting::index');
        $this->assertNotNull($accResolved);
        $this->assertStringContainsString('CUSTOM_ACCOUNTING_VIEW', File::get($accResolved));
    }

    public function test_find_override_path_returns_null_when_overrides_are_disabled(): void
    {
        $overrideDir = "{$this->testThemeDir}/overrides/user";
        File::makeDirectory($overrideDir, 0755, true);
        $overrideFile = "{$overrideDir}/dashboard.blade.php";
        File::put($overrideFile, '<div>DISABLED_CUSTOM_DASHBOARD</div>');

        ContentSetting::setValue('crm.theme_key', $this->testTheme);
        ContentSetting::setValue('crm.enable_overrides', 'false');

        $themeManager = app(ThemeManager::class);
        $themeManager->clearCache();

        $resolver = app(ViewOverrideResolver::class);
        $resolvedPath = $resolver->findOverridePath('user.dashboard');

        $this->assertNull($resolvedPath);

        // Finder should return standard core view file
        $foundByFinder = app('view.finder')->find('user.dashboard');
        $this->assertStringNotContainsString('DISABLED_CUSTOM_DASHBOARD', File::get($foundByFinder));
    }

    public function test_theme_override_artisan_command_scaffolds_file_correctly(): void
    {
        $this->artisan('theme:override', [
            'view'    => 'user.partials.topbar',
            '--theme' => $this->testTheme,
            '--force' => true,
        ])->assertExitCode(0);

        $expectedPath = "{$this->testThemeDir}/overrides/user/partials/topbar.blade.php";
        $this->assertFileExists($expectedPath);

        $originalSource = app('view.finder')->find('user.partials.topbar');
        $this->assertEquals(File::get($originalSource), File::get($expectedPath));
    }

    public function test_livewire_content_settings_can_create_new_theme_from_ui(): void
    {
        $unique = uniqid('admin_', true);
        $admin = \App\Models\User::create([
            'name'     => 'Admin ' . $unique,
            'email'    => $unique . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        $testKey = 'test-ui-theme';
        $testDir = resource_path("views/themes/{$testKey}");

        if (File::isDirectory($testDir)) {
            File::deleteDirectory($testDir);
        }

        try {
            \Livewire\Livewire::test(\Modules\ContentForge\App\Livewire\Admin\ContentSettings::class)
                ->call('openCreateThemeModal')
                ->assertSet('showCreateThemeModal', true)
                ->set('newThemeKey', $testKey)
                ->set('newThemeName', 'پوسته تستی از فرانت')
                ->set('newThemeDescription', 'توضیحات تست')
                ->set('initialOverrideView', 'admin.users.edit')
                ->call('createTheme')
                ->assertHasNoErrors()
                ->assertSet('showCreateThemeModal', false);

            $this->assertDirectoryExists($testDir);
            $this->assertFileExists("{$testDir}/theme.json");
            $this->assertFileExists("{$testDir}/overrides/admin/users/edit.blade.php");

            // Verify active theme in ContentSetting
            $this->assertEquals($testKey, ContentSetting::getValue('crm.theme_key'));
        } finally {
            if (File::isDirectory($testDir)) {
                File::deleteDirectory($testDir);
            }
        }
    }

    public function test_theme_studio_route_and_override_management_works_cleanly(): void
    {
        $unique = uniqid('studio_', true);
        $admin = \App\Models\User::create([
            'name'     => 'Studio Admin ' . $unique,
            'email'    => $unique . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        // 1. Route loads cleanly
        $this->get(route('user.content.themes'))->assertStatus(200);
        $this->get(route('user.content.settings'))->assertStatus(200);
        $this->get('/settings')->assertStatus(200);

        $testKey = 'studio-test-theme';
        $testDir = resource_path("views/themes/{$testKey}");
        if (File::isDirectory($testDir)) {
            File::deleteDirectory($testDir);
        }

        try {
            // 2. Create CRM-only theme via ThemeStudio
            $component = \Livewire\Livewire::test(\Modules\ContentForge\App\Livewire\Admin\ThemeStudio::class)
                ->call('openCreateThemeModal')
                ->set('newThemeKey', $testKey)
                ->set('newThemeName', 'پوسته استودیو')
                ->set('initialOverrideView', 'none')
                ->call('createTheme')
                ->assertHasNoErrors();

            $this->assertDirectoryExists($testDir);
            $this->assertEquals($testKey, ContentSetting::getValue('crm.theme_key'));

            // 3. Guardrail: CRM-only theme cannot be set as public website theme
            $component->call('activateWebTheme', $testKey);
            $this->assertNotEquals($testKey, \Modules\Settings\Entities\Setting::where('key', 'app_theme')->value('value'));

            // 4. Add override file via UI
            $component->call('openOverridesModal', $testKey)
                ->assertSet('showOverridesModal', true)
                ->set('selectedOverrideTarget', 'admin.users.edit')
                ->call('addOverrideFile');

            $expectedOverride = "{$testDir}/overrides/admin/users/edit.blade.php";
            $this->assertFileExists($expectedOverride);

            // 5. Delete override file via UI
            $component->call('deleteOverrideFile', 'admin/users/edit.blade.php');
            $this->assertFileDoesNotExist($expectedOverride);

            // 6. Edit theme metadata in ThemeStudio
            $component->call('openEditThemeModal', $testKey)
                ->assertSet('showEditThemeModal', true)
                ->set('editThemeName', 'پوسته ویرایش‌شده')
                ->set('editThemeVersion', '2.0.0')
                ->call('updateTheme')
                ->assertSet('showEditThemeModal', false);

            $updatedMeta = json_decode(File::get("{$testDir}/theme.json"), true);
            $this->assertEquals('پوسته ویرایش‌شده', $updatedMeta['name']);
            $this->assertEquals('2.0.0', $updatedMeta['version']);

            // 7. Apply CRM theme from System Settings (#themes tab)
            $this->post(route('settings.update'), [
                'active_tab'           => 'themes',
                'crm_theme_key'        => $testKey,
                'crm_enable_overrides' => 'true',
            ])->assertRedirect();

            $this->assertEquals($testKey, ContentSetting::getValue('crm.theme_key'));
            $this->assertEquals('true', ContentSetting::getValue('crm.enable_overrides'));

            // 8. PermissionCatalog and RBAC guard check
            $this->assertEquals('contentforge', \App\Support\PermissionCatalog::detectGroup('content.themes.manage'));
            $this->assertEquals(
                'مدیریت استودیو پوسته‌ها و بازنویسی فرانت CRM',
                \App\Support\PermissionCatalog::translate('content.themes.manage')
            );

            $regularUser = \App\Models\User::create([
                'name'     => 'Regular User ' . $unique,
                'email'    => 'reg_' . $unique . '@example.com',
                'password' => bcrypt('password123'),
            ]);
            $this->actingAs($regularUser)
                ->get(route('user.content.themes'))
                ->assertStatus(403);
        } finally {
            if (File::isDirectory($testDir)) {
                File::deleteDirectory($testDir);
            }
        }
    }
}
