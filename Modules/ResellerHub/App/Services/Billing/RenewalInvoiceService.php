<?php

namespace Modules\ResellerHub\App\Services\Billing;

use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Events\InvoiceCreated;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerModulePricing;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Morilog\Jalali\Jalalian;
use Throwable;

class RenewalInvoiceService
{
    /**
     * Create a renewal invoice from an existing order.
     */
    public function createRenewalInvoiceFromOrder(ResellerOrder $order, array $options = []): ResellerInvoice
    {
        $resellerId = $order->reseller_id;
        $order->loadMissing(['tenant', 'invoice.items', 'invoice.package']);

        $period = $order->renewalPeriod();
        $cycle = $period['cycle'];
        $months = $period['months'];
        $days = $period['days'];
        $isCustomPeriod = $period['is_custom'];
        $cycleLabel = $period['label'];

        if (!empty($options['months']) && (int)$options['months'] > 0) {
            $months = (int)$options['months'];
            $isCustomPeriod = false;
            $days = null;
            $cycle = match ($months) {
                1 => 'monthly',
                3 => 'quarterly',
                6 => 'semi_annual',
                12 => 'yearly',
                default => 'custom',
            };
            $cycleLabel = ResellerHelper::durationLabel($months, true);
        }

        if ($order->tenant) {
            $this->assertCanRenewAdvance($order->tenant, $months, $resellerId);
        }

        $orderCycle = $order->billing_cycle ?? 'yearly';
        $orderMonths = ResellerOrder::cycleToMonths($orderCycle);

        $baseRenewalPrice = 0;
        if ((float)$order->renewal_price > 0) {
            $baseRenewalPrice = (int)$order->renewal_price;
        } elseif ((float)$order->total_amount > 0) {
            $baseRenewalPrice = (int)$order->total_amount;
        }

        $pkg = $order->tenant?->package ?? $order->invoice?->package;
        if (!empty($options['price']) && (int)$options['price'] >= 0) {
            $cycleRenewalPrice = (int)$options['price'];
        } elseif ($pkg && method_exists($pkg, 'getPriceForMonths') && (int)$pkg->getPriceForMonths($months) > 0) {
            $cycleRenewalPrice = (int)$pkg->getPriceForMonths($months);
        } else {
            $cycleRenewalPrice = ($isCustomPeriod || $cycle === $orderCycle)
                ? $baseRenewalPrice
                : (int)round(($baseRenewalPrice / max(1, $orderMonths)) * $months);
        }

        $currentRenewalDate = $order->renewal_date
            ? Carbon::parse($order->renewal_date)
            : ($order->end_date ? Carbon::parse($order->end_date) : now());

        if ($isCustomPeriod && $days) {
            $nextRenewalDate = (clone $currentRenewalDate)->addDays((int)$days);
        } else {
            $nextRenewalDate = reseller_add_jalali_months($currentRenewalDate, $months);
        }

        $dueDateOffset = isset($options['due_date_offset']) ? (int)$options['due_date_offset'] : null;
        $dueDate = ($dueDateOffset !== null && $dueDateOffset > 0)
            ? now()->addDays($dueDateOffset)
            : $nextRenewalDate;

        $taxPercent = (float)($order->invoice?->tax_percent ?? 0);
        $taxMode = (string)($order->invoice?->meta['tax_mode'] ?? BillingCalculator::TAX_MODE_INVOICE);
        $itemSubtotal = $cycleRenewalPrice;
        $itemTax = BillingCalculator::calculateTax($itemSubtotal, $taxPercent);
        $itemTotal = $itemSubtotal + $itemTax;

        $invoiceNumber = ResellerInvoice::generateNextNumber($resellerId, ResellerInvoice::TYPE_OFFICIAL);

        $renewalOrderNumber = ResellerHelper::toPersianDigits($order->order_number);
        $renewalNotes = $options['notes'] ?? __('resellerhub::messages.invoices.renewal_from_order_note', [
            'order' => $renewalOrderNumber,
            'period' => $cycleLabel,
        ]);

        $renewalInvoice = DB::transaction(function () use (
            $resellerId, $order, $invoiceNumber, $dueDate, $nextRenewalDate,
            $taxPercent, $taxMode, $itemSubtotal, $itemTax, $itemTotal,
            $cycle, $cycleLabel, $cycleRenewalPrice, $months, $days,
            $isCustomPeriod, $orderMonths, $options, $renewalNotes
        ) {
            $inv = ResellerInvoice::create([
                'reseller_id' => $resellerId,
                'tenant_id' => $order->tenant_id,
                'parent_invoice_id' => $order->invoice_id,
                'source_order_id' => $order->id,
                'source' => 'renewal',
                'package_id' => $order->tenant?->package_id ?? $order->invoice?->package_id,
                'invoice_number' => $invoiceNumber,
                'type' => $options['type'] ?? ResellerInvoice::TYPE_OFFICIAL,
                'status' => ResellerInvoice::STATUS_UNPAID,
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
                'notes' => $renewalNotes,
                'meta' => [
                    'is_renewal' => true,
                    'tax_mode' => $taxMode,
                    'created_by_manual_renewal' => !empty($options['manual']),
                    'created_by_workflow' => !empty($options['created_by_workflow']) || empty($options['manual']),
                    'source_invoice_id' => $order->invoice_id,
                    'source_order_id' => $order->id,
                    'billing_cycle' => $cycle,
                    'duration_months' => $months,
                    'duration_days' => $days,
                    'is_custom_period' => $isCustomPeriod,
                    'period_label' => $cycleLabel,
                ],
                'created_by' => auth()->id() ?? $order->created_by,
            ]);

            if ($order->invoice && $order->invoice->items->isNotEmpty()) {
                foreach ($order->invoice->items as $item) {
                    if ($item->item_type === 'setup_fee') {
                        continue;
                    }

                    $qty = max(1, (int)$item->quantity);
                    $unitPrice = (int)$item->unit_price;

                    if ($isCustomPeriod) {
                        $unitPrice = (int)$item->unit_price;
                    } elseif (($order->renewal_price_type ?? 'auto') !== 'manual') {
                        $pricing = null;
                        if ($item->item_type === 'module' && !empty($item->item_key)) {
                            $pricing = ResellerModulePricing::where('reseller_id', $resellerId)
                                ->where('module_slug', $item->item_key)
                                ->whereNull('feature_key')
                                ->first();
                        } elseif ($item->item_type === 'feature' && !empty($item->item_key)) {
                            $pricing = ResellerModulePricing::where('reseller_id', $resellerId)
                                ->where('feature_key', $item->item_key)
                                ->first();
                        }
                        if ($pricing) {
                            $p = match ($cycle) {
                                'monthly' => $pricing->monthly_price,
                                'quarterly' => $pricing->quarterly_price ?: ($pricing->monthly_price * 3),
                                'semi_annual' => $pricing->semi_annual_price ?: ($pricing->monthly_price * 6),
                                default => $pricing->yearly_price ?: ($pricing->monthly_price * 12),
                            };
                            if ($p > 0) {
                                $unitPrice = (int)$p;
                            }
                        } else {
                            $sourceMonths = (int)($item->duration_months ?: $orderMonths);
                            if ($sourceMonths > 0 && $sourceMonths !== $months) {
                                $unitPrice = (int)round(($item->unit_price / $sourceMonths) * $months);
                            }
                        }
                    } else {
                        $sourceMonths = (int)($item->duration_months ?: $orderMonths);
                        if ($sourceMonths > 0 && $sourceMonths !== $months) {
                            $unitPrice = (int)round(($item->unit_price / $sourceMonths) * $months);
                        }
                    }

                    $itemTitle = __('resellerhub::messages.invoices.renewal_item_title', ['title' => $item->title]);
                    $itemDesc = __('resellerhub::messages.invoices.renewal_item_desc', [
                        'title' => $item->title,
                        'period' => $cycleLabel,
                    ]);

                    ResellerInvoiceItem::create([
                        'invoice_id' => $inv->id,
                        'item_type' => $item->item_type,
                        'item_key' => $item->item_key,
                        'title' => $itemTitle,
                        'description' => $itemDesc,
                        'duration_months' => $months,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'discount_amount' => 0,
                        'total_price' => $qty * $unitPrice,
                        'meta' => [
                            'is_renewal_item' => true,
                            'source_item_id' => $item->id,
                        ],
                    ]);
                }
                $inv->load('items');
                $inv->recalculateTotals()->save();
            } else {
                $pkgTitle = __('resellerhub::messages.invoices.renewal_order_package_title', [
                    'order' => $order->tenant?->name ?? $order->order_number,
                ]);
                $pkgDesc = __('resellerhub::messages.invoices.renewal_order_package_desc', [
                    'period' => $cycleLabel,
                ]);

                ResellerInvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'item_type' => 'module',
                    'item_key' => 'renewal',
                    'title' => $pkgTitle,
                    'description' => $pkgDesc,
                    'duration_months' => $months,
                    'quantity' => 1,
                    'unit_price' => $cycleRenewalPrice,
                    'discount_amount' => 0,
                    'total_price' => $cycleRenewalPrice,
                    'meta' => [
                        'is_renewal_item' => true,
                        'billing_cycle' => $cycle,
                    ],
                ]);
            }

            $order->update([
                'renewal_date' => $nextRenewalDate,
                'renewal_price' => $inv->total,
            ]);

            return $inv;
        });

        event(new InvoiceCreated($renewalInvoice));

        return $renewalInvoice;
    }

    /**
     * Validate whether a tenant is allowed to renew in advance according to reseller limits.
     */
    public function validateAdvanceRenewalLimit(
        Tenant $tenant,
        int    $renewalMonths = 12,
        ?int   $resellerId = null,
        ?int   $excludeInvoiceId = null
    ): array
    {
        $resellerId = $resellerId ?: $tenant->reseller_id;
        if (!$resellerId) {
            return [
                'allowed' => true,
                'max_cycles' => 0,
                'current_expires_at' => null,
                'projected_expires_at' => null,
                'max_allowed_date' => null,
                'message' => null,
            ];
        }

        $maxCyclesSetting = ResellerSetting::get($resellerId, 'max_advance_renewal_cycles', 1);
        $maxCycles = is_null($maxCyclesSetting) ? 1 : (int)$maxCyclesSetting;

        // 0 or negative means unlimited advance renewal cycles
        if ($maxCycles <= 0) {
            return [
                'allowed' => true,
                'max_cycles' => 0,
                'current_expires_at' => null,
                'projected_expires_at' => null,
                'max_allowed_date' => null,
                'message' => null,
            ];
        }

        $currentExpiresAt = $tenant->plan_expires_at ? Carbon::parse($tenant->plan_expires_at) : null;
        if (!$currentExpiresAt || $currentExpiresAt->isPast()) {
            return [
                'allowed' => true,
                'max_cycles' => $maxCycles,
                'current_expires_at' => $currentExpiresAt,
                'projected_expires_at' => null,
                'max_allowed_date' => null,
                'message' => null,
            ];
        }

        // Determine base cycle duration in months
        $cycleMonths = $renewalMonths > 0 ? $renewalMonths : 0;
        if ($cycleMonths <= 0) {
            $latestOrder = $tenant->orders()->latest('id')->first();
            $orderCycle = $latestOrder?->billing_cycle;
            $cycleMonths = $orderCycle ? ResellerOrder::cycleToMonths($orderCycle) : 0;
        }
        if ($cycleMonths <= 0 && $tenant->package && (int)$tenant->package->duration_months > 0) {
            $cycleMonths = (int)$tenant->package->duration_months;
        }
        if ($cycleMonths <= 0) {
            $cycleMonths = 12;
        }

        // Calculate pending (unpaid) renewal months for this tenant
        $pendingRenewalQuery = ResellerInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', [
                ResellerInvoice::STATUS_UNPAID,
                ResellerInvoice::STATUS_OVERDUE,
                ResellerInvoice::STATUS_DRAFT,
                'pending',
                'unpaid',
            ])
            ->where(function ($q) {
                $q->where('meta->is_renewal', true)
                    ->orWhereNotNull('parent_invoice_id');
            });

        if ($excludeInvoiceId) {
            $pendingRenewalQuery->where('id', '!=', $excludeInvoiceId);
        }

        $pendingMonths = 0;
        foreach ($pendingRenewalQuery->get() as $inv) {
            $invMonths = (int)($inv->meta['duration_months'] ?? 0);
            if ($invMonths <= 0 && $inv->items) {
                $invMonths = (int)$inv->items->max('duration_months');
            }
            $pendingMonths += ($invMonths > 0 ? $invMonths : $cycleMonths);
        }

        // Projected expiration date if this renewal is applied
        $totalMonthsToAdd = $pendingMonths + max(1, $renewalMonths);
        $projectedExpiry = (clone $currentExpiresAt)->addMonths($totalMonthsToAdd);

        // Maximum allowed future date: up to remaining current cycle + $maxCycles full future cycles
        $maxAllowedDate = Carbon::now()->addMonths(($maxCycles + 1) * $cycleMonths);

        // Calculate advance cycles ahead
        $monthsAhead = Carbon::now()->diffInMonths($projectedExpiry, false);
        $cyclesAhead = (int)ceil($monthsAhead / max(1, $cycleMonths));
        $advanceCycles = max(0, $cyclesAhead - 1);

        if ($advanceCycles > $maxCycles || $projectedExpiry->greaterThan($maxAllowedDate)) {
            $jalaliExpiresAtFa = reseller_format_jalali($currentExpiresAt);
            $maxCyclesFa = ResellerHelper::toPersianDigits((string)$maxCycles);

            $message = "امکان تمدید مجدد وجود ندارد؛ سقف تمدید آتی برای این سامانه تکمیل شده است (حداکثر {$maxCyclesFa} دوره آتی). اشتراک این سامانه در حال حاضر تا تاریخ {$jalaliExpiresAtFa} فعال و معتبر است.";

            return [
                'allowed' => false,
                'max_cycles' => $maxCycles,
                'current_expires_at' => $currentExpiresAt,
                'projected_expires_at' => $projectedExpiry,
                'max_allowed_date' => $maxAllowedDate,
                'message' => $message,
            ];
        }

        return [
            'allowed' => true,
            'max_cycles' => $maxCycles,
            'current_expires_at' => $currentExpiresAt,
            'projected_expires_at' => $projectedExpiry,
            'max_allowed_date' => $maxAllowedDate,
            'message' => null,
        ];
    }

    /**
     * Assert that advance renewal is allowed, or throw an exception.
     *
     * @throws Exception
     */
    public function assertCanRenewAdvance(
        Tenant $tenant,
        int    $renewalMonths = 12,
        ?int   $resellerId = null,
        ?int   $excludeInvoiceId = null
    ): void
    {
        $validation = $this->validateAdvanceRenewalLimit($tenant, $renewalMonths, $resellerId, $excludeInvoiceId);
        if (!$validation['allowed']) {
            throw new ResellerHubException($validation['message']);
        }
    }
}
