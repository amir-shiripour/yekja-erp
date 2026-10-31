<?php

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Http\Request;
use Modules\ResellerHub\App\Http\Controllers\SplashController;
use Modules\ResellerHub\App\Http\Controllers\TenantSsoController;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Phase 1 regression tests for the security/tenancy hardening.
 *
 * These intentionally avoid the database: they cover the pure guards that were
 * introduced to stop open redirects and SSO token replay from another browser.
 */
class Phase1SecurityTest extends TestCase
{
    private function splash(): SplashController
    {
        return new class extends SplashController {
            public function check(mixed $candidate): ?string
            {
                return $this->safeRedirectUrl($candidate);
            }
        };
    }

    private function sso(): TenantSsoController
    {
        return new class extends TenantSsoController {
            public function matches(array $payload, Request $request): bool
            {
                return $this->userAgentMatches($payload, $request);
            }
        };
    }

    protected function tearDown(): void
    {
        // Reset the request binding so tests do not leak host state.
        $this->app->forgetInstance('request');

        parent::tearDown();
    }

    public function test_splash_accepts_relative_and_same_host_redirects(): void
    {
        $request = Request::create('https://app.example.test/resellerhub/splash/registered');
        $this->app->instance('request', $request);
        config(['app.url' => 'https://app.example.test']);

        $controller = $this->splash();

        $this->assertSame('/user/dashboard', $controller->check('/user/dashboard'));
        $this->assertSame(
            'https://app.example.test/user/dashboard',
            $controller->check('https://app.example.test/user/dashboard')
        );
    }

    public function test_splash_rejects_open_redirects(): void
    {
        $request = Request::create('https://app.example.test/resellerhub/splash/registered');
        $this->app->instance('request', $request);
        config(['app.url' => 'https://app.example.test']);

        $controller = $this->splash();

        $this->assertNull($controller->check('https://evil.example/user/dashboard'));
        $this->assertNull($controller->check('//evil.example/user/dashboard'));
        $this->assertNull($controller->check('/\\evil.example/user/dashboard'));
        $this->assertNull($controller->check('javascript:alert(1)'));
        $this->assertNull($controller->check('data:text/html,<script>alert(1)</script>'));
        $this->assertNull($controller->check('   '));
        $this->assertNull($controller->check(null));
    }

    public function test_sso_token_fails_closed_without_user_agent_hash(): void
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Browser/1.0']);

        $this->assertFalse($this->sso()->matches([], $request));
        $this->assertFalse($this->sso()->matches(['user_agent_hash' => ''], $request));
    }

    public function test_sso_token_is_bound_to_the_requesting_browser(): void
    {
        $hash = md5('Browser/1.0');
        $payload = ['user_agent_hash' => $hash];

        $matching = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Browser/1.0']);
        $foreign = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Another/2.0']);

        $controller = $this->sso();

        $this->assertTrue($controller->matches($payload, $matching));
        $this->assertFalse($controller->matches($payload, $foreign));
    }
}
