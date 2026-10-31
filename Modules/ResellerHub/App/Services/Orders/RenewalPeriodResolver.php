<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Orders;

use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Throwable;

final class RenewalPeriodResolver
{
    /**
     * @return array{cycle: string, months: int, days: int|null, is_custom: bool, label: string}
     */
    public function resolve(ResellerOrder $order): array
    {
        $cycle = (string)($order->billing_cycle ?? 'yearly');

        if (preg_match('/^(?:period_)?(\d+)(?:_price)?$/', $cycle, $match)) {
            $months = (int)$match[1];

            if ($months > 0) {
                return [
                    'cycle' => $cycle,
                    'months' => $months,
                    'days' => null,
                    'is_custom' => false,
                    'label' => "دوره {$months} ماهه",
                ];
            }
        }

        $billingCycle = BillingCycle::fromPeriodKey($cycle);
        $isCustom = !$order->package || $cycle === 'custom' || $billingCycle === BillingCycle::CUSTOM;

        if (!$isCustom) {
            return [
                'cycle' => $cycle === 'annual' ? 'yearly' : $cycle,
                'months' => $billingCycle->defaultMonths(),
                'days' => null,
                'is_custom' => false,
                'label' => 'دوره ' . $billingCycle->label(),
            ];
        }

        $days = $this->lastRenewalDays($order);

        if (!$days) {
            $days = $order->start_date && $order->end_date && $order->end_date->greaterThan($order->start_date)
                ? (int)$order->start_date->diffInDays($order->end_date)
                : 360;
        }

        $days = max(1, $days);

        return [
            'cycle' => 'custom',
            'months' => max(1, (int)round($days / 30)),
            'days' => $days,
            'is_custom' => true,
            'label' => 'سفارشی — ' . $days . ' روز',
        ];
    }

    /**
     * The duration recorded on the most recent renewal invoice, when present.
     */
    private function lastRenewalDays(ResellerOrder $order): ?int
    {
        if (!$order->exists) {
            return null;
        }

        try {
            $invoices = ResellerInvoice::query()
                ->where(static function ($query) use ($order): void {
                    $query->where('meta->source_order_id', (int)$order->id)
                        ->orWhere('meta->source_order_id', (string)$order->id);
                })
                ->orderByDesc('id')
                ->get();

            foreach ($invoices as $invoice) {
                $meta = $invoice->meta;

                if (is_array($meta)
                    && !empty($meta['is_renewal'])
                    && (int)($meta['duration_days'] ?? 0) > 0) {
                    return (int)$meta['duration_days'];
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Convert a billing cycle identifier to months.
     */
    public static function cycleToMonths(string|int|null $cycle): int
    {
        return BillingCycle::resolveMonthFromPeriodKey($cycle);
    }
}
