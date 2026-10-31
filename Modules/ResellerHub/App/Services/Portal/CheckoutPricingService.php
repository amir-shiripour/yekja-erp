<?php

namespace Modules\ResellerHub\App\Services\Portal;

use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;

class CheckoutPricingService
{
    /**
     * Calculate white-label fee breakdown (recurring, setup, total) for given reseller and months.
     *
     * @return array{recurring: int, setup: int, total: int}
     */
    public function getWhiteLabelFee(?Reseller $reseller, string $whiteLabelType, int $months): array
    {
        if ($whiteLabelType !== 'custom' || !$reseller) {
            return ['recurring' => 0, 'setup' => 0, 'total' => 0];
        }

        $wlYearly = (int)ResellerSetting::get($reseller->id, 'white_label_yearly_price', 0);
        $wlMonthly = (int)ResellerSetting::get($reseller->id, 'white_label_monthly_price', 0);
        $wlSetup = (int)ResellerSetting::get($reseller->id, 'white_label_setup_fee', 0);

        $wlPrice = 0;
        if ($months === 1 && $wlMonthly > 0) {
            $wlPrice = $wlMonthly;
        } elseif ($months === 12 && $wlYearly > 0) {
            $wlPrice = $wlYearly;
        } elseif ($wlYearly > 0) {
            $wlPrice = (int)round(($wlYearly / 12) * $months);
        } elseif ($wlMonthly > 0) {
            $wlPrice = $wlMonthly * $months;
        }

        return [
            'recurring' => $wlPrice,
            'setup' => $wlSetup,
            'total' => $wlPrice + $wlSetup,
        ];
    }

    /**
     * Calculate total package price including one-time setup and white-label fees.
     */
    public function getTotalPrice(
        ResellerPackage $package,
        int             $months,
        ?Reseller       $reseller = null,
        string          $whiteLabelType = 'none'
    ): int
    {
        $wlFee = $this->getWhiteLabelFee($reseller, $whiteLabelType, $months);
        $setupFee = $package->getFinalSetupFeeAttribute();

        // A free package is only free when no paid add-ons are selected.
        if ($package->isFree()) {
            return $setupFee + $wlFee['total'];
        }

        $pkgPrice = $package->getFinalPriceForMonths($months);
        $rawTotal = $pkgPrice + $setupFee + $wlFee['total'];

        if ($rawTotal > 0 && $reseller) {
            return app(PriceRoundingService::class)->round($rawTotal, $reseller->id);
        }

        return $rawTotal;
    }
}
