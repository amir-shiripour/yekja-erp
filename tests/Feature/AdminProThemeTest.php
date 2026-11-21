<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\ThemeManager;
use App\Services\ViewOverrideResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Modules\ContentForge\App\Services\ThemeResolver;
use Modules\ContentForge\Entities\ContentSetting;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProThemeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        View::share('errors', new \Illuminate\Support\ViewErrorBag());
        ContentSetting::setValue('crm.theme_key', 'admin-pro');
        ContentSetting::setValue('crm.enable_overrides', 'true');
        app(ThemeManager::class)->clearCache();
    }

    protected function tearDown(): void
    {
        ContentSetting::setValue('crm.theme_key', 'default');
        ContentSetting::setValue('crm.enable_overrides', 'true');
        app(ThemeManager::class)->clearCache();
        parent::tearDown();
    }

    public function test_admin_pro_theme_exists_and_has_all_overrides(): void
    {
        $themes = app(ThemeResolver::class)->getAvailableThemes();

        $this->assertArrayHasKey('admin-pro', $themes);
        $theme = $themes['admin-pro'];

        $this->assertEquals('مدیریت پیشرفته (Admin Pro)', $theme['name']);
        $this->assertTrue($theme['has_overrides']);
        $this->assertFalse($theme['has_web_index']);
        $this->assertEquals(12, $theme['crm_overrides_count']);

        $expectedFiles = [
            'layouts/user.blade.php',
            'admin/users/index.blade.php',
            'admin/users/create.blade.php',
            'admin/users/edit.blade.php',
            'admin/roles/index.blade.php',
            'admin/roles/create.blade.php',
            'admin/roles/edit.blade.php',
            'admin/custom_fields/index.blade.php',
            'admin/custom_fields/create.blade.php',
            'admin/custom_fields/sub_account_create.blade.php',
            'admin/custom_fields/edit.blade.php',
            'admin/custom_fields/sub_account_edit.blade.php',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertContains($file, $theme['overridden_files']);
        }
    }

    public function test_view_override_resolver_finds_admin_pro_overrides(): void
    {
        $resolver = app(ViewOverrideResolver::class);

        $indexOverride = $resolver->findOverridePath('admin.users.index');
        $this->assertNotNull($indexOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $indexOverride));
        $this->assertFileExists($indexOverride);

        $createOverride = $resolver->findOverridePath('admin.users.create');
        $this->assertNotNull($createOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $createOverride));
        $this->assertFileExists($createOverride);

        $editOverride = $resolver->findOverridePath('admin.users.edit');
        $this->assertNotNull($editOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $editOverride));
        $this->assertFileExists($editOverride);

        $rolesIndexOverride = $resolver->findOverridePath('admin.roles.index');
        $this->assertNotNull($rolesIndexOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $rolesIndexOverride));
        $this->assertFileExists($rolesIndexOverride);

        $layoutOverride = $resolver->findOverridePath('layouts.user');
        $this->assertNotNull($layoutOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $layoutOverride));
        $this->assertFileExists($layoutOverride);

        $rolesCreateOverride = $resolver->findOverridePath('admin.roles.create');
        $this->assertNotNull($rolesCreateOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $rolesCreateOverride));
        $this->assertFileExists($rolesCreateOverride);

        $rolesEditOverride = $resolver->findOverridePath('admin.roles.edit');
        $this->assertNotNull($rolesEditOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $rolesEditOverride));
        $this->assertFileExists($rolesEditOverride);

        $cfIndexOverride = $resolver->findOverridePath('admin.custom_fields.index');
        $this->assertNotNull($cfIndexOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $cfIndexOverride));
        $this->assertFileExists($cfIndexOverride);

        $cfCreateOverride = $resolver->findOverridePath('admin.custom_fields.create');
        $this->assertNotNull($cfCreateOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $cfCreateOverride));
        $this->assertFileExists($cfCreateOverride);

        $cfSubCreateOverride = $resolver->findOverridePath('admin.custom_fields.sub_account_create');
        $this->assertNotNull($cfSubCreateOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $cfSubCreateOverride));
        $this->assertFileExists($cfSubCreateOverride);
    }

    public function test_users_index_override_renders_with_ui_components(): void
    {
        $user = new User([
            'name' => 'تست تستی',
            'email' => 'test@example.com',
            'mobile' => '09120000000',
        ]);
        $user->id = 999;
        $user->setRelation('roles', collect());
        $this->actingAs($user);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$user]),
            1,
            15,
            1,
            ['path' => '/admin/users']
        );

        $rendered = View::make('admin.users.index', [
            'users' => $paginator,
            'pageTitle' => 'مدیریت کاربران',
        ])->render();

        $this->assertStringContainsString('کاربر فعال', $rendered);
        $this->assertStringContainsString('تست تستی', $rendered);
        $this->assertStringContainsString('test@example.com', $rendered);
    }

    public function test_users_edit_override_renders_with_ui_components(): void
    {
        $user = new User([
            'name' => 'کاربر آزمایشی',
            'email' => 'edit-user@example.com',
            'mobile' => '09121111111',
        ]);
        $user->id = 555;
        $user->setRelation('roles', collect());
        $user->setRelation('superiors', collect());
        $this->actingAs($user);

        $rendered = View::make('admin.users.edit', [
            'user' => $user,
            'roles' => [],
            'pageTitle' => 'ویرایش کاربر',
        ])->render();

        $this->assertStringContainsString('ویرایش کاربر', $rendered);
        $this->assertStringContainsString('edit-user@example.com', $rendered);
        $this->assertStringContainsString('تنظیمات امنیتی و رمز عبور', $rendered);
    }

    public function test_roles_index_override_renders_with_ui_components(): void
    {
        $user = new User(['name' => 'ادمین تست']);
        $user->id = 1;
        $this->actingAs($user);

        $role = new Role(['name' => 'sales-manager', 'display_name' => 'مدیر فروش', 'guard_name' => 'web']);
        $role->id = 1;

        $rendered = View::make('admin.roles.index', [
            'roles' => collect([$role]),
            'roleUserCounts' => ['sales-manager' => 3],
        ])->render();

        $this->assertStringContainsString('مدیریت نقش‌ها و دسترسی‌ها', $rendered);
        $this->assertStringContainsString('مدیر فروش', $rendered);
        $this->assertStringContainsString('3 کاربر', $rendered);
    }

    public function test_roles_create_and_edit_overrides_render(): void
    {
        $user = new User(['name' => 'ادمین تست']);
        $user->id = 1;
        $this->actingAs($user);

        $permissionGroups = [
            'core' => [
                'title' => 'هسته سیستم',
                'items' => [
                    ['name' => 'users.view', 'label' => 'مشاهده کاربران'],
                    ['name' => 'users.create', 'label' => 'ایجاد کاربر'],
                ],
            ],
        ];

        $renderedCreate = View::make('admin.roles.create', [
            'widgets' => [],
            'permissionGroups' => $permissionGroups,
        ])->render();

        $this->assertStringContainsString('تعریف نقش سازمانی جدید', $renderedCreate);
        $this->assertStringContainsString('میزان پوشش دسترسی‌های این نقش', $renderedCreate);

        $role = new Role(['name' => 'custom-role', 'display_name' => 'نقش سفارشی', 'guard_name' => 'web']);
        $role->id = 2;

        $renderedEdit = View::make('admin.roles.edit', [
            'role' => $role,
            'widgets' => [],
            'roleWidgets' => [],
            'rolePermissions' => ['users.view'],
            'permissionGroups' => $permissionGroups,
        ])->render();

        $this->assertStringContainsString('ویرایش مشخصات و دسترسی‌های نقش', $renderedEdit);
        $this->assertStringContainsString('نقش سفارشی', $renderedEdit);
    }

    public function test_users_create_override_renders_with_phone_and_security_inputs(): void
    {
        $user = new User(['name' => 'ادمین تست']);
        $user->id = 1;
        $this->actingAs($user);

        $rendered = View::make('admin.users.create', [
            'roles' => ['admin' => 'مدیر سیستم'],
            'customFieldsByRole' => collect(),
            'selectedRole' => 'admin',
        ])->render();

        $this->assertStringContainsString('ایجاد کاربر جدید', $rendered);
        $this->assertStringContainsString('امنیت حساب و کلمه عبور', $rendered);
        $this->assertStringContainsString('+98', $rendered);
        $this->assertStringContainsString('912 345 6789', $rendered);
        $this->assertStringContainsString('کد ملی کاربر', $rendered);
    }

    public function test_custom_fields_index_override_renders_properly(): void
    {
        $user = new User(['name' => 'ادمین تست']);
        $user->id = 1;
        $this->actingAs($user);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            15,
            1,
            ['path' => '/admin/custom-fields']
        );

        $rendered = View::make('admin.custom_fields.index', [
            'fields' => $paginator,
            'tab' => 'user',
            'userFieldsCount' => 0,
            'subAccountFieldsCount' => 0,
        ])->render();

        $this->assertStringContainsString('فیلدهای سفارشی و احراز هویت', $rendered);
        $this->assertStringContainsString('فیلدهای کاربران و پرسنل', $rendered);
    }
}
