<?php

namespace App\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'tenant_id',
    ];

    /**
     * Relationship to tenant if custom role.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to roles created specifically for a given tenant.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope query to roles available for a given tenant (system global templates + tenant's own custom roles).
     */
    public function scopeAvailableForTenant(Builder $query, ?int $tenantId): Builder
    {
        if (!$tenantId) {
            return $query->whereNull('tenant_id');
        }

        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
                ->orWhereNull('tenant_id');
        });
    }

    /**
     * Check if this role is a tenant custom role.
     */
    public function isCustom(): bool
    {
        return $this->tenant_id !== null;
    }

    /**
     * Check if this role is a system template role.
     */
    public function isSystem(): bool
    {
        return $this->tenant_id === null;
    }
}
