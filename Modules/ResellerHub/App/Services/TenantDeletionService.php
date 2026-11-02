<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Models\TenantOnboardingState;
use Throwable;

class TenantDeletionService
{
    /**
     * Completely and permanently hard-delete a Tenant and all its associated artifacts.
     *
     * @param Tenant $tenant
     * @return void
     * @throws Throwable
     */
    public function hardDeleteTenant(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $tenantId = (int)$tenant->id;
            Log::info("TenantDeletionService: Beginning hard-delete for Tenant #{$tenantId} ({$tenant->subdomain})");

            //Clean up stored branding files from public disk if present
            $branding = (array)($tenant->branding ?? []);
            if (!empty($branding['brand_logo'])) {
                $this->deleteStoredFile((string)$branding['brand_logo']);
            }
            if (!empty($branding['brand_favicon'])) {
                $this->deleteStoredFile((string)$branding['brand_favicon']);
            }

            //Delete tenant onboarding states
            if (Schema::hasTable('tenant_onboarding_states')) {
                TenantOnboardingState::where('tenant_id', $tenantId)->delete();
            }

            //Delete tenant module subscriptions
            if (Schema::hasTable('tenant_modules')) {
                TenantModule::where('tenant_id', $tenantId)->delete();
            }

            //Clean up tenant-scoped users (admin/staff created inside this tenant)
            if (Schema::hasTable('users')) {
                $tenantUsers = User::where('tenant_id', $tenantId)->get();
                foreach ($tenantUsers as $u) {
                    if (Schema::hasTable('personal_access_tokens') && method_exists($u, 'tokens')) {
                        $u->tokens()->delete();
                    }
                    if (Schema::hasTable('model_has_roles') && method_exists($u, 'roles')) {
                        $u->roles()->detach();
                    }
                    $u->forceDelete();
                }
            }

            //Unbind financial records so historical ledgers don't break constraints
            if (Schema::hasTable('reseller_invoices')) {
                ResellerInvoice::where('tenant_id', $tenantId)->update(['tenant_id' => null]);
            }
            if (Schema::hasTable('reseller_orders')) {
                ResellerOrder::where('tenant_id', $tenantId)->update(['tenant_id' => null]);
            }

            //Unbind any sub-accounts pointing directly to this tenant
            if (Schema::hasTable('reseller_sub_accounts')) {
                ResellerSubAccount::where('tenant_id', $tenantId)->update(['tenant_id' => null]);
            }

            //Finally, force delete the tenant record
            $tenant->forceDelete();

            Log::info("TenantDeletionService: Successfully hard-deleted Tenant #{$tenantId}");
        });
    }

    /**
     * Completely and permanently hard-delete a SubAccount.
     * If $forceDeleteTenants is true, also cascades hard-deletion to all connected tenants.
     * @param ResellerSubAccount $subAccount
     * @param bool $forceDeleteTenants
     * @return array{deleted_sub_account_id: int, deleted_tenants_count: int}
     * @throws Throwable
     */
    public function hardDeleteSubAccount(ResellerSubAccount $subAccount, bool $forceDeleteTenants = false): array
    {
        $connectedTenants = $this->getConnectedTenants($subAccount);

        if ($connectedTenants->isNotEmpty() && !$forceDeleteTenants) {
            throw new ResellerHubException(
                __('resellerhub::messages.subaccounts.delete_conflict_has_tenants', [
                    'count' => ResellerHelper::toPersianDigits($connectedTenants->count())
                ])
            );
        }

        return DB::transaction(function () use ($subAccount, $connectedTenants, $forceDeleteTenants) {
            $subAccountId = (int)$subAccount->id;
            $deletedTenantsCount = 0;

            Log::info("TenantDeletionService: Beginning hard-delete for SubAccount #{$subAccountId}");

            // If forceDeleteTenants is requested, hard delete every connected tenant
            if ($forceDeleteTenants && $connectedTenants->isNotEmpty()) {
                foreach ($connectedTenants as $tenant) {
                    $this->hardDeleteTenant($tenant);
                    $deletedTenantsCount++;
                }
            }

            //Unlink any other tenant that might reference this sub_account_id
            if (Schema::hasTable('tenants')) {
                Tenant::where('sub_account_id', $subAccountId)->update(['sub_account_id' => null]);
            }

            //Handle subaccount dedicated user if one exists and has no tenant binding
            if ($subAccount->user_id && Schema::hasTable('users')) {
                $user = User::find($subAccount->user_id);
                if ($user && $user->tenant_id === null) {
                    if (Schema::hasTable('model_has_roles') && method_exists($user, 'roles')) {
                        $user->roles()->detach();
                    }
                    if (Schema::hasTable('personal_access_tokens') && method_exists($user, 'tokens')) {
                        $user->tokens()->delete();
                    }
                    $user->delete();
                }
            }

            //Force delete the SubAccount
            $subAccount->forceDelete();

            Log::info("TenantDeletionService: Successfully hard-deleted SubAccount #{$subAccountId} (Cascaded {$deletedTenantsCount} tenants)");

            return [
                'deleted_sub_account_id' => $subAccountId,
                'deleted_tenants_count' => $deletedTenantsCount,
            ];
        });
    }

    /**
     * Retrieve all tenants connected to the given sub-account.
     *
     * @param ResellerSubAccount $subAccount
     * @return Collection<int, Tenant>
     */
    public function getConnectedTenants(ResellerSubAccount $subAccount): Collection
    {
        return Tenant::query()
            ->where('sub_account_id', $subAccount->id)
            ->when($subAccount->tenant_id, function ($q) use ($subAccount) {
                $q->orWhere('id', $subAccount->tenant_id);
            })
            ->get();
    }

    /**
     * Delete stored file from public disk if present.
     */
    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            try {
                Storage::disk('public')->delete($path);
            } catch (Throwable $e) {
                Log::warning("TenantDeletionService: Could not delete stored file [{$path}]: " . $e->getMessage());
            }
        }
    }
}
