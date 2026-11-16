<?php

namespace App\Tenancy\Scopes;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantScope implements Scope
{
    protected static array $hasColumnCache = [];
    protected static array $loggedWarnings = [];

    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        if ($tenantManager->isBypassed()) {
            return;
        }

        if (app()->runningInConsole() && !$tenantManager->hasTenant()) {
            return;
        }

        $table = $model->getTable();
        if (!isset(static::$hasColumnCache[$table])) {
            static::$hasColumnCache[$table] = Schema::hasColumn($table, 'tenant_id');
        }

        if (!static::$hasColumnCache[$table]) {
            $warningKey = $table . '_' . $tenantManager->getTenantId();
            if ($tenantManager->hasTenant() && !isset(static::$loggedWarnings[$warningKey])) {
                static::$loggedWarnings[$warningKey] = true;
                Log::warning(
                    "[TenantScope] Table '{$table}' is missing 'tenant_id' column in active tenant context [{$tenantManager->getTenantId()}]. Run 'php artisan tenancy:isolate-tables'."
                );
            }
            return;
        }

        if ($tenantManager->hasTenant()) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantManager->getTenantId());
        } else {
            $builder->whereNull($model->qualifyColumn('tenant_id'));
        }
    }


    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        $builder->macro('forTenant', function (Builder $builder, $tenant) {
            $tenantId = $tenant instanceof Tenant ? $tenant->id : (int) $tenant;
            return $builder->withoutGlobalScope(TenantScope::class)
                           ->where($builder->getModel()->qualifyColumn('tenant_id'), $tenantId);
        });
        $builder->macro('forDirectSystem', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class)
                           ->whereNull($builder->getModel()->qualifyColumn('tenant_id'));
        });
    }
}
