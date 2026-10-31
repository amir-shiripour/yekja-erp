<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;

class RenewalInvoiceService
{
    protected ResellerBillingService $billingService;

    public function __construct(ResellerBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Create a renewal invoice for a tenant, unifying order-based and direct tenant paths.
     */
    public function createRenewalInvoice(
        Tenant  $tenant,
        int     $months,
        ?int    $resellerId = null,
        ?string $notes = null,
        ?int    $createdBy = null
    ): ResellerInvoice
    {

        $tenant->loadMissing(['package', 'orders.invoice', 'subAccount']);
        $latestOrder = $tenant->orders()->latest('id')->first();
        $resellerId = (int)($tenant->reseller_id ?: $resellerId);

        if ($latestOrder) {
            return $this->billingService->createRenewalInvoiceFromOrder($latestOrder, [
                'months' => $months,
                'manual' => true,
                'notes' => $notes,
            ]);
        }

        return DB::transaction(function () use ($tenant, $months, $resellerId, $notes, $createdBy) {
            $pkg = $tenant->package;
            $price = $pkg && method_exists($pkg, 'getPriceForMonths') ? $pkg->getPriceForMonths($months) : 0;
            $cycleLabel = reseller_duration_label($months, true);

            $baseDate = ($tenant->plan_expires_at && Carbon::now()->lessThan($tenant->plan_expires_at))
                ? Carbon::parse($tenant->plan_expires_at)
                : Carbon::now();
            $dueDate = reseller_add_jalali_months($baseDate, $months);

            $renewalInvoice = ResellerInvoice::create([
                'reseller_id' => $resellerId,
                'tenant_id' => $tenant->id,
                'package_id' => $tenant->package_id,
                'invoice_number' => ResellerInvoice::generateNextNumber($resellerId),
                'type' => ResellerInvoice::TYPE_OFFICIAL,
                'status' => ResellerInvoice::STATUS_UNPAID,
                'sub_account_name' => $tenant->subAccount?->name ?: $tenant->name,
                'sub_account_mobile' => $tenant->subAccount?->mobile ?? null,
                'sub_account_email' => $tenant->subAccount?->email ?? null,
                'issue_date' => now(),
                'due_date' => $dueDate,
                'tax_percent' => 0,
                'subtotal' => $price,
                'total' => $price,
                'paid_amount' => 0,
                'notes' => $notes ?: __('resellerhub::messages.invoices.subscription_renewal_note', ['name' => $tenant->name]),
                'meta' => [
                    'is_renewal' => true,
                    'duration_months' => $months,
                    'period_label' => $cycleLabel,
                ],
                'created_by' => $createdBy ?: auth()->id(),
            ]);

            ResellerInvoiceItem::create([
                'invoice_id' => $renewalInvoice->id,
                'item_type' => 'package',
                'item_key' => $tenant->package_id ? ('package_' . $tenant->package_id) : 'subscription_renewal',
                'title' => __('resellerhub::messages.invoices.subscription_renewal_title', ['cycle' => $cycleLabel]),
                'duration_months' => $months,
                'quantity' => 1,
                'unit_price' => $price,
                'total_price' => $price,
            ]);

            return $renewalInvoice;
        });
    }
}
