<?php

namespace Modules\ResellerHub\App\Services\Billing;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\BillingCalculator;

class UpgradeInvoiceService
{
    public function __construct(
        protected SubscriptionProrationCalculator $prorationCalculator,
        protected OrderProvisioningService        $orderProvisioningService,
    )
    {
    }

    /**
     * Create an upgrade invoice from an existing order to a new package.
     */
    public function createUpgradeInvoiceFromOrder(ResellerOrder $order, int $newPackageId, array $options = []): ResellerInvoice
    {
        $resellerId = $order->reseller_id;
        $order->loadMissing(['tenant', 'invoice.items']);

        $newPackage = ResellerPackage::where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
                ->orWhereNull('reseller_id');
        })->findOrFail($newPackageId);

        $oldPackage = $order->package ?? ($order->tenant?->package_id ? ResellerPackage::find($order->tenant->package_id) : null);

        if ($oldPackage && (int)$oldPackage->id === (int)$newPackage->id) {
            throw new InvalidArgumentException('پکیج انتخابی همان پکیج فعلی سفارش است.');
        }

        $cycle = $options['billing_cycle'] ?? ($order->billing_cycle ?? 'yearly');
        $months = is_numeric($cycle)
            ? (int)$cycle
            : (isset($options['duration_months']) && is_numeric($options['duration_months'])
                ? (int)$options['duration_months']
                : BillingCycle::resolveMonthFromPeriodKey($cycle));
        if ($months < 1) {
            $months = 12;
        }

        if ($oldPackage && !$newPackage->isUpgradeOver($oldPackage)) {
            throw new InvalidArgumentException('ارتقای پکیج فقط به پکیجی با تعرفه بالاتر از پکیج فعلی امکان‌پذیر است.');
        }

        $cycleLabel = ResellerHelper::durationLabel($months, true);

        $rawNewPackageCyclePrice = $newPackage->getPriceForMonths($months);
        $newPackageCyclePrice = $newPackage->getFinalPriceForMonths($months);
        $oldPackageCyclePrice = $oldPackage ? $oldPackage->getPriceForMonths($months) : (int)$order->total_amount;
        $proration = $this->prorationCalculator->calculate($order, $newPackage, $months);
        $fullCycleDiff = $proration->fullDifference;
        $proratedDiff = $proration->proratedAmount;
        $remainingDays = $proration->remainingDays;
        $totalDays = $proration->totalDays;

        $calcMode = $options['calculation_mode'] ?? 'prorated';
        if ($calcMode === 'full') {
            $itemSubtotal = $fullCycleDiff;
        } elseif ($calcMode === 'custom' && isset($options['custom_price']) && is_numeric($options['custom_price'])) {
            $itemSubtotal = max(0, (int)$options['custom_price']);
        } else {
            // Prorated default when days are left
            $itemSubtotal = ($remainingDays > 0) ? $proratedDiff : $fullCycleDiff;
        }

        // If explicit upgrade_price is provided
        if (isset($options['upgrade_price']) && is_numeric($options['upgrade_price'])) {
            $itemSubtotal = max(0, (int)$options['upgrade_price']);
        }

        $taxPercent = (float)($order->invoice?->tax_percent ?? 0);
        $taxMode = (string)($order->invoice?->meta['tax_mode'] ?? BillingCalculator::TAX_MODE_INVOICE);
        $itemTax = BillingCalculator::calculateTax($itemSubtotal, $taxPercent);
        $itemTotal = $itemSubtotal + $itemTax;

        $invoiceNumber = ResellerInvoice::generateNextNumber($resellerId, ResellerInvoice::TYPE_OFFICIAL);
        $dueDate = now()->addDays(isset($options['due_date_offset']) ? (int)$options['due_date_offset'] : 3);
        $oldPackageName = $oldPackage?->name ?? 'طرح قبلی';

        $upgradeInvoice = DB::transaction(function () use (
            $resellerId, $order, $newPackage, $oldPackage, $oldPackageName, $invoiceNumber, $dueDate,
            $taxPercent, $taxMode, $itemSubtotal, $itemTax, $itemTotal, $cycle, $cycleLabel, $months, $options,
            $calcMode, $fullCycleDiff, $proratedDiff, $remainingDays, $totalDays, $newPackageCyclePrice, $rawNewPackageCyclePrice, $oldPackageCyclePrice
        ) {
            $inv = ResellerInvoice::create([
                'reseller_id' => $resellerId,
                'tenant_id' => $order->tenant_id,
                'parent_invoice_id' => $order->invoice_id,
                'source_order_id' => $order->id,
                'source' => 'tenant_upgrade_request',
                'package_id' => $newPackage->id,
                'invoice_number' => $invoiceNumber,
                'type' => $options['type'] ?? ResellerInvoice::TYPE_OFFICIAL,
                'status' => $itemTotal === 0 ? ResellerInvoice::STATUS_PAID : ResellerInvoice::STATUS_UNPAID,
                'paid_at' => $itemTotal === 0 ? now() : null,
                'sub_account_name' => $order->sub_account_name,
                'sub_account_mobile' => $order->sub_account_mobile,
                'sub_account_email' => $order->sub_account_email,
                'issue_date' => now(),
                'due_date' => $dueDate,
                'tax_percent' => $taxPercent,
                'subtotal' => $itemSubtotal,
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_amount' => $itemTax,
                'total' => $itemTotal,
                'paid_amount' => 0,
                'notes' => $options['notes'] ?? "فاکتور ارتقای پکیج سفارش " . ResellerHelper::toPersianDigits($order->order_number) . " از «{$oldPackageName}» به «{$newPackage->name}»",
                'meta' => [
                    'source' => 'tenant_upgrade_request',
                    'source_label' => 'خرید آنلاین از سایت',
                    'is_upgrade' => true,
                    'tax_mode' => $taxMode,
                    'source_order_id' => $order->id,
                    'source_invoice_id' => $order->invoice_id,
                    'from_package_id' => $oldPackage?->id,
                    'to_package_id' => $newPackage->id,
                    'from_package_name' => $oldPackageName,
                    'to_package_name' => $newPackage->name,
                    'calculation_mode' => $calcMode,
                    'full_difference' => $fullCycleDiff,
                    'prorated_difference' => $proratedDiff,
                    'remaining_days' => $remainingDays,
                    'total_days' => $totalDays,
                    'billing_cycle' => $cycle,
                    'duration_months' => $months,
                    'old_package_price' => $oldPackageCyclePrice,
                    'new_package_price' => $newPackageCyclePrice,
                    'new_package_raw_price' => $rawNewPackageCyclePrice,
                    'has_package_discount' => $newPackage->isSubscription() ? $newPackage->hasActiveDiscountForMonths($months) : $newPackage->hasActiveDiscount(),
                ],
                'created_by' => auth()->id() ?? $order->created_by,
            ]);

            $remDaysFa = ResellerHelper::toPersianDigits($remainingDays);
            $descSuffix = ($calcMode === 'prorated' && $remainingDays > 0)
                ? " (محاسبه برای {$remDaysFa} روز باقیمانده از دوره {$cycleLabel})"
                : " (مابه‌التفاوت دوره {$cycleLabel})";

            $hasPackageDiscount = ($newPackage->isSubscription() ? $newPackage->hasActiveDiscountForMonths($months) : $newPackage->hasActiveDiscount()) && $newPackageCyclePrice < $rawNewPackageCyclePrice;
            $discountTitleSuffix = $hasPackageDiscount ? ' (با تخفیف ویژه پکیج)' : '';

            ResellerInvoiceItem::create([
                'invoice_id' => $inv->id,
                'item_type' => 'package_upgrade',
                'item_key' => 'package_' . $newPackage->id,
                'title' => "ارتقای پکیج به «{$newPackage->name}»" . $discountTitleSuffix,
                'description' => "مابه‌التفاوت ارتقای اشتراک از پکیج «{$oldPackageName}» به «{$newPackage->name}»{$descSuffix}",
                'duration_months' => $months,
                'quantity' => 1,
                'unit_price' => $itemSubtotal,
                'discount_amount' => 0,
                'total_price' => $itemSubtotal,
                'meta' => [
                    'is_upgrade_item' => true,
                    'from_package_id' => $oldPackage?->id,
                    'to_package_id' => $newPackage->id,
                    'remaining_days' => $remainingDays,
                    'has_package_discount' => $hasPackageDiscount,
                ],
            ]);

            // Add all modules of the new package as zero-cost bundled items in the invoice
            if (is_array($newPackage->modules)) {
                foreach ($newPackage->modules as $mSlug) {
                    $modTitle = ResellerHelper::getModuleTitle($mSlug);
                    ResellerInvoiceItem::create([
                        'invoice_id' => $inv->id,
                        'item_type' => 'module',
                        'item_key' => $mSlug,
                        'title' => $modTitle,
                        'description' => "ماژول فعال در پکیج ارتقایافته {$newPackage->name}",
                        'duration_months' => $months,
                        'quantity' => 1,
                        'unit_price' => 0,
                        'discount_amount' => 0,
                        'total_price' => 0,
                        'meta' => [
                            'is_bundled_module' => true,
                            'package_id' => $newPackage->id,
                        ],
                    ]);
                }
            }

            // Also carry forward any previously paid custom standalone modules from old order that are not in the new package
            if (is_array($order->modules_snapshot)) {
                $newPkgModules = is_array($newPackage->modules) ? $newPackage->modules : [];
                foreach ($order->modules_snapshot as $oldItem) {
                    if (($oldItem['type'] ?? '') === 'module' && !empty($oldItem['slug']) && !in_array($oldItem['slug'], $newPkgModules)) {
                        $mSlug = $oldItem['slug'];
                        $modTitle = $oldItem['title'] ?? ResellerHelper::getModuleTitle($mSlug);
                        ResellerInvoiceItem::create([
                            'invoice_id' => $inv->id,
                            'item_type' => 'module',
                            'item_key' => $mSlug,
                            'title' => $modTitle,
                            'description' => "ماژول قبلی خریداری‌شده (انتقال‌یافته)",
                            'duration_months' => $months,
                            'quantity' => 1,
                            'unit_price' => 0,
                            'discount_amount' => 0,
                            'total_price' => 0,
                            'meta' => [
                                'is_carried_forward' => true,
                            ],
                        ]);
                    }
                }
            }

            return $inv;
        });

        if ($itemTotal === 0) {
            $this->upgradeOrderPackageWithoutInvoice($order, $newPackage->id, array_merge($options, [
                'notes' => 'ارتقای بدون هزینه به دلیل محاسبه Proration برابر با صفر'
            ]));
        }

        return $upgradeInvoice;
    }

    /**
     * Upgrade an order package directly without requiring an unpaid invoice payment.
     */
    public function upgradeOrderPackageWithoutInvoice(ResellerOrder $order, int $newPackageId, array $options = []): ResellerOrder
    {
        $resellerId = $order->reseller_id;
        $order->loadMissing(['tenant', 'invoice.items']);

        $newPackage = ResellerPackage::where(function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId)
                ->orWhereNull('reseller_id');
        })->findOrFail($newPackageId);

        $oldPackage = $order->package ?? ($order->tenant?->package_id ? ResellerPackage::find($order->tenant->package_id) : null);

        if ($oldPackage && (int)$oldPackage->id === (int)$newPackage->id) {
            throw new InvalidArgumentException(__('resellerhub::messages.orders.same_package_selected'));
        }

        // Never allow a silent downgrade when applying an upgrade without an invoice.
        if ($oldPackage && !$newPackage->isUpgradeOver($oldPackage)) {
            throw new InvalidArgumentException('ارتقای پکیج فقط به پکیجی با تعرفه بالاتر از پکیج فعلی امکان‌پذیر است.');
        }

        $cycle = $options['billing_cycle'] ?? ($order->billing_cycle ?? 'yearly');
        $months = ResellerOrder::cycleToMonths($cycle);

        return DB::transaction(function () use ($order, $newPackage, $months, $options) {
            if ($order->tenant) {
                $order->tenant->update(['package_id' => $newPackage->id]);
            }

            if (is_array($newPackage->modules)) {
                $currentSnapshot = $order->modules_snapshot ?? [];
                $existingSlugs = collect($currentSnapshot)->pluck('slug')->toArray();
                foreach ($newPackage->modules as $mSlug) {
                    if (!in_array($mSlug, $existingSlugs)) {
                        $currentSnapshot[] = [
                            'type' => 'module',
                            'slug' => $mSlug,
                            'title' => ResellerHelper::getModuleTitle($mSlug),
                            'duration_months' => $months,
                        ];
                    }
                }
                $order->modules_snapshot = $currentSnapshot;
            }

            $order->renewal_price = $newPackage->getPriceForMonths($months);
            if (!empty($options['notes'])) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') . "ارتقا به پکیج «{$newPackage->name}» (بدون صدور فاکتور): " . $options['notes'];
            }
            $order->save();
            $this->orderProvisioningService->provisionOrderToTenant($order);

            return $order;
        });
    }
}
