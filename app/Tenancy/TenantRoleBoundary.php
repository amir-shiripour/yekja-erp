<?php

namespace App\Tenancy;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class TenantRoleBoundary
{
    /**
     * Absolute role ceiling for any tenant context.
     * Subordinates inside their own workspace are ordinary admins and can never exceed this ceiling.
     */
    public const CEILING_ROLE = 'admin';

    /**
     * Permission prefixes strictly reserved for platform infrastructure / system administration.
     * Any role possessing ANY of these permissions is automatically disqualified from tenant workspaces,
     * regardless of its name.
     */
    protected array $platformPermissionPrefixes = [
        'resellerhub.',
        'roles.',
        'modules.',
        'tenants.',
        'registration-requests.',
        'menu.see.resellerhub',
        'menu.see.roles',
        'menu.see.registration-requests',
    ];

    /**
     * Get all roles allowed to be assigned within a tenant context.
     *
     * Rules:
     * 1. 'super-admin' is strictly excluded.
     * 2. 'admin' is explicitly allowed as the designated tenant ceiling.
     * 3. Any other system role is allowed ONLY IF it does not possess any platform infrastructure permissions.
     * 4. Any custom tenant role belonging to the given tenantId is allowed.
     *
     * @return Collection<string, string> [name => display_name]
     */
    public function getAllowedRoles(?int $tenantId = null): Collection
    {
        $prefixes = $this->platformPermissionPrefixes;

        return Role::where('name', '!=', 'super-admin')
            ->where(function ($query) use ($prefixes, $tenantId) {
                // System roles: must either be CEILING_ROLE ('admin') OR have NO platform permissions
                $query->where(function ($sys) use ($prefixes) {
                    $sys->whereNull('tenant_id')
                        ->where(function ($q) use ($prefixes) {
                            $q->where('name', self::CEILING_ROLE)
                                ->orWhereDoesntHave('permissions', function ($subQ) use ($prefixes) {
                                    $subQ->where(function ($sub) use ($prefixes) {
                                        foreach ($prefixes as $prefix) {
                                            $sub->orWhere('name', 'like', $prefix . '%');
                                        }
                                    });
                                });
                        });
                });

                // Custom tenant roles: strictly for THIS tenant
                if ($tenantId) {
                    $query->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderBy('name')
            ->pluck('display_name', 'name');
    }

    /**
     * Check if a given role name is within the tenant role boundary.
     */
    public function isRoleAllowed(string $roleName, ?int $tenantId = null): bool
    {
        return $this->getAllowedRoles($tenantId)->has($roleName);
    }

    /**
     * Validate an array of role names against the boundary.
     * Returns true if all roles are allowed, false if any role exceeds the ceiling.
     */
    public function areRolesAllowed(array $roleNames, ?int $tenantId = null): bool
    {
        if (empty($roleNames)) {
            return false;
        }

        $allowedKeys = $this->getAllowedRoles($tenantId)->keys()->all();
        $disallowed = array_diff($roleNames, $allowedKeys);

        return empty($disallowed);
    }

    /**
     * Check if a user possesses any role that exceeds the tenant ceiling.
     * For example, super-admin, reseller, or any custom role with platform permissions.
     */
    public function isUserOutsideCeiling(User $user, ?int $tenantId = null): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $userRoles = $user->roles()->pluck('name')->all();
        if (empty($userRoles)) {
            return false;
        }

        $allowedKeys = $this->getAllowedRoles($tenantId ?? $user->tenant_id)->keys()->all();
        $disallowed = array_diff($userRoles, $allowedKeys);

        return !empty($disallowed);
    }

    /**
     * Get all permissions that a tenant admin is permitted to assign to a custom role.
     * Excludes all platform/superadmin infrastructure permissions.
     * Optionally filters based on active modules purchased by the tenant.
     */
    public function getAllowedPermissions(?Tenant $tenant = null): Collection
    {
        $prefixes = $this->platformPermissionPrefixes;

        $query = Permission::query();

        // Exclude platform prefixes
        foreach ($prefixes as $prefix) {
            $query->where('name', 'not like', $prefix . '%');
        }

        $permissions = $query->orderBy('name')->get();

        if ($tenant) {
            $activeSlugs = array_map('strtolower', $tenant->activeModuleSlugs());

            // Core features always allowed (users, clients, tasks, menus, custom-fields, reminders, client-calls, etc.)
            $corePrefixes = [
                'users.', 'menu.see.users',
                'clients.', 'menu.see.clients',
                'tasks.', 'menu.see.tasks',
                'custom-fields.', 'menu.see.custom-fields',
                'reminders.', 'menu.see.reminders',
                'client-calls.', 'menu.see.client-calls',
                'followups.', 'menu.see.followups',
                'calendar.', 'menu.see.calendar',
            ];

            $permissions = $permissions->filter(function ($perm) use ($activeSlugs, $corePrefixes) {
                // If it's a core permission, always allow
                foreach ($corePrefixes as $cp) {
                    if (str_starts_with($perm->name, $cp)) {
                        return true;
                    }
                }

                // Check if permission belongs to an active module
                $parts = explode('.', $perm->name, 2);
                $moduleCandidate = strtolower($parts[0]);

                return in_array($moduleCandidate, $activeSlugs, true);
            })->values();
        }

        return $permissions;
    }
}
