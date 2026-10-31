<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Guards the public route surface of the module.
 * A catch-all route registered by another module can silently swallow a
 * dedicated route: the URL still "works" in `route:list`, but the request
 * lands in the wrong controller. These assertions pin the resolved route, not
 * just its existence.
 */
class RouteRegistrationTest extends TestCase
{
    private function resolve(string $uri, string $method = 'GET'): Route
    {
        return RouteFacade::getRoutes()->match(Request::create($uri, $method));
    }

    public function test_the_public_registration_page_is_not_shadowed_by_a_catch_all(): void
    {
        $route = $this->resolve('/get-started');

        $this->assertSame(
            'portal.get-started',
            $route->getName(),
            'GET /get-started is being handled by [' . $route->getActionName() . '] instead of the ResellerHub portal.'
        );
    }

    public function test_the_public_pricing_page_resolves_to_the_module(): void
    {
        $this->assertSame('resellerhub.pricing', $this->resolve('/resellerhub/pricing')->getName());
    }

    public function test_impersonation_exit_is_a_post_route(): void
    {
        $route = $this->resolve('/reseller/exit-impersonation', 'POST');

        $this->assertSame('tenant.exit-impersonation', $route->getName());
        $this->assertContains('POST', $route->methods());
        $this->assertNotContains('GET', $route->methods(), 'Logging out of an impersonated session must never be a GET.');
    }

    public function test_tenant_login_as_is_protected_by_its_own_permission(): void
    {
        $route = $this->resolve('/reseller/tenants/1/login-as', 'POST');

        $this->assertSame('reseller.tenants.login-as', $route->getName());
        $this->assertContains(
            'role_or_permission:super-admin|resellerhub.tenants.login-as',
            $route->gatherMiddleware(),
            'Impersonation must require an explicit permission, not just resellerhub.view.'
        );
    }
}
