<?php

namespace Modules\ResellerHub\App\Services\Billing;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;

final readonly class SubscriptionProrationCalculator
{
    /**
     * Calculate prorated upgrade difference and remaining days for a subscription order.
     */
    public function calculate(
        ResellerOrder $order,
        ResellerPackage $newPackage,
        int $cycleMonths
    ): ProrationResult {
        $oldPackage = $order->package ?? ($order->tenant?->package_id ? ResellerPackage::find($order->tenant->package_id) : null);
        $rawNewPrice = $newPackage->getPriceForMonths($cycleMonths);
        $newPrice = $newPackage->getFinalPriceForMonths($cycleMonths);
        $oldPrice = $oldPackage ? $oldPackage->getPriceForMonths($cycleMonths) : (int)$order->total_amount;

        if ($newPrice < $oldPrice) {
            if ($rawNewPrice >= $oldPrice) {
                $fullDifference = 0;
            } else {
                throw new InvalidArgumentException('امکان ارتقا به پکیج با تعرفه کمتر وجود ندارد (تنها ارتقا به پکیج‌های بالاتر مجاز است).');
            }
        } else {
            $fullDifference = max(0, $newPrice - $oldPrice);
        }

        $now = Carbon::now()->startOfDay();
        $endDate = $order->end_date
            ? Carbon::parse($order->end_date)->startOfDay()
            : ($order->renewal_date ? Carbon::parse($order->renewal_date)->startOfDay() : null);
        $startDate = $order->start_date
            ? Carbon::parse($order->start_date)->startOfDay()
            : ($endDate ? (clone $endDate)->subMonths($cycleMonths) : $now);

        if (!$endDate || $endDate->isPast()) {
            return new ProrationResult(
                fullDifference: $fullDifference,
                proratedAmount: $fullDifference,
                remainingDays: 0,
                totalDays: max(1, $cycleMonths * 30)
            );
        }

        $totalDays = ($startDate && $endDate && $endDate->greaterThan($startDate))
            ? max(1, $startDate->diffInDays($endDate))
            : ($cycleMonths * 30);
        $remainingDays = max(0, $now->diffInDays($endDate));

        $proratedAmount = ($totalDays > 0 && $remainingDays > 0 && $remainingDays < $totalDays)
            ? (int) round($fullDifference * ($remainingDays / $totalDays))
            : $fullDifference;

        return new ProrationResult(
            fullDifference: $fullDifference,
            proratedAmount: $proratedAmount,
            remainingDays: $remainingDays,
            totalDays: $totalDays
        );
    }
}
