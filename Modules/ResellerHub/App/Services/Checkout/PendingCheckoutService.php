<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Checkout;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Actions\Invoices\CancelInvoiceAction;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Throwable;

final class PendingCheckoutService
{
    public function __construct(private readonly CancelInvoiceAction $cancelInvoice)
    {
    }

    public function ttlMinutes(): int
    {
        return max(15, (int)config('resellerhub.checkout.reservation_ttl_minutes', 60));
    }

    public function expiry(): Carbon
    {
        return now()->addMinutes($this->ttlMinutes());
    }

    /**
     * Find an existing reusable pending_payment tenant reservation for this user.
     */
    public function findReusable(User $user, ?int $resellerId): ?Tenant
    {
        return Tenant::query()
            ->where('status', Tenant::STATUS_PENDING_PAYMENT)
            ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
            ->where(function ($q) use ($user): void {
                $q->whereHas('subAccount', fn($s) => $s->where('user_id', $user->id)
                    ->orWhere('mobile', $user->mobile))
                    ->orWhereHas('users', fn($u) => $u->where('id', $user->id));
            })
            ->where(function ($q): void {
                $q->whereNull('checkout_expires_at')
                    ->orWhere('checkout_expires_at', '>', now());
            })
            // Reservations that already received settled payments must never be reused
            ->whereDoesntHave('invoices', fn($q) => $q->where('status', InvoiceStatus::PAID->value))
            ->whereDoesntHave('invoices.payments', fn($q) => $q->whereIn('status', ResellerInvoicePayment::SETTLED_STATUSES))
            ->lockForUpdate()
            ->latest('id')
            ->first();
    }

    /**
     * Mark a tenant as pending_payment and reset its checkout expiration.
     */
    public function markPending(Tenant $tenant, array $attributes): Tenant
    {
        $tenant->fill($attributes);
        $tenant->status = Tenant::STATUS_PENDING_PAYMENT;
        $tenant->checkout_expires_at = $this->expiry();
        $tenant->save();

        return $tenant;
    }

    /**
     * Cancel any existing open/unpaid invoices on the tenant to prevent double-billing.
     */
    public function releaseOpenInvoices(Tenant $tenant): int
    {
        $count = 0;

        foreach ($tenant->invoices()->whereIn('status', [
            InvoiceStatus::UNPAID->value,
            InvoiceStatus::OVERDUE->value,
            InvoiceStatus::DRAFT->value,
        ])->get() as $invoice) {
            try {
                $this->cancelInvoice->execute($invoice);
                $count++;
            } catch (Throwable $e) {
                Log::warning('[PendingCheckout] invoice release failed', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Reclaim stale pending reservations whose expiration time plus grace period has passed.
     *
     * @return array<int, array{id: int, subdomain: string|null}>
     */
    public function expireStale(int $limit = 200, bool $dryRun = false): array
    {
        $expired = [];

        Tenant::query()
            ->where('status', Tenant::STATUS_PENDING_PAYMENT)
            ->whereNotNull('checkout_expires_at')
            ->where('checkout_expires_at', '<', now()->subMinutes(10)) // 10 minute safety buffer
            ->whereDoesntHave('invoices', fn($q) => $q->where('status', InvoiceStatus::PAID->value))
            ->whereDoesntHave('invoices.payments', fn($q) => $q->whereIn('status', ResellerInvoicePayment::SETTLED_STATUSES))
            ->whereDoesntHave('invoices.payments', fn($q) => $q->where('status', ResellerInvoicePayment::STATUS_PENDING))
            ->orderBy('id')
            ->limit($limit)
            ->chunkById(50, function ($tenants) use (&$expired, $dryRun): void {
                foreach ($tenants as $tenant) {
                    $expired[] = ['id' => $tenant->id, 'subdomain' => $tenant->subdomain];

                    if ($dryRun) {
                        continue;
                    }

                    DB::transaction(function () use ($tenant): void {
                        $this->releaseOpenInvoices($tenant);

                        ResellerSubAccount::where('tenant_id', $tenant->id)
                            ->update(['tenant_id' => null]);

                        // Rename subdomain and clear custom_domain so unique index is immediately freed
                        $tenant->forceFill([
                            'subdomain' => Str::limit((string)$tenant->subdomain, 40, '') . '-exp-' . $tenant->id,
                            'custom_domain' => null,
                        ])->save();

                        $tenant->delete();
                    });
                }
            });

        return $expired;
    }
}
