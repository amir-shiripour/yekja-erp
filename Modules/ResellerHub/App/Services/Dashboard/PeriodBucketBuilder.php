<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Dashboard;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

/**
 * Builds the time buckets the reseller dashboard charts are grouped by.
 *
 * Extracted from {@see \Modules\ResellerHub\App\Services\DashboardMetricsService}
 * so the Jalali month arithmetic can be unit-tested without a database.
 *
 * Every bucket is `['key' => 'YYYY-MM', 'label' => '…', 'start' => Carbon, 'end' => Carbon]`
 * where `key` uses the Jalali year/month and `start`/`end` are Gregorian boundaries.
 */
final class PeriodBucketBuilder
{
    /**
     * Rolling Jalali months, oldest first, ending with the current month.
     *
     * @return array<int, array{key: string, label: string, start: Carbon, end: Carbon}>
     */
    public function rollingJalaliMonths(Jalalian $current, int $monthCount): array
    {
        $monthCount = max(1, $monthCount);
        $buckets = [];

        for ($i = $monthCount - 1; $i >= 0; $i--) {
            // morilog/jalali (>= 3.5) asserts on subMonths(0); offset 0 simply
            // means "the current month", so it must not be shifted at all.
            $target = $i === 0 ? $current : $current->subMonths($i);

            $buckets[] = $this->bucketFor($target, $monthCount <= 6);
        }

        return $buckets;
    }

    /**
     * Current Jalali financial year: 1 Farvardin through the current month.
     *
     * @return array<int, array{key: string, label: string, start: Carbon, end: Carbon}>
     */
    public function jalaliYearToDate(Jalalian $current): array
    {
        $year = $current->getYear();
        $month = $current->getMonth();
        $buckets = [];

        for ($m = 1; $m <= $month; $m++) {
            $start = new Jalalian($year, $m, 1);

            $buckets[] = [
                'key' => sprintf('%04d-%02d', $year, $m),
                'label' => $start->format('%B'),
                'start' => $start->toCarbon()->startOfDay(),
                'end' => (new Jalalian($year, $m, $start->getMonthDays(), 23, 59, 59))
                    ->toCarbon()
                    ->endOfDay(),
            ];
        }

        return $buckets;
    }

    /**
     * @return array{key: string, label: string, start: Carbon, end: Carbon}
     */
    private function bucketFor(Jalalian $month, bool $shortLabel): array
    {
        $year = $month->getYear();
        $monthNumber = $month->getMonth();
        $start = new Jalalian($year, $monthNumber, 1);
        $end = new Jalalian($year, $monthNumber, $start->getMonthDays(), 23, 59, 59);

        return [
            'key' => sprintf('%04d-%02d', $year, $monthNumber),
            'label' => $start->format($shortLabel ? '%B' : '%B %y'),
            'start' => $start->toCarbon()->startOfDay(),
            'end' => $end->toCarbon()->endOfDay(),
        ];
    }
}
