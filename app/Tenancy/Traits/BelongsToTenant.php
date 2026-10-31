<?php

namespace App\Tenancy\Traits;

use App\Models\Tenant;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenantManager;
use DomainException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{

    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isBypassed()) {
                return;
            }

            if (empty($model->tenant_id) && $tenantManager->hasTenant()) {
                $model->tenant_id = $tenantManager->getTenantId();
            }

            // Fail-Fast Guard: Never allow an empty tenant_id in an active tenant context
            if ($tenantManager->hasTenant() && empty($model->tenant_id)) {
                throw new DomainException(
                    "Security Violation: Cannot persist " . get_class($model) . " without tenant_id in active tenant context [" . $tenantManager->getTenantId() . "]."
                );
            }
        });

        static::saving(function ($model) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isBypassed()) {
                return;
            }

            // Anti-Tampering Guard: Ensure record strictly belongs to the current tenant
            if ($tenantManager->hasTenant()) {
                if (empty($model->tenant_id)) {
                    $model->tenant_id = $tenantManager->getTenantId();
                } elseif ((int)$model->tenant_id !== (int)$tenantManager->getTenantId()) {
                    throw new DomainException(
                        "Security Violation: Cross-tenant tampering! Attempted to persist " . get_class($model) . " with tenant_id [" . $model->tenant_id . "] in tenant context [" . $tenantManager->getTenantId() . "]."
                    );
                }
            }
        });
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function isDirect(): bool
    {
        return empty($this->tenant_id);
    }
}
