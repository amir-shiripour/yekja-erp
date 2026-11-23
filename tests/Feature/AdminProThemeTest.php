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
    private ?string $originalThemeKey = null;
    private ?string $originalEnableOverrides = null;

    protected function setUp(): void
    {
        parent::setUp();
        View::share('errors', new \Illuminate\Support\ViewErrorBag());
        $this->originalThemeKey = (string)ContentSetting::getValue('crm.theme_key', 'admin-pro');
        $this->originalEnableOverrides = (string)ContentSetting::getValue('crm.enable_overrides', 'true');

        ContentSetting::setValue('crm.theme_key', 'admin-pro');
        ContentSetting::setValue('crm.enable_overrides', 'true');
        app(ThemeManager::class)->clearCache();
    }

    protected function tearDown(): void
    {
        ContentSetting::setValue('crm.theme_key', $this->originalThemeKey ?: 'admin-pro');
        ContentSetting::setValue('crm.enable_overrides', $this->originalEnableOverrides ?: 'true');
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
        $this->assertEquals(20, $theme['crm_overrides_count']);

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
            'followups/user/followups/index.blade.php',
            'followups/user/followups/show.blade.php',
            'followups/user/followups/create.blade.php',
            'followups/user/followups/edit.blade.php',
            'tasks/user/tasks/index.blade.php',
            'tasks/user/tasks/show.blade.php',
            'tasks/user/tasks/create.blade.php',
            'tasks/user/tasks/edit.blade.php',
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

        $followupOverride = $resolver->findOverridePath('followups::user.followups.index');
        $this->assertNotNull($followupOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $followupOverride));
        $this->assertFileExists($followupOverride);

        $taskOverride = $resolver->findOverridePath('tasks::user.tasks.index');
        $this->assertNotNull($taskOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $taskOverride));
        $this->assertFileExists($taskOverride);

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

    public function test_followups_index_and_create_overrides_render_with_ui_components(): void
    {
        $user = new User(['name' => 'کاربر پیگیری']);
        $user->id = 1;
        $this->actingAs($user);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            15,
            1,
            ['path' => '/user/followups']
        );

        $renderedIndex = View::make('followups::user.followups.index', [
            'followups' => $paginator,
            'statuses' => ['TODO' => 'در انتظار', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'types' => ['FOLLOW_UP' => 'پیگیری عادی'],
            'users' => collect(),
        ])->render();

        $this->assertStringContainsString('لیست پیگیری‌ها', $renderedIndex);
        $this->assertStringContainsString('هیچ پیگیری‌ای یافت نشد', $renderedIndex);

        $renderedCreate = View::make('followups::user.followups.create', [
            'statuses' => ['TODO' => 'در انتظار'],
            'priorities' => ['MEDIUM' => 'متوسط'],
            'users' => collect(),
            'clients' => collect(),
            'canAssign' => true,
        ])->render();

        $this->assertStringContainsString('ثبت پیگیری جدید', $renderedCreate);
        $this->assertStringContainsString('اطلاعات پایه پیگیری', $renderedCreate);
        $this->assertStringContainsString('کاربر مسئول انجام', $renderedCreate);
        $this->assertStringContainsString('مشتری مرتبط', $renderedCreate);
        $this->assertStringNotContainsString('نکات و الزامات ثبت', $renderedCreate);

        $fakeTask = new \Modules\Tasks\Entities\Task([
            'title' => 'پیگیری تست',
            'status' => 'TODO',
            'priority' => 'MEDIUM',
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
        ]);
        $fakeTask->id = 999;
        $fakeTask->created_at = now();

        $renderedEdit = View::make('followups::user.followups.edit', [
            'followUp' => $fakeTask,
            'statuses' => ['TODO' => 'در انتظار', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'users' => collect(),
            'clients' => collect(),
            'canAssign' => true,
        ])->render();

        $this->assertStringContainsString('ویرایش پیگیری شماره', $renderedEdit);
        $this->assertStringContainsString('اطلاعات پایه پیگیری', $renderedEdit);
        $this->assertStringContainsString('کاربر مسئول انجام', $renderedEdit);
        $this->assertStringContainsString('مشتری مرتبط', $renderedEdit);
        $this->assertStringNotContainsString('نکات و الزامات ثبت', $renderedEdit);
    }

    public function test_tasks_index_and_create_overrides_render_with_ui_components(): void
    {
        $user = new User(['name' => 'کاربر وظایف']);
        $user->id = 1;
        $this->actingAs($user);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            15,
            1,
            ['path' => '/user/tasks']
        );

        $renderedIndex = View::make('tasks::user.tasks.index', [
            'tasks' => $paginator,
            'statuses' => ['TODO' => 'در صف انجام', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'types' => ['GENERAL' => 'عمومی'],
            'users' => collect(),
        ])->render();

        $this->assertStringContainsString('لیست وظایف', $renderedIndex);
        $this->assertStringContainsString('اعمال فیلتر', $renderedIndex);
        $this->assertStringContainsString('هیچ وظیفه‌ای یافت نشد', $renderedIndex);

        // تست رندر تسک با دکمه حذف Devigner
        \Illuminate\Support\Facades\Gate::define('tasks.delete', fn() => true);
        $taskItem = new \Modules\Tasks\Entities\Task([
            'title' => 'تسک تست دکمه حذف',
            'status' => 'TODO',
            'priority' => 'HIGH',
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_GENERAL,
        ]);
        $taskItem->id = 777;
        $paginatorWithItem = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$taskItem]),
            1,
            15,
            1,
            ['path' => '/user/tasks']
        );
        $renderedIndexWithItem = View::make('tasks::user.tasks.index', [
            'tasks' => $paginatorWithItem,
            'statuses' => ['TODO' => 'در صف انجام'],
            'priorities' => ['HIGH' => 'بالا'],
            'types' => ['GENERAL' => 'عمومی'],
            'users' => collect(),
        ])->render();
        $this->assertStringContainsString('confirmDelete', $renderedIndexWithItem);
        $this->assertStringContainsString('aria-label="حذف"', $renderedIndexWithItem);

        $renderedCreate = View::make('tasks::user.tasks.create', [
            'statuses' => ['TODO' => 'در صف انجام'],
            'priorities' => ['MEDIUM' => 'متوسط'],
            'types' => ['GENERAL' => 'عمومی'],
            'users' => collect(),
            'roles' => collect(),
            'clients' => collect(),
            'clientStatuses' => collect(),
            'canAssign' => true,
        ])->render();

        $this->assertStringContainsString('ثبت وظیفه جدید', $renderedCreate);
        $this->assertStringContainsString('اطلاعات پایه وظیفه', $renderedCreate);
        $this->assertStringContainsString('کاربر مسئول انجام', $renderedCreate);
        $this->assertStringContainsString('مشتری مرتبط', $renderedCreate);
        $this->assertStringContainsString('Menu Dock', $renderedCreate);
        $this->assertStringContainsString('role="radiogroup"', $renderedCreate);
        $this->assertStringContainsString('name="assignee_mode"', $renderedCreate);
        $this->assertStringContainsString('name="related_target"', $renderedCreate);
        $this->assertStringContainsString('window.uiSearchableSelect', $renderedCreate);
        $this->assertStringContainsString('name="assignee_user_ids[]"', $renderedCreate);

        $fakeTask = new \Modules\Tasks\Entities\Task([
            'title' => 'وظیفه تست',
            'status' => 'TODO',
            'priority' => 'MEDIUM',
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_GENERAL,
        ]);
        $fakeTask->id = 888;
        $fakeTask->created_at = now();

        $renderedEdit = View::make('tasks::user.tasks.edit', [
            'task' => $fakeTask,
            'statuses' => ['TODO' => 'در صف انجام', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'types' => ['GENERAL' => 'عمومی'],
            'users' => collect(),
            'roles' => collect(),
            'clients' => collect(),
            'clientStatuses' => collect(),
            'canAssign' => true,
        ])->render();

        $this->assertStringContainsString('ویرایش وظیفه شماره', $renderedEdit);
        $this->assertStringContainsString('اطلاعات پایه وظیفه', $renderedEdit);
        $this->assertStringContainsString('کاربر مسئول انجام', $renderedEdit);
        $this->assertStringContainsString('مشتری مرتبط', $renderedEdit);
        $this->assertStringContainsString('Menu Dock', $renderedEdit);
        $this->assertStringContainsString('role="radiogroup"', $renderedEdit);
        $this->assertStringContainsString('name="assignee_mode"', $renderedEdit);
        $this->assertStringContainsString('name="related_target"', $renderedEdit);
        $this->assertStringContainsString('window.uiSearchableSelect', $renderedEdit);
        $this->assertStringContainsString('name="assignee_user_ids[]"', $renderedEdit);

        $renderedShow = View::make('tasks::user.tasks.show', [
            'task' => $fakeTask,
            'statuses' => ['TODO' => 'در صف انجام', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'types' => ['GENERAL' => 'عمومی'],
            'relatedClient' => null,
            'relatedUser' => null,
        ])->render();

        $this->assertStringContainsString('مشخصات و شرح وظیفه', $renderedShow);
        $this->assertStringContainsString('هشدارهای یادآوری وظیفه', $renderedShow);
    }

    public function test_segmented_button_component_renders_with_options_and_models(): void
    {
        $rendered = \Illuminate\Support\Facades\Blade::render(
            '<x-ui.segmented-button name="period" model="selectedPeriod" :options="[\'monthly\' => \'ماهانه\', \'yearly\' => \'سالانه\']" />'
        );
        $this->assertStringContainsString('role="radiogroup"', $rendered);
        $this->assertStringContainsString('ماهانه', $rendered);
        $this->assertStringContainsString('سالانه', $rendered);
        $this->assertStringContainsString('name="period"', $rendered);
        $this->assertStringContainsString('selectedPeriod = \'monthly\'', $rendered);
    }

    public function test_searchable_select_component_renders_with_options(): void
    {
        $options = [
            ['value' => '1', 'label' => 'محمد رضایی', 'subtitle' => '09121234567'],
            ['value' => '2', 'label' => 'سارا کریمی', 'subtitle' => 'sara@test.com'],
        ];

        $rendered = \Illuminate\Support\Facades\Blade::render(
            '<x-ui.searchable-select name="assignee_id" :options="$options" placeholder="انتخاب مسئول..." />',
            ['options' => $options]
        );

        $this->assertStringContainsString('window.uiSearchableSelect', $rendered);
        $this->assertStringContainsString('name="assignee_id"', $rendered);
        $this->assertStringContainsString('محمد رضایی', $rendered);
        $this->assertStringContainsString('سارا کریمی', $rendered);
    }

    public function test_select_component_card_variant_and_multiple(): void
    {
        $options = [
            ['value' => '10', 'label' => 'شرکت توسعه داده', 'badge' => 'پرونده: ۱۰۰۱'],
        ];

        $rendered = \Illuminate\Support\Facades\Blade::render(
            '<x-ui.select name="client_ids" :options="$options" :multiple="true" variant="card" color="emerald" />',
            ['options' => $options]
        );

        $this->assertStringContainsString('window.uiSearchableSelect', $rendered);
        $this->assertStringContainsString('client_ids[]', $rendered);
        $this->assertStringContainsString('bg-emerald-600', $rendered);
        $this->assertStringContainsString('شرکت توسعه داده', $rendered);
    }
}
