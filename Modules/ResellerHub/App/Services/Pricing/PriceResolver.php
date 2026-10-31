<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Pricing;

use Carbon\Carbon;
use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Support\Money;

/**
 * The single source of truth for every package price in ResellerHub.
 * need a currency wrap the integer result with {@see Money}.
 */
final class PriceResolver
{
    public const LEGACY_PERIODS = [
        1 => 'monthly_price',
        3 => 'quarterly_price',
        6 => 'semi_annual_price',
        12 => 'yearly_price',
    ];

    /**
     * Billing periods the package actually offers, in months.
     *
     * @return array<int, int>
     */
    public function activeMonths(ResellerPackage $package): array
    {
        $months = [];

        foreach ((array)($package->active_periods ?? []) as $period) {
            $resolved = BillingCycle::resolveMonthFromPeriodKey($period);
            if ($resolved >= 1) {
                $months[] = $resolved;
            }
        }

        foreach ((array)($package->period_prices ?? []) as $key => $price) {
            if ((int)$price > 0) {
                $resolved = BillingCycle::resolveMonthFromPeriodKey($key);
                if ($resolved >= 1) {
                    $months[] = $resolved;
                }
            }
        }

        if ($months === []) {
            foreach (self::LEGACY_PERIODS as $anchor => $column) {
                if ((int)$package->getAttribute($column) > 0) {
                    $months[] = $anchor;
                }
            }
        }

        if ($months === [] && $package->isSubscription()) {
            $months = [1, 3, 6, 12];
        }

        $months = array_values(array_unique(array_filter(
            array_map('intval', $months),
            static fn(int $month): bool => $month >= 1,
        )));

        sort($months, SORT_NUMERIC);

        return $months;
    }

    /**
     * @return array<int, string>
     */
    public function activePeriods(ResellerPackage $package): array
    {
        return array_values(array_unique(array_map(
            static fn(int $months): string => self::periodKey($months),
            $this->activeMonths($package),
        )));
    }

    public function isPeriodActive(ResellerPackage $package, string|int $period): bool
    {
        if (!$package->isSubscription()) {
            return false;
        }

        $months = BillingCycle::resolveMonthFromPeriodKey($period);

        return $months >= 1 && in_array($months, $this->activeMonths($package), true);
    }

    public static function periodKey(int $months): string
    {
        return BillingCycle::resolvePeriodKeyFromMonth($months);
    }

    /**
     * Raw (pre-discount) price for a number of months.
     */
    public function rawPrice(ResellerPackage $package, int $months): int
    {
        if ($package->isFree()) {
            return 0;
        }

        if ($package->isOneTime()) {
            return (int)$package->one_time_price;
        }

        $periodPrices = (array)($package->period_prices ?? []);

        foreach ([$months, (string)$months, self::periodKey($months)] as $key) {
            if (isset($periodPrices[$key]) && (int)$periodPrices[$key] > 0) {
                return (int)$periodPrices[$key];
            }
        }

        $monthly = (int)$package->monthly_price;

        return match (true) {
            $months === 1 => $monthly,
            $months === 3 => (int)($package->quarterly_price > 0 ? $package->quarterly_price : $monthly * 3),
            $months === 6 => (int)($package->semi_annual_price > 0 ? $package->semi_annual_price : $monthly * 6),
            $months === 12 => (int)($package->yearly_price > 0 ? $package->yearly_price : $monthly * 12),
            $months > 12 => (int)round((($package->yearly_price > 0 ? $package->yearly_price : $monthly * 12) / 12) * $months),
            default => (int)($monthly * $months),
        };
    }

    /**
     * The per-period discount configuration, if one was defined for it.
     *
     * @return array<string, mixed>|null
     */
    public function discountConfig(ResellerPackage $package, int $months): ?array
    {
        $discounts = (array)($package->period_discounts ?? []);

        foreach ([(string)$months, self::periodKey($months)] as $key) {
            if (isset($discounts[$key]) && is_array($discounts[$key])) {
                return $discounts[$key];
            }
        }

        return null;
    }

    public function hasCustomDiscount(ResellerPackage $package, int $months): bool
    {
        $config = $this->discountConfig($package, $months);

        if ($config === null) {
            return false;
        }

        $hasCustom = !empty($config['has_custom'])
            && in_array((string)$config['has_custom'], ['1', 'true', 'on'], true);
        $hasValue = (float)($config['discount_value'] ?? 0) > 0;

        return ($hasCustom || (!isset($config['has_custom']) && $hasValue)) && $hasValue;
    }

    public function hasActiveDiscount(ResellerPackage $package, int $months): bool
    {
        if ($this->hasCustomDiscount($package, $months)) {
            $config = $this->discountConfig($package, $months) ?? [];

            if ((float)($config['discount_value'] ?? 0) <= 0) {
                return false;
            }

            $starts = $config['discount_starts_at'] ?? $config['starts_at'] ?? null;
            if (!empty($starts) && Carbon::parse($starts)->isFuture()) {
                return false;
            }

            $expires = $config['discount_expires_at'] ?? $config['expires_at'] ?? null;

            return !(!empty($expires) && Carbon::parse($expires)->isPast());
        }

        return $this->hasGlobalActiveDiscount($package);
    }

    /**
     * The package-level discount window, used as the fallback for every period.
     */
    public function hasGlobalActiveDiscount(ResellerPackage $package): bool
    {
        if ((float)$package->discount_value <= 0) {
            return false;
        }

        if ($package->discount_starts_at instanceof Carbon && $package->discount_starts_at->isFuture()) {
            return false;
        }

        if ($package->discount_expires_at instanceof Carbon && $package->discount_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Discount amount for one billing period. Never exceeds the raw price.
     */
    public function discountAmount(ResellerPackage $package, int $months, ?int $rawPrice = null): int
    {
        $rawPrice ??= $this->rawPrice($package, $months);

        if ($rawPrice <= 0 || $package->isFree()) {
            return 0;
        }

        if ($this->hasCustomDiscount($package, $months)) {
            if (!$this->hasActiveDiscount($package, $months)) {
                return 0;
            }

            $config = $this->discountConfig($package, $months) ?? [];

            return (int)min($rawPrice, max(0.0, (float)($config['discount_value'] ?? 0)));
        }

        if (!$this->hasGlobalActiveDiscount($package)) {
            return 0;
        }

        return $this->clampDiscount($rawPrice, (float)$package->discount_value, (string)($package->discount_type ?? ''));
    }

    /**
     * Final, payable price for a number of months.
     */
    public function finalPrice(ResellerPackage $package, int $months): int
    {
        $raw = $this->rawPrice($package, $months);

        return max(0, $raw - $this->discountAmount($package, $months, $raw));
    }


    public function discountedPrice(ResellerPackage $package, int $rawPrice): int
    {
        if ($rawPrice <= 0 || $package->isFree()) {
            return 0;
        }

        if (!$this->hasGlobalActiveDiscount($package)) {
            return $rawPrice;
        }

        return max(0, $rawPrice - $this->clampDiscount(
                $rawPrice,
                (float)$package->discount_value,
                (string)($package->discount_type ?? ''),
            ));
    }

    /**
     * Structured period catalogue used by checkout and the package page.
     *
     * @return array<string, array{key: string, months: int, label: string, raw_price: int, final_price: int, discount_amount: int, has_discount: bool, discount_percent: int, monthly_rate: int}>
     */
    public function periods(ResellerPackage $package): array
    {
        if (!$package->isSubscription()) {
            return [];
        }

        $options = collect(BillingCycle::durationOptions());
        $periods = [];

        foreach ($this->activeMonths($package) as $months) {
            $key = self::periodKey($months);
            $rawPrice = $this->rawPrice($package, $months);
            $finalPrice = $this->finalPrice($package, $months);
            $discountAmount = $this->discountAmount($package, $months, $rawPrice);
            $hasDiscount = $this->hasActiveDiscount($package, $months)
                && $finalPrice < $rawPrice
                && $discountAmount > 0;

            $option = $options->firstWhere('months', $months) ?? $options->firstWhere('key', $key);

            $periods[$key] = [
                'key' => $key,
                'months' => $months,
                'label' => $option ? $option['label'] : ResellerHelper::durationLabel($months, true),
                'raw_price' => $rawPrice,
                'final_price' => $finalPrice,
                'discount_amount' => $discountAmount,
                'has_discount' => $hasDiscount,
                'discount_percent' => $hasDiscount && $rawPrice > 0
                    ? (int)round(($discountAmount / $rawPrice) * 100)
                    : 0,
                'monthly_rate' => $months > 0 ? (int)round($finalPrice / $months) : $finalPrice,
            ];
        }

        return $periods;
    }


    public function isUpgradeOver(ResellerPackage $package, ?ResellerPackage $current): bool
    {
        if (!$current) {
            return !$package->isFree();
        }

        if ($package === $current || ($package->getKey() && (int)$package->getKey() === (int)$current->getKey())) {
            return false;
        }

        if ($package->isFree()) {
            return false;
        }

        $strictlyHigher = false;

        foreach (array_keys(self::LEGACY_PERIODS) as $months) {
            $newPrice = $this->finalPrice($package, $months);
            $currentPrice = $this->finalPrice($current, $months);

            if ($newPrice < $currentPrice) {
                return false;
            }

            if ($newPrice > $currentPrice) {
                $strictlyHigher = true;
            }
        }

        return $strictlyHigher;
    }

    /**
     * Wrap a package amount in a Money value object.
     */
    public function money(ResellerPackage $package, int $amount, ?string $currency = null): Money
    {
        return Money::of($amount, $currency ?? \Modules\ResellerHub\App\Support\CurrencyContext::forReseller(
            $package->reseller_id ? (int)$package->reseller_id : null,
        ));
    }

    private function clampDiscount(int $rawPrice, float $value, string $type): int
    {
        if ($value <= 0) {
            return 0;
        }

        if ($type === ResellerPackage::DISCOUNT_TYPE_AMOUNT) {
            return (int)min($rawPrice, $value);
        }

        $percent = min(100.0, $value);

        return (int)round($rawPrice * ($percent / 100));
    }
}
