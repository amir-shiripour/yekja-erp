<?php

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Traits\HasReseller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Regression tests for reseller context resolution.
 *
 * The bug this locks down: `resolveReseller()` used to end with
 * `?: Reseller::first()`, so a platform administrator with nothing selected was
 * silently bound to an arbitrary (and non-deterministic) reseller, and the
 * "go back to the dashboard and choose" branch was unreachable.
 *
 * If anyone re-introduces that fallback, the `assertNull` cases below fail.
 */
class HasResellerTest extends TestCase
{
    private object $harness;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        $this->harness = new class {
            use HasReseller;

            public function resolve(): Reseller
            {
                return $this->getReseller();
            }

            public function resolveOrNull(): ?Reseller
            {
                return $this->getResellerOrNull();
            }
        };
    }

    protected function tearDown(): void
    {
        Auth::logout();
        session()->flush();
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'کاربر ' . uniqid('', true),
            'email' => uniqid('', true) . '@example.test',
        ], $attributes));
    }

    private function reseller(User $owner, string $status = 'active'): Reseller
    {
        return Reseller::create([
            'user_id' => $owner->id,
            'company_name' => 'نمایندگی ' . uniqid('', true),
            'status' => $status,
        ]);
    }

    private function grantRole(User $user, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id');

        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $user->unsetRelation('roles');
    }

    private function statusOf(callable $call): string
    {
        try {
            $result = $call();

            return $result === null ? 'null' : 'reseller:' . $result->id;
        } catch (HttpResponseException $e) {
            return 'redirect';
        } catch (HttpException $e) {
            return 'http:' . $e->getStatusCode();
        }
    }

    public function test_the_owner_of_an_active_reseller_is_resolved(): void
    {
        $owner = $this->user();
        $reseller = $this->reseller($owner);

        Auth::login($owner);

        $this->assertSame($reseller->id, $this->harness->resolveOrNull()?->id);
        $this->assertSame($reseller->id, $this->harness->resolve()->id);
    }

    public function test_the_owner_of_a_suspended_reseller_is_blocked_everywhere(): void
    {
        $owner = $this->user();
        $this->reseller($owner, 'suspended');

        Auth::login($owner);

        $this->assertSame('http:403', $this->statusOf(fn() => $this->harness->resolve()));
        $this->assertSame(
            'http:403',
            $this->statusOf(fn() => $this->harness->resolveOrNull()),
            'A suspended reseller must not even reach the read-only dashboard.'
        );
    }

    /**
     * The core regression: a platform administrator who has selected nothing
     * must get a neutral context, never an arbitrary reseller.
     */
    public function test_a_super_admin_with_no_selection_is_not_bound_to_any_reseller(): void
    {
        $admin = $this->user();
        $this->grantRole($admin, 'super-admin');

        // Resellers exist, so a `Reseller::first()` fallback would find one.
        $this->reseller($this->user());
        $this->reseller($this->user());

        Auth::login($admin);
        session()->forget('active_reseller_id');

        $this->assertNull(
            $this->harness->resolveOrNull(),
            'A super-admin must never be silently bound to another reseller.'
        );

        $this->assertSame(
            'redirect',
            $this->statusOf(fn() => $this->harness->resolve()),
            'Operational screens must send the administrator to the selector.'
        );
    }

    public function test_a_super_admin_can_explicitly_choose_an_active_reseller(): void
    {
        $admin = $this->user();
        $this->grantRole($admin, 'super-admin');

        $owner = $this->user();
        $reseller = $this->reseller($owner);

        Auth::login($admin);
        session(['active_reseller_id' => $reseller->id]);

        $this->assertSame($reseller->id, $this->harness->resolve()->id);
    }

    public function test_a_super_admin_may_inspect_but_not_operate_a_suspended_reseller(): void
    {
        $admin = $this->user();
        $this->grantRole($admin, 'super-admin');

        $owner = $this->user();
        $reseller = $this->reseller($owner, 'suspended');

        Auth::login($admin);
        session(['active_reseller_id' => $reseller->id]);

        $this->assertSame($reseller->id, $this->harness->resolveOrNull()?->id, 'Read-only inspection stays allowed.');
        $this->assertSame('http:403', $this->statusOf(fn() => $this->harness->resolve()), 'Trading on its behalf is not.');
    }

    public function test_a_stale_session_reseller_id_falls_back_to_the_selector(): void
    {
        $admin = $this->user();
        $this->grantRole($admin, 'super-admin');

        Auth::login($admin);
        session(['active_reseller_id' => 999_999]);

        $this->assertNull($this->harness->resolveOrNull());
        $this->assertSame('redirect', $this->statusOf(fn() => $this->harness->resolve()));
    }

    public function test_a_user_with_no_reseller_row_is_denied(): void
    {
        $nobody = $this->user();

        Auth::login($nobody);

        $this->assertNull($this->harness->resolveOrNull());
        $this->assertSame('http:403', $this->statusOf(fn() => $this->harness->resolve()));
    }

    public function test_holding_the_reseller_role_without_ownership_grants_nothing(): void
    {
        $user = $this->user();
        $this->grantRole($user, 'reseller');

        // Someone else owns a reseller; this user owns none.
        $this->reseller($this->user());

        Auth::login($user);

        $this->assertNull(
            $this->harness->resolveOrNull(),
            'The role name alone is not a capability — ownership of a resellers row is.'
        );
        $this->assertSame('http:403', $this->statusOf(fn() => $this->harness->resolve()));
    }

    public function test_resolution_is_scoped_to_the_acting_user(): void
    {
        $ownerA = $this->user();
        $resellerA = $this->reseller($ownerA);

        $ownerB = $this->user();
        $resellerB = $this->reseller($ownerB);

        Auth::login($ownerA);

        $resolved = $this->harness->resolveOrNull();

        $this->assertSame($resellerA->id, $resolved?->id);
        $this->assertNotSame($resellerB->id, $resolved?->id);
    }
}
