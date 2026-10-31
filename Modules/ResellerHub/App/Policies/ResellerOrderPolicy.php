<?php

namespace Modules\ResellerHub\App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerOrder;

class ResellerOrderPolicy
{
    use HandlesAuthorization;

    protected function getResellerForUser(User $user): ?Reseller
    {
        return Reseller::where('user_id', $user->id)->orderBy('id')->first();
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('reseller') || $user->can('resellerhub.orders.view') || $user->can('reseller.orders.view');
    }

    public function view(User $user, ResellerOrder $order): bool
    {
        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && $user->can('resellerhub.admin.view'))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);

        return $reseller && $reseller->isActive() && (int)$order->reseller_id === (int)$reseller->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ResellerOrder $order): bool
    {
        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && $user->can('resellerhub.admin.manage'))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);
        if (!$reseller || !$reseller->isActive() || (int)$order->reseller_id !== (int)$reseller->id) {
            return false;
        }

        return $user->hasRole('reseller-owner') || $user->can('resellerhub.manage') || $user->can('resellerhub.orders.manage');
    }

    public function provision(User $user, ResellerOrder $order): bool
    {
        if (empty($order->tenant_id)) {
            return false;
        }

        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && $user->can('resellerhub.admin.manage'))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);
        if (!$reseller || !$reseller->isActive() || (int)$order->reseller_id !== (int)$reseller->id) {
            return false;
        }

        return $user->hasRole('reseller-owner') || $user->can('resellerhub.manage') || $user->can('resellerhub.provision');
    }

    public function delete(User $user, ResellerOrder $order): bool
    {
        return false;
    }
}
