<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\SubAccounts;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerSubAccount;


final class SubAccountResolver
{
    public function resolve(Model $document): ?ResellerSubAccount
    {
        $resellerId = (int)$document->getAttribute('reseller_id');
        $tenantId = $document->getAttribute('tenant_id');

        if ($tenantId) {
            $subAccount = $this->fromTenant($document, (int)$tenantId);

            if ($subAccount) {
                return $subAccount;
            }
        }

        if ($document instanceof ResellerInvoice) {
            $fromMeta = $this->fromInvoiceMeta($document, $resellerId);

            if ($fromMeta) {
                return $fromMeta;
            }
        }

        $email = trim((string)$document->getAttribute('sub_account_email'));
        $mobile = trim((string)$document->getAttribute('sub_account_mobile'));

        if ($email === '' && $mobile === '') {
            return null;
        }

        $query = ResellerSubAccount::query()->where('reseller_id', $resellerId);

        if ($email !== '' && $mobile !== '') {
            return $query
                ->where(static fn($q) => $q->where('email', $email)->orWhere('mobile', $mobile))
                ->first();
        }

        return $email !== ''
            ? $query->where('email', $email)->first()
            : $query->where('mobile', $mobile)->first();
    }

    /**
     * Resolve the user that owns an invoice, never by a global lookup.
     */
    public function customerUser(ResellerInvoice $invoice): ?User
    {
        $subAccount = $this->resolve($invoice);

        if ($subAccount?->user_id) {
            $query = User::query();

            if ($subAccount->tenant_id) {
                $query->where('tenant_id', $subAccount->tenant_id);
            }

            $user = $query->find($subAccount->user_id);

            if ($user) {
                return $user;
            }
        }

        $tenant = $invoice->tenant;

        if ($tenant) {
            return User::query()
                ->where('tenant_id', $tenant->id)
                ->whereHas('roles', static fn($q) => $q->where('name', 'admin'))
                ->first();
        }

        return null;
    }

    private function fromTenant(Model $document, int $tenantId): ?ResellerSubAccount
    {
        if ($document->relationLoaded('tenant')) {
            /** @var Tenant|null $tenant */
            $tenant = $document->getRelation('tenant');

            return $tenant?->subAccount;
        }

        return ResellerSubAccount::query()->where('tenant_id', $tenantId)->first();
    }

    private function fromInvoiceMeta(ResellerInvoice $invoice, int $resellerId): ?ResellerSubAccount
    {
        $meta = $invoice->meta;
        $subAccountId = is_array($meta) ? ($meta['sub_account_id'] ?? null) : null;

        if (empty($subAccountId)) {
            return null;
        }

        return ResellerSubAccount::query()
            ->where('reseller_id', $resellerId)
            ->find((int)$subAccountId);
    }
}
