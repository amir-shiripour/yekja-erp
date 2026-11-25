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
        $this->assertEquals(27, $theme['crm_overrides_count']);

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
            'reminders/user/reminders/index.blade.php',
            'reminders/user/reminders/snooze-history.blade.php',
            'reminders/user/settings/index.blade.php',
            'notifications/user/index.blade.php',
            'notifications/user/settings.blade.php',
            'notifications/admin/broadcast/index.blade.php',
            'notifications/admin/broadcast/create.blade.php',
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

        $reminderOverride = $resolver->findOverridePath('reminders::user.reminders.index');
        $this->assertNotNull($reminderOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $reminderOverride));
        $this->assertFileExists($reminderOverride);

        $reminderSettingsOverride = $resolver->findOverridePath('reminders::user.settings.index');
        $this->assertNotNull($reminderSettingsOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $reminderSettingsOverride));
        $this->assertFileExists($reminderSettingsOverride);

        $reminderSnoozeOverride = $resolver->findOverridePath('reminders::user.reminders.snooze-history');
        $this->assertNotNull($reminderSnoozeOverride);
        $this->assertStringContainsString('admin-pro', str_replace('\\', '/', $reminderSnoozeOverride));
        $this->assertFileExists($reminderSnoozeOverride);

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

        \Illuminate\Support\Facades\Gate::define('followups.edit', fn() => true);
        $fakeFollowupItem = new \Modules\Tasks\Entities\Task([
            'title' => 'پیگیری تستی برای کامپوننت انجام شد',
            'status' => 'TODO',
            'priority' => 'MEDIUM',
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
        ]);
        $fakeFollowupItem->id = 888;
        $paginatorWithFu = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$fakeFollowupItem]),
            1,
            15,
            1,
            ['path' => '/user/followups']
        );
        $renderedIndexWithFu = View::make('followups::user.followups.index', [
            'followups' => $paginatorWithFu,
            'statuses' => ['TODO' => 'در انتظار', 'DONE' => 'انجام شده'],
            'priorities' => ['LOW' => 'پایین', 'MEDIUM' => 'متوسط'],
            'types' => ['FOLLOW_UP' => 'پیگیری عادی'],
            'users' => collect(),
        ])->render();
        $this->assertStringContainsString('confirmComplete', $renderedIndexWithFu);
        $this->assertStringContainsString('user/tasks/bulk', $renderedIndexWithFu);
        $this->assertStringContainsString('لغو انتخاب همه', $renderedIndexWithFu);

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

        // تست رندر تسک با دکمه حذف و تکمیل Devigner
        \Illuminate\Support\Facades\Gate::define('tasks.delete', fn() => true);
        \Illuminate\Support\Facades\Gate::define('tasks.edit', fn() => true);
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
        $this->assertStringContainsString('confirmComplete', $renderedIndexWithItem);
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

    public function test_reminders_index_override_renders_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'کاربر آزمایشی',
            'email' => 'test@crm.local',
        ]);
        $this->actingAs($user);

        // ۱. بررسی رندر خالی
        $renderedEmpty = View::make('reminders::user.reminders.index', [
            'taskReminders' => collect(),
            'followUpReminders' => collect(),
            'status' => 'open',
            'period' => 'today',
            'statusFilterOptions' => ['open' => 'باز (در انتظار)', 'done' => 'انجام‌شده', 'all' => 'همه'],
            'periodOptions' => ['today' => 'امروز', 'all' => 'بدون فیلتر زمانی'],
            'users' => collect([$user]),
            'selectedUserId' => 1,
            'from' => null,
            'to' => null,
        ])->render();

        $this->assertStringContainsString('مدیریت و پیگیری یادآوری‌ها', $renderedEmpty);
        $this->assertStringContainsString('کل یادآوری‌ها', $renderedEmpty);
        $this->assertStringContainsString('یادآوری‌های وظایف', $renderedEmpty);
        $this->assertStringContainsString('یادآوری‌های پیگیری', $renderedEmpty);
        $this->assertStringContainsString('در انتظار انجام', $renderedEmpty);
        $this->assertStringContainsString('وضعیت یادآوری', $renderedEmpty);
        $this->assertStringContainsString('هیچ یادآوری‌ای در این بازه یافت نشد', $renderedEmpty);

        // ۲. بررسی رندر با داده فیک
        $fakeTask = new \Modules\Tasks\Entities\Task([
            'title' => 'تماس با مشتری VIP',
            'priority' => \Modules\Tasks\Entities\Task::PRIORITY_HIGH,
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_GENERAL,
        ]);
        $fakeTask->id = 10;

        $fakeFollowUp = new \Modules\Tasks\Entities\Task([
            'title' => 'پیگیری فاکتور ارسالی',
            'priority' => \Modules\Tasks\Entities\Task::PRIORITY_CRITICAL,
            'task_type' => \Modules\Tasks\Entities\Task::TYPE_FOLLOW_UP,
        ]);
        $fakeFollowUp->id = 20;

        $fakeReminder1 = new \Modules\Reminders\Entities\Reminder([
            'message' => 'تماس تلفنی با مدیر خرید',
            'remind_at' => now()->addHours(2),
            'status' => \Modules\Reminders\Entities\Reminder::STATUS_OPEN,
            'related_type' => 'TASK',
            'related_id' => 10,
            'user_id' => 1,
            'snooze_count' => 0,
        ]);
        $fakeReminder1->id = 101;
        $fakeReminder1->setRelation('task', $fakeTask);
        $fakeReminder1->setRelation('user', $user);

        $fakeReminder2 = new \Modules\Reminders\Entities\Reminder([
            'message' => 'بررسی تایید پرداخت مشتری',
            'remind_at' => now()->subHours(1),
            'status' => \Modules\Reminders\Entities\Reminder::STATUS_OPEN,
            'related_type' => 'TASK',
            'related_id' => 20,
            'user_id' => 1,
            'snooze_count' => 1,
        ]);
        $fakeReminder2->id = 102;
        $fakeReminder2->setRelation('task', $fakeFollowUp);
        $fakeReminder2->setRelation('followUp', $fakeFollowUp);
        $fakeReminder2->setRelation('user', $user);

        $renderedWithData = View::make('reminders::user.reminders.index', [
            'taskReminders' => collect([$fakeReminder1]),
            'followUpReminders' => collect([$fakeReminder2]),
            'status' => 'open',
            'period' => 'today',
            'statusFilterOptions' => ['open' => 'باز (در انتظار)', 'done' => 'انجام‌شده', 'all' => 'همه'],
            'periodOptions' => ['today' => 'امروز', 'all' => 'بدون فیلتر زمانی'],
            'users' => collect([$user]),
            'selectedUserId' => 1,
            'from' => null,
            'to' => null,
        ])->render();

        $this->assertStringContainsString('تماس با مشتری VIP', $renderedWithData);
        $this->assertStringContainsString('پیگیری فاکتور ارسالی', $renderedWithData);
        $this->assertStringContainsString('تماس تلفنی با مدیر خرید', $renderedWithData);
        $this->assertStringContainsString('بررسی تایید پرداخت مشتری', $renderedWithData);
        $this->assertStringContainsString('تعویق', $renderedWithData);
        $this->assertStringContainsString('data-scope="tasks"', $renderedWithData);
        $this->assertStringContainsString('data-scope="followups"', $renderedWithData);
        $this->assertStringContainsString('confirmComplete', $renderedWithData);
        $this->assertStringContainsString('user/reminders/bulk-status', $renderedWithData);
    }

    public function test_reminders_settings_override_renders_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'کاربر آزمایشی',
            'email' => 'test@crm.local',
        ]);
        $this->actingAs($user);

        $fakeRole = new \Spatie\Permission\Models\Role(['id' => 1, 'name' => 'super-admin']);

        $rendered = View::make('reminders::user.settings.index', [
            'settings' => [
                'reminders_in_progress_enabled' => '1',
                'reminders_snooze_enabled' => '1',
                'reminders_snooze_limit' => 5,
                'reminders_snooze_reason_required' => 'optional',
                'reminders_escalation_roles' => json_encode(['super-admin']),
                'reminders_escalation_users' => json_encode([1]),
            ],
            'escalationRoles' => ['super-admin'],
            'escalationUsers' => [1],
            'roles' => collect([$fakeRole]),
            'users' => collect([$user]),
        ])->render();

        $this->assertStringContainsString('تنظیمات یادآوری و تعویق', $rendered);
        $this->assertStringContainsString('قوانین و رفتارهای تعویق', $rendered);
        $this->assertStringContainsString('امکان تغییر وضعیت به «درحال انجام»', $rendered);
        $this->assertStringContainsString('فعال بودن قابلیت تعویق (Snooze)', $rendered);
        $this->assertStringContainsString('حداکثر تعداد مجاز تعویق', $rendered);
        $this->assertStringContainsString('وضعیت ثبت دلیل تعویق', $rendered);
        $this->assertStringContainsString('تنظیمات ارجاع خودکار (Escalation)', $rendered);
        $this->assertStringContainsString('ذخیره تنظیمات یادآوری', $rendered);
    }

    public function test_reminders_snooze_history_override_renders_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'مدیر سیستم',
            'email' => 'admin@admin.com',
        ]);
        $this->actingAs($user);

        $fakeReminder = new \Modules\Reminders\Entities\Reminder([
            'message' => 'پیگیری فاکتور خرید هاست',
            'remind_at' => now()->addDay(),
            'status' => \Modules\Reminders\Entities\Reminder::STATUS_OPEN,
            'reminder_scope' => 'tasks',
            'related_type' => 'TASK',
            'related_id' => 10,
        ]);
        $fakeReminder->id = 14;

        $fakeLog = new \Modules\Reminders\Entities\ReminderSnoozeLog([
            'reminder_id' => 14,
            'user_id' => 1,
            'original_remind_at' => now(),
            'snoozed_to' => now()->addHours(2),
            'duration_minutes' => 120,
            'duration_key' => '2h',
            'reason' => 'مشتری در جلسه کاری بود',
            'snooze_sequence' => 1,
        ]);
        $fakeLog->id = 101;
        $fakeLog->created_at = now();
        $fakeLog->setRelation('user', $user);

        $rendered = View::make('reminders::user.reminders.snooze-history', [
            'reminder' => $fakeReminder,
            'logs' => collect([$fakeLog]),
        ])->render();

        $this->assertStringContainsString('تاریخچه تعویق یادآوری', $rendered);
        $this->assertStringContainsString('پیگیری فاکتور خرید هاست', $rendered);
        $this->assertStringContainsString('مشتری در جلسه کاری بود', $rendered);
        $this->assertStringContainsString('مدیر سیستم', $rendered);
        $this->assertStringContainsString('۲ ساعت', $rendered);
    }

    public function test_notifications_index_override_renders_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'مدیر سیستم',
            'email' => 'admin@admin.com',
        ]);
        $this->actingAs($user);

        $fakeNotif = new \Modules\Notifications\Entities\Notification([
            'id' => '00000000-0000-0000-0000-000000000001',
            'type' => 'Modules\\Notifications\\Notifications\\GeneralNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => 1,
            'data' => [
                'title' => 'هشدار امنیتی تست',
                'message' => 'تلاش ناموفق برای ورود شناسایی شد.',
                'category' => 'system',
                'priority' => 'urgent',
                'severity' => 'danger',
            ],
            'read_at' => null,
        ]);
        $fakeNotif->created_at = now();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$fakeNotif]),
            1,
            15,
            1
        );

        $rendered = View::make('notifications::user.index', [
            'notifications' => $paginator,
            'statusFilter' => 'all',
            'categoryFilter' => 'all',
            'priorityFilter' => 'all',
            'search' => null,
            'unreadCount' => 1,
            'readCount' => 0,
            'totalCount' => 1,
            'categories' => config('notifications.categories', []),
            'priorities' => config('notifications.priorities', []),
        ])->render();

        $this->assertStringContainsString('مرکز اعلان‌ها', $rendered);
        $this->assertStringContainsString('مدیریت و پیگیری اعلان‌ها', $rendered);
        $this->assertStringContainsString('هشدار امنیتی تست', $rendered);
        $this->assertStringContainsString('فوری', $rendered);
    }

    public function test_notifications_broadcast_overrides_render_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'مدیر سیستم',
            'email' => 'admin@admin.com',
        ]);
        $this->actingAs($user);

        $fakeBroadcast = new \Modules\Notifications\Entities\NotificationBroadcast([
            'id' => 1,
            'created_by' => 1,
            'title' => 'اطلاعیه تستی همگانی',
            'message' => 'متن پیام آزمایشی برای کل کاربران',
            'category' => 'broadcast',
            'priority' => 'high',
            'severity' => 'warning',
            'target_type' => 'all',
            'channels' => ['database', 'sms'],
            'recipients_count' => 15,
            'sent_at' => now(),
        ]);
        $fakeBroadcast->id = 1;
        $fakeBroadcast->setRelation('creator', $user);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$fakeBroadcast]),
            1,
            15,
            1
        );

        $renderedIndex = View::make('notifications::admin.broadcast.index', [
            'broadcasts' => $paginator,
        ])->render();

        $this->assertStringContainsString('اطلاعیه‌ها و پیام‌های همگانی', $renderedIndex);
        $this->assertStringContainsString('اطلاعیه تستی همگانی', $renderedIndex);
        $this->assertStringContainsString('ارسال اطلاعیه جدید', $renderedIndex);

        $fakeRole = new Role(['name' => 'super-admin', 'display_name' => 'مدیر ارشد']);
        $renderedCreate = View::make('notifications::admin.broadcast.create', [
            'roles' => collect([$fakeRole]),
            'categories' => config('notifications.categories', []),
            'priorities' => config('notifications.priorities', []),
            'severities' => config('notifications.severities', []),
        ])->render();

        $this->assertStringContainsString('ارسال اطلاعیه همگانی جدید', $renderedCreate);
        $this->assertStringContainsString('محتوا و مخاطبان اطلاعیه', $renderedCreate);
        $this->assertStringContainsString('مدیر ارشد', $renderedCreate);
    }

    public function test_notifications_settings_override_renders_with_ui_components(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'مدیر سیستم',
            'email' => 'admin@admin.com',
        ]);
        $this->actingAs($user);

        $userSetting = new \Modules\Notifications\Entities\NotificationUserSetting([
            'user_id' => 1,
            'sound_enabled' => true,
            'quiet_hours_enabled' => false,
            'quiet_hours_start' => '22:00:00',
            'quiet_hours_end' => '08:00:00',
        ]);

        $rendered = View::make('notifications::user.settings', [
            'user' => $user,
            'categories' => config('notifications.categories', []),
            'userPreferences' => collect(),
            'userSetting' => $userSetting,
            'defaultChannels' => config('notifications.default_channels', []),
        ])->render();

        $this->assertStringContainsString('تنظیمات و ترجیحات اعلان‌ها', $rendered);
        $this->assertStringContainsString('تنظیمات عمومی و ساعات سکوت (Do Not Disturb)', $rendered);
        $this->assertStringContainsString('کانال‌های دریافت به تفکیک موضوع', $rendered);
        $this->assertStringContainsString('ذخیره تنظیمات اعلان‌ها', $rendered);
    }
}
