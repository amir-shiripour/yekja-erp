<?php

namespace Modules\ResellerHub\App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;

class ResellerInvoicePolicy
{
    use HandlesAuthorization;

    protected function getResellerForUser(User $user): ?Reseller
    {
        return Reseller::where('user_id', $user->id)->orderBy('id')->first();
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('reseller') || $user->can('resellerhub.invoices.view') || $user->can('reseller.invoices.view');
    }

    public function view(User $user, ResellerInvoice $invoice): bool
    {
        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && $user->can('resellerhub.admin.view'))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);
        if ($reseller && $reseller->isActive() && (int)$invoice->reseller_id === (int)$reseller->id) {
            return true;
        }


        if ((int)$invoice->created_by === (int)$user->id) {
            return true;
        }

        if (!empty($invoice->user_id) && (int)$invoice->user_id === (int)$user->id) {
            return true;
        }

        if (!empty($user->tenant_id) && (int)$invoice->tenant_id === (int)$user->tenant_id) {
            return true;
        }

        if ($invoice->tenant && $invoice->tenant->subAccount && (int)$invoice->tenant->subAccount->user_id === (int)$user->id) {
            return true;
        }

        return false;
    }

    public function isResellerOwnerOrSuperAdmin(User $user, ResellerInvoice $invoice): bool
    {
        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && $user->can('resellerhub.admin.manage'))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);
        return $reseller && $reseller->isActive() && (int)$invoice->reseller_id === (int)$reseller->id;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('super-admin') || (method_exists($user, 'can') && ($user->can('resellerhub.manage') || $user->can('resellerhub.invoices.create')))) {
            return true;
        }

        $reseller = $this->getResellerForUser($user);
        return $reseller && $reseller->isActive();
    }

    public function update(User $user, ResellerInvoice $invoice): bool
    {
        return $this->isResellerOwnerOrSuperAdmin($user, $invoice) && !$invoice->isPaid() && !$invoice->isCancelled();
    }

    public function updateStatus(User $user, ResellerInvoice $invoice): bool
    {
        return $this->isResellerOwnerOrSuperAdmin($user, $invoice);
    }

    public function delete(User $user, ResellerInvoice $invoice): bool
    {
        if (!$this->isResellerOwnerOrSuperAdmin($user, $invoice)) {
            return false;
        }

        if ($invoice->isPaid() || $invoice->calculatePaidAmount() > 0) {
            return false;
        }

        if ($invoice->order()->exists()) {
            return false;
        }

        return true;
    }

    public function pay(User $user, ResellerInvoice $invoice): bool
    {
        if (!$this->view($user, $invoice)) {
            return false;
        }

        if ($invoice->isCancelled() || $invoice->isPaid()) {
            return false;
        }

        return true;
    }

    public function cancel(User $user, ResellerInvoice $invoice): bool
    {
        if (!$this->isResellerOwnerOrSuperAdmin($user, $invoice)) {
            return false;
        }

        if ($invoice->isPaid()) {
            return false;
        }

        return true;
    }

    public function cancelPayment(User $user, ResellerInvoice $invoice, ?ResellerInvoicePayment $payment = null): bool
    {
        if (!$this->isResellerOwnerOrSuperAdmin($user, $invoice)) {
            return false;
        }

        if ($payment && (int)$payment->invoice_id !== (int)$invoice->id) {
            return false;
        }

        return true;
    }

    public function renew(User $user, ResellerInvoice $invoice): bool
    {
        if (!$this->isResellerOwnerOrSuperAdmin($user, $invoice)) {
            return false;
        }

        return !$invoice->isCancelled();
    }
}
