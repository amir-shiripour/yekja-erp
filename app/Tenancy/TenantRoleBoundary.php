<?php

namespace App\Tenancy;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

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
     * 3. Any other role is allowed ONLY IF it does not possess any platform infrastructure permissions.
     *
     * @return Collection<string, string> [name => display_name]
     */
    public function getAllowedRoles(): Collection
    {
        $prefixes = $this->platformPermissionPrefixes;

        return Role::where('name', '!=', 'super-admin')
            ->where(function ($query) use ($prefixes) {
                $query->where('name', self::CEILING_ROLE)
                    ->orWhereDoesntHave('permissions', function ($q) use ($prefixes) {
                        $q->where(function ($sub) use ($prefixes) {
                            foreach ($prefixes as $prefix) {
                                $sub->orWhere('name', 'like', $prefix . '%');
                            }
                        });
                    });
            })
            ->orderBy('name')
            ->pluck('display_name', 'name');
    }

    /**
     * Check if a given role name is within the tenant role boundary.
     */
    public function isRoleAllowed(string $roleName): bool
    {
        return $this->getAllowedRoles()->has($roleName);
    }

    /**
     * Validate an array of role names against the boundary.
     * Returns true if all roles are allowed, false if any role exceeds the ceiling.
     */
    public function areRolesAllowed(array $roleNames): bool
    {
        if (empty($roleNames)) {
            return false;
        }

        $allowedKeys = $this->getAllowedRoles()->keys()->all();
        $disallowed = array_diff($roleNames, $allowedKeys);

        return empty($disallowed);
    }

    /**
     * Check if a user possesses any role that exceeds the tenant ceiling.
     * For example, super-admin, reseller, or any custom role with platform permissions.
     */
    public function isUserOutsideCeiling(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $userRoles = $user->roles()->pluck('name')->all();
        if (empty($userRoles)) {
            return false;
        }

        $allowedKeys = $this->getAllowedRoles()->keys()->all();
        $disallowed = array_diff($userRoles, $allowedKeys);

        return !empty($disallowed);
    }
}
