<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Clients\Entities\Client;
use Modules\Projects\App\Http\Models\Project;
use Modules\Tasks\Entities\Task;
use Tests\TestCase;

class MultiTenancyDataIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected TenantManager $tenantManager;
    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantManager = app(TenantManager::class);

        // Setup Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'کلینیک الف',
            'subdomain' => 'clinic-alpha-' . uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->userA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مدیر کلینیک الف',
            'email' => 'admin-a-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        // Setup Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'کلینیک ب',
            'subdomain' => 'clinic-beta-' . uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'مدیر کلینیک ب',
            'email' => 'admin-b-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_records_created_in_tenant_a_are_automatically_tagged(): void
    {
        $this->tenantManager->setTenant($this->tenantA);

        $client = Client::create([
            'username' => 'client_a_' . uniqid(),
            'full_name' => 'بیمار الف',
            'phone' => '09121111111',
        ]);

        $task = Task::create([
            'title' => 'وظیفه الف',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        $project = Project::create([
            'title' => 'پروژه الف',
        ]);

        $this->assertEquals($this->tenantA->id, $client->tenant_id);
        $this->assertEquals($this->tenantA->id, $task->tenant_id);
        $this->assertEquals($this->tenantA->id, $project->tenant_id);
    }

    public function test_tenant_b_cannot_see_or_query_tenant_a_records(): void
    {
        // 1. Create records for Tenant A
        $this->tenantManager->setTenant($this->tenantA);

        $clientA = Client::create([
            'username' => 'client_sec_' . uniqid(),
            'full_name' => 'بیمار محرمانه الف',
            'phone' => '09122222222',
        ]);

        $taskA = Task::create([
            'title' => 'تسک محرمانه الف',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        $projectA = Project::create([
            'title' => 'پروژه محرمانه الف',
        ]);

        // Verify Tenant A sees their own records
        $this->assertEquals(1, Client::where('id', $clientA->id)->count());
        $this->assertEquals(1, Task::where('id', $taskA->id)->count());
        $this->assertEquals(1, Project::where('id', $projectA->id)->count());

        // 2. Switch context to Tenant B
        $this->tenantManager->setTenant($this->tenantB);

        // Tenant B must find 0 records of Tenant A
        $this->assertEquals(0, Client::where('id', $clientA->id)->count());
        $this->assertEquals(0, Task::where('id', $taskA->id)->count());
        $this->assertEquals(0, Project::where('id', $projectA->id)->count());

        // Direct find() must return NULL
        $this->assertNull(Client::find($clientA->id));
        $this->assertNull(Task::find($taskA->id));
        $this->assertNull(Project::find($projectA->id));
    }

    public function test_anti_tampering_blocks_cross_tenant_injection(): void
    {
        $this->tenantManager->setTenant($this->tenantA);

        $this->expectException(DomainException::class);

        // Attempt to create a task belonging to Tenant B while in Tenant A context
        $task = new Task();
        $task->title = 'تلاش برای نفوذ به مستاجر ب';
        $task->task_type = Task::TYPE_GENERAL;
        $task->status = Task::STATUS_TODO;
        $task->priority = Task::PRIORITY_HIGH;
        $task->tenant_id = $this->tenantB->id;
        $task->save();
    }

    public function test_anti_tampering_blocks_cross_tenant_update(): void
    {
        // 1. Create a task in Tenant A
        $this->tenantManager->setTenant($this->tenantA);
        $taskA = Task::create([
            'title' => 'تسک اصلی الف',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        // 2. Attempt to re-assign it to Tenant B
        $this->expectException(DomainException::class);

        $taskA->tenant_id = $this->tenantB->id;
        $taskA->save();
    }

    public function test_super_admin_bypass_allows_unscoped_queries(): void
    {
        // 1. Create record for Tenant A
        $this->tenantManager->setTenant($this->tenantA);
        $taskA = Task::create([
            'title' => 'تسک الف',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        // 2. Create record for Tenant B
        $this->tenantManager->setTenant($this->tenantB);
        $taskB = Task::create([
            'title' => 'تسک ب',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        // 3. Super admin bypass
        $allTasksCount = $this->tenantManager->bypass(function () {
            return Task::count();
        });

        $this->assertGreaterThanOrEqual(2, $allTasksCount);

        // 4. Scoping queries specifically withoutTenancy
        $tenantATasks = Task::withoutTenancy()->where('tenant_id', $this->tenantA->id)->count();
        $this->assertEquals(1, $tenantATasks);

        $tenantBTasks = Task::withoutTenancy()->where('tenant_id', $this->tenantB->id)->count();
        $this->assertEquals(1, $tenantBTasks);
    }

    public function test_route_model_binding_returns_404_when_accessing_another_tenants_task(): void
    {
        // 1. Create task in Tenant A
        $this->tenantManager->setTenant($this->tenantA);
        $taskA = Task::create([
            'title' => 'تسک کاملاً محرمانه کلینیک الف',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        // 2. Switch to Tenant B context, assign tasks.view permission & enable tasks module
        $this->tenantManager->setTenant($this->tenantB);
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'tasks.view', 'guard_name' => 'web']);
        $this->userB->givePermissionTo($permission);
        $this->tenantB->modules()->create([
            'module_slug' => 'tasks',
            'is_active' => true,
        ]);

        // 3. User B sends GET request to show Task A with Tenant B context
        $url = 'http://' . $this->tenantB->subdomain . '.crm-laravel.test/user/tasks/' . $taskA->id;
        $response = $this->actingAs($this->userB)->get($url);

        // Must be 404 because Task A does not exist in Tenant B scope
        $response->assertStatus(404);
    }

    public function test_path_based_routing_also_returns_404_when_accessing_another_tenants_task(): void
    {
        // 1. Create task in Tenant A
        $this->tenantManager->setTenant($this->tenantA);
        $taskA = Task::create([
            'title' => 'تسک محرمانه کلینیک الف در مسیر پث',
            'task_type' => Task::TYPE_GENERAL,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        // 2. Switch to Tenant B context
        $this->tenantManager->setTenant($this->tenantB);
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'tasks.view', 'guard_name' => 'web']);
        $this->userB->givePermissionTo($permission);
        $this->tenantB->modules()->create([
            'module_slug' => 'tasks',
            'is_active' => true,
        ]);

        // 3. User B sends GET request via Path routing: /{subdomain}/user/tasks/{id}
        $url = 'http://crm-laravel.test/' . $this->tenantB->subdomain . '/user/tasks/' . $taskA->id;
        $response = $this->actingAs($this->userB)->get($url);

        $response->assertStatus(404);
    }
}
