<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantExitImpersonationTest extends TestCase
{
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->tenant = Tenant::firstOrCreate(
            ['subdomain' => 'test-impersonation-sub'],
            ['name' => 'Test Impersonation Tenant', 'status' => 'active']
        );
    }

    private function makeUser(array $attributes = []): User
    {
        $unique = uniqid('user_', true);

        return User::create(array_merge([
            'name' => 'Test User ' . $unique,
            'email' => $unique . '@example.com',
            'password' => bcrypt('password123'),
        ], $attributes));
    }

    public function test_exit_impersonation_route_is_exempt_from_csrf_and_accepts_post(): void
    {
        $superAdmin = $this->makeUser(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $tenantUser = $this->makeUser(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($tenantUser)
            ->withSession([
                'impersonated_by' => $superAdmin->id,
                'impersonated_tenant_id' => $this->tenant->id,
            ])
            ->post('/user/reseller/exit-impersonation');

        // Must NOT be 419 (CSRF Token Mismatch)
        $this->assertNotEquals(419, $response->getStatusCode());
        // Must be redirect (302)
        $response->assertStatus(302);
    }

    public function test_exit_impersonation_accepts_get_request_without_405_error(): void
    {
        $superAdmin = $this->makeUser(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $tenantUser = $this->makeUser(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($tenantUser)
            ->withSession([
                'impersonated_by' => $superAdmin->id,
                'impersonated_tenant_id' => $this->tenant->id,
            ])
            ->get('/user/reseller/exit-impersonation');

        // Must NOT be 405 (Method Not Allowed)
        $this->assertNotEquals(405, $response->getStatusCode());
        $response->assertStatus(302);
    }

    public function test_exit_impersonation_recovers_impersonator_from_encrypted_cookie_when_session_expired(): void
    {
        $superAdmin = $this->makeUser(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $tenantUser = $this->makeUser(['tenant_id' => $this->tenant->id]);

        $cookiePayload = encrypt([
            'impersonator_id' => $superAdmin->id,
            'tenant_id' => $this->tenant->id,
            'created_at' => now()->timestamp,
        ]);

        // Simulating an expired session: session has NO impersonated_by, but cookie is present
        $response = $this->actingAs($tenantUser)
            ->withCookies(['impersonator_token' => $cookiePayload])
            ->get('/user/reseller/exit-impersonation');

        $response->assertStatus(302);
        // Should redirect to sso-return because impersonator was recovered
        $this->assertStringContainsString('sso-return?token=', $response->headers->get('Location'));
    }

    public function test_exit_impersonation_handles_completely_expired_session_gracefully_without_error(): void
    {
        $tenantUser = $this->makeUser(['tenant_id' => $this->tenant->id]);

        // No session data, no cookie
        $response = $this->actingAs($tenantUser)
            ->get('/user/reseller/exit-impersonation');

        $response->assertStatus(302);
        // Must redirect to login safely
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
        // Web guard must be logged out
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_sso_return_cleanses_all_tenant_state_and_restores_super_admin(): void
    {
        $superAdmin = $this->makeUser(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $returnToken = 'test_return_token_' . uniqid();
        Cache::put('sso_return_' . $returnToken, [
            'user_id' => $superAdmin->id,
            'ip_address' => '127.0.0.1',
            'user_agent_hash' => md5('Symfony'),
            'created_at' => now()->timestamp,
        ], 120);

        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        // Pre-pollute state with a dummy tenant to verify total cleansing
        $dummyTenant = new Tenant();
        $dummyTenant->id = 777;
        $dummyTenant->name = 'Leaked Subaccount';
        $dummyTenant->subdomain = 'leaked-sub';
        $tenantManager->setTenant($dummyTenant);
        app()->instance('currentTenant', $dummyTenant);
        view()->share('currentTenant', $dummyTenant);

        $response = $this->get('/user/reseller/sso-return?token=' . $returnToken);

        $response->assertStatus(302);
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals($superAdmin->id, Auth::guard('web')->id());

        // Assert tenant state is completely cleaned
        $this->assertFalse($tenantManager->hasTenant());
        $this->assertNull($tenantManager->getTenant());
        $this->assertNull(app('currentTenant'));
        $this->assertNull(view()->shared('currentTenant'));
        $this->assertNull(Auth::user()->tenant_id);
    }

    public function test_topbar_renders_exit_impersonation_links_cleanly(): void
    {
        $tenantUser = $this->makeUser(['tenant_id' => $this->tenant->id]);

        $rendered = (string)$this->actingAs($tenantUser)
            ->withSession(['impersonated_by' => 1])
            ->view('user.partials.topbar');

        $this->assertStringContainsString('exit-impersonation', $rendered);
        $this->assertStringContainsString('بازگشت به نمایندگی', $rendered);
    }
}
