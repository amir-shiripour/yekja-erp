<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\File;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Several reseller screens eager-load `owner`; when the relation was missing
 * they all returned 500. This pins the relation contract and stops a future
 * controller from eager-loading a relation that does not exist.
 */
class TenantOwnerRelationTest extends TestCase
{
    public function test_a_tenant_exposes_its_owner_through_the_sub_account(): void
    {
        $relation = (new Tenant())->owner();

        $this->assertInstanceOf(HasOneThrough::class, $relation);
        $this->assertSame('users', $relation->getRelated()->getTable());
        // Proves the join path: reseller_sub_accounts.user_id → users.id
        $this->assertSame('reseller_sub_accounts.user_id', $relation->getQualifiedParentKeyName());
    }

    public function test_the_module_never_eager_loads_a_relation_the_tenant_does_not_have(): void
    {
        $tenant = new Tenant();
        $files = collect(File::allFiles(module_path('ResellerHub', 'App')))
            ->filter(static fn($file): bool => $file->getExtension() === 'php');

        $offenders = [];

        foreach ($files as $file) {
            $contents = File::get($file->getPathname());

            // Only `$tenant->load([...])` / `$tenant->loadMissing([...])` — a
            // relation list loaded on a different model is not our business.
            if (!preg_match_all('/\$tenant->load(?:Missing)?\(\s*\[([^\]]*)\]/', $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $list) {
                foreach (explode(',', $list) as $candidate) {
                    $relation = trim($candidate, " \t\n\r\0\x0B'\"");

                    // Nested eager loads such as 'subAccount.user' are checked
                    // by their root segment only.
                    $relation = explode('.', $relation)[0];

                    if ($relation === '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $relation)) {
                        continue;
                    }

                    if (!method_exists($tenant, $relation)) {
                        $offenders[] = $file->getRelativePathname() . ' → ' . $relation;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These relations do not exist on App\\Models\\Tenant:\n- " . implode("\n- ", array_unique($offenders))
        );
    }
}
