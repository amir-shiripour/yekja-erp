<?php

declare(strict_types=1);

namespace App\Tenancy\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Support\Facades\Log;

class PropagateTenantContext
{
    public function __construct(
        public ?int $forcedTenantId = null
    )
    {
    }

    /**
     * Process the queued job within the tenant context.
     */
    public function handle(mixed $job, Closure $next): mixed
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $previousTenant = $tenantManager->getTenant();

        $targetTenantId = $this->forcedTenantId;

        if ($targetTenantId === null && isset($job->tenantId)) {
            $targetTenantId = $job->tenantId;
        } elseif ($targetTenantId === null && method_exists($job, 'getTenantId')) {
            $targetTenantId = $job->getTenantId();
        }

        if ($targetTenantId !== null) {
            $tenant = Tenant::find($targetTenantId);
            if ($tenant) {
                $tenantManager->setTenant($tenant);
            } else {
                Log::warning("[PropagateTenantContext] Tenant [ID: {$targetTenantId}] not found for job " . get_class($job));
            }
        }

        try {
            return $next($job);
        } finally {
            $tenantManager->setTenant($previousTenant);
        }
    }
}
