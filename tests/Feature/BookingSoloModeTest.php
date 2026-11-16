<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingSetting;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingSoloModeTest extends TestCase
{
    public function test_booking_setting_allowed_roles_defaults_to_admin_role(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['display_name' => 'مدیر سیستم']
        );

        $setting = new BookingSetting();
        $setting->allowed_roles = [];

        $this->assertContains((int) $adminRole->id, $setting->allowed_roles);
    }

    public function test_creating_service_without_providers_automatically_attaches_default_provider(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Solo Clinic Test',
            'subdomain' => 'soloclinic',
            'status' => 'active',
        ]);

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['display_name' => 'مدیر سیستم']
        );

        $user = User::where('tenant_id', $tenant->id)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Dr. Solo',
                'email' => 'solo@test.com',
                'password' => bcrypt('secret'),
                'tenant_id' => $tenant->id,
            ]);
            $user->assignRole($adminRole);
        }

        $this->actingAs($user);

        $service = BookingService::create([
            'tenant_id' => $tenant->id,
            'owner_user_id' => $user->id,
            'name' => 'ویزیت عمومی تک‌نفره',
            'base_price' => 250000,
            'status' => BookingService::STATUS_ACTIVE,
            'online_booking_mode' => BookingService::ONLINE_MODE_INHERIT,
            'payment_mode' => BookingService::PAYMENT_MODE_NONE,
        ]);

        $this->assertDatabaseHas('booking_service_providers', [
            'service_id' => $service->id,
            'provider_user_id' => $user->id,
            'is_active' => 1,
        ]);

        $service->delete();
    }

    public function test_creating_service_with_explicit_provider_respects_chosen_provider(): void
    {
        $tenant = Tenant::first();
        $user = User::where('tenant_id', $tenant->id)->first();
        $this->actingAs($user);

        // Simulate an HTTP request that explicitly passes provider_ids = [9999]
        request()->merge(['provider_ids' => [9999]]);

        $service = BookingService::create([
            'tenant_id' => $tenant->id,
            'owner_user_id' => $user->id,
            'name' => 'خدمت تخصصی با پرسنل مشخص',
            'base_price' => 300000,
            'status' => BookingService::STATUS_ACTIVE,
            'online_booking_mode' => BookingService::ONLINE_MODE_INHERIT,
            'payment_mode' => BookingService::PAYMENT_MODE_NONE,
        ]);

        // Observer should NOT have attached $user->id because provider_ids was explicitly given
        $this->assertDatabaseMissing('booking_service_providers', [
            'service_id' => $service->id,
            'provider_user_id' => $user->id,
        ]);

        $service->delete();
    }
}
