<?php

declare(strict_types=1);

namespace App\Tenancy\Traits;

use App\Models\Tenant;
use App\Tenancy\Middleware\PropagateTenantContext;
use App\Tenancy\TenantManager;

trait TenantAwareJob
{
    /**
     * The ID of the tenant this job belongs to.
     */
    public ?int $tenantId = null;

    /**
     * Attach a tenant to this job instance.
     */
    public function withTenant(Tenant|int|null $tenant = null): static
    {
        if ($tenant instanceof Tenant) {
            $this->tenantId = $tenant->id;
        } elseif (is_numeric($tenant)) {
            $this->tenantId = (int)$tenant;
        } elseif (app()->bound(TenantManager::class)) {
            $this->tenantId = app(TenantManager::class)->getTenantId();
        }

        return $this;
    }

    /**
     * Automatically capture the current active tenant if not explicitly specified.
     */
    public function captureTenantContext(): static
    {
        if ($this->tenantId === null && app()->bound(TenantManager::class)) {
            $this->tenantId = app(TenantManager::class)->getTenantId();
        }

        return $this;
    }

    /**
     * Get the tenant ID for this job.
     */
    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    /**
     * Job middleware that restores the tenant context during execution.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new PropagateTenantContext($this->tenantId)];
    }
}
