<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Support;

use App\Models\Tenant;
use App\Tenancy\TenantManager;

/**
 * Keeping the decision here means the module owns its own URL policy and shared
 * tenancy code needs no extra parameter for it.
 */
final class TenantSubdomainUrl
{
    public function __construct(private readonly TenantManager $tenants)
    {
    }

    public function for(Tenant $tenant, string $path = ''): string
    {
        $request = request();
        $protocol = ($request && $request->isSecure()) ? 'https://' : 'http://';

        $host = ($request && $request->getHttpHost())
            ? $request->getHttpHost()
            : ($this->tenants->getCentralDomains()[0] ?? '127.0.0.1:8000');

        $clean = ltrim($path, '/');

        return $protocol . $host . '/' . $tenant->subdomain . ($clean === '' ? '' : '/' . $clean);
    }
}
