<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Modules\ModuleMenuService;
use App\Tenancy\TenantManager;
use Modules\ResellerHub\App\Data\Onboarding\StepData;
use Modules\ResellerHub\App\Enums\OnboardingInternalRoute;
use Modules\ResellerHub\App\Enums\OnboardingStepPreset;
use Modules\ResellerHub\App\Livewire\Dashboard\OnboardingWidget;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OnboardingCustomStepsRoutingTest extends TestCase
{
    protected Tenant $tenant;
    protected User $tenantAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['display_name' => 'مدیر سیستم']
        );

        $this->tenant = Tenant::firstOrCreate(
            ['subdomain' => 'test-onboarding-routes'],
            ['name' => 'Onboarding Test Tenant', 'status' => 'active']
        );

        $this->tenantAdmin = User::firstOrCreate(
            ['email' => 'onboarding-admin@tenant.test'],
            [
                'name' => 'Onboarding Admin',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
            ]
        );
        if (!$this->tenantAdmin->hasRole('admin')) {
            $this->tenantAdmin->assignRole($adminRole);
        }
    }

    public function test_internal_routes_enum_contains_both_users_create_and_booking_providers(): void
    {
        $frontendArray = OnboardingInternalRoute::toFrontendArray();
        $routes = array_column($frontendArray, 'route');

        $this->assertContains('user/users/create', $routes);
        $this->assertContains('user/users', $routes);
        $this->assertContains('user/booking/providers', $routes);
        $this->assertContains('user/booking', $routes);
        $this->assertContains('user/clients/create', $routes);

        // Ensure user creation and booking providers are distinct
        $this->assertNotEquals(
            OnboardingInternalRoute::USERS_CREATE->value,
            OnboardingInternalRoute::BOOKING_PROVIDERS->value
        );
    }

    public function test_team_member_preset_routes_to_users_create_not_booking(): void
    {
        $preset = OnboardingStepPreset::TEAM_MEMBER;

        $this->assertEquals(OnboardingInternalRoute::USERS_CREATE, $preset->internalRouteEnum());
        $this->assertEquals('/user/users/create', $preset->actionUrl());
        $this->assertStringNotContainsString('booking', $preset->actionUrl());
    }

    public function test_onboarding_widget_resolves_and_normalizes_distinct_routes(): void
    {
        app(TenantManager::class)->setTenant($this->tenant);
        $widget = new OnboardingWidget();

        // 1. User creation route normalization
        $resolvedUserCreate = $widget->getStepActionUrl(['action_url' => '/admin/users/create']);
        $this->assertStringContainsString('user/users/create', $resolvedUserCreate);

        $resolvedDirectUserCreate = $widget->getStepActionUrl(['action_url' => '/user/users/create']);
        $this->assertStringContainsString('user/users/create', $resolvedDirectUserCreate);

        // 2. Booking provider route remains distinct and untouched
        $resolvedBookingProvider = $widget->getStepActionUrl(['action_url' => '/user/booking/providers']);
        $this->assertStringContainsString('user/booking/providers', $resolvedBookingProvider);
        $this->assertStringNotContainsString('user/users', $resolvedBookingProvider);

        // 3. User list route
        $resolvedUserList = $widget->getStepActionUrl(['action_url' => '/admin/users']);
        $this->assertStringContainsString('user/users', $resolvedUserList);
    }

    public function test_team_member_step_auto_completes_when_tenant_adds_staff(): void
    {
        $stateService = app(OnboardingStateService::class);

        $stepData = new StepData(
            slug: 'team_member',
            journey: 'crm_setup',
            title: 'تعریف اولین همکار',
            description: 'توضیحات تستی',
            routeAction: 'user/users/create',
            actionUrl: '/user/users/create',
            actionTarget: 'user',
        );

        // 1. Initially only 1 user (the admin) exists in tenant
        User::where('tenant_id', $this->tenant->id)->where('id', '!=', $this->tenantAdmin->id)->delete();
        $this->assertEquals(1, User::where('tenant_id', $this->tenant->id)->count());

        $isCompleteBefore = $stateService->verifyStepEntityExistence($this->tenant, $stepData, $this->tenantAdmin);
        $this->assertFalse($isCompleteBefore);

        // 2. Add a second user to tenant
        $newStaff = User::create([
            'name' => 'پرسنل جدید',
            'email' => 'newstaff@tenant.test',
            'password' => bcrypt('password123'),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(2, User::where('tenant_id', $this->tenant->id)->count());

        $isCompleteAfter = $stateService->verifyStepEntityExistence($this->tenant, $stepData, $this->tenantAdmin);
        $this->assertTrue($isCompleteAfter);

        $newStaff->delete();
    }

    public function test_sidebar_menu_shows_users_management_and_no_broken_system_management(): void
    {
        app(TenantManager::class)->setTenant($this->tenant);

        $menuService = app(ModuleMenuService::class);
        $menu = $menuService->getAllForUser($this->tenantAdmin);

        $items = $menu['items'] ?? [];
        $groups = $menu['groups'] ?? [];

        // 1. Verify 'مدیریت کاربران' item exists
        $userMenuItem = collect($items)->firstWhere('route', 'user.users.index');
        $this->assertNotNull($userMenuItem, 'مدیریت کاربران باید در لیست منوهای تننت وجود داشته باشد.');
        $this->assertEquals('مدیریت کاربران', $userMenuItem['title']);

        // 2. Verify NO 'admin' module group or 'menu.system_management'
        $adminGroup = collect($groups)->firstWhere('module', 'admin');
        $this->assertNull($adminGroup, 'منوی مرکزی ادمین نباید در سایدبار تننت نمایش داده شود.');

        // 3. Verify no raw translation keys leaked into titles
        foreach ($items as $item) {
            $this->assertStringNotContainsString('system_management', $item['title']);
        }
        foreach ($groups as $group) {
            $this->assertStringNotContainsString('system_management', $group['module_name'] ?? '');
        }
    }
}
