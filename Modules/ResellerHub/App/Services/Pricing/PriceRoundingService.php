<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Pricing;

use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Models\ResellerSetting;

class PriceRoundingService
{
    /**
     * Apply rounding to a given amount based on reseller settings or explicit parameters.
     *
     * @param float|int $amount The raw / unrounded amount
     * @param int|null $resellerId Reseller ID to pull settings for
     * @param string|null $mode Override mode ('none', 'nearest', 'up', 'down')
     * @param int|null $factor Override factor (e.g. 1000, 10000)
     * @return array{0: int, 1: array{mode: string, factor: int, original_total: int, diff: int, is_rounded: bool}}
     */
    public function apply(
        float|int $amount,
        ?int $resellerId = null,
        ?string $mode = null,
        ?int $factor = null
    ): array {
        if ($mode === null || $factor === null) {
            $settings = $this->getRoundingSettings($resellerId);
            $mode = $mode ?? $settings['mode'];
            $factor = $factor ?? $settings['factor'];
        }

        $unrounded = (int) round((float) $amount);
        $finalTotal = $unrounded;
        $diff = 0;

        if ($factor > 0) {
            if ($mode === 'up') {
                $finalTotal = (int) (ceil($amount / $factor) * $factor);
                $diff = $finalTotal - $unrounded;
            } elseif ($mode === 'down') {
                $finalTotal = (int) (floor($amount / $factor) * $factor);
                $diff = $finalTotal - $unrounded;
            } elseif ($mode === 'nearest') {
                $finalTotal = (int) (round($amount / $factor) * $factor);
                $diff = $finalTotal - $unrounded;
            }
        }

        $meta = [
            'mode' => $mode,
            'factor' => $factor,
            'original_total' => $unrounded,
            'diff' => $diff,
            'is_rounded' => ($mode !== 'none' && $factor > 0 && $diff !== 0),
        ];

        return [$finalTotal, $meta];
    }

    /**
     * Get rounded integer total directly.
     */
    public function round(float|int $amount, ?int $resellerId = null): int
    {
        [$rounded] = $this->apply($amount, $resellerId);
        return $rounded;
    }

    /**
     * Retrieve current rounding settings for a reseller.
     *
     * @param int|null $resellerId
     * @return array{mode: string, factor: int}
     */
    public function getRoundingSettings(?int $resellerId = null): array
    {
        if (!$resellerId && auth()->check()) {
            $resellerId = auth()->user()?->reseller?->id ?? null;
        }

        if (!$resellerId) {
            return [
                'mode' => 'none',
                'factor' => 1000,
            ];
        }

        $mode = (string) ResellerSetting::get($resellerId, ResellerSettingKey::ResellerRoundingMode, 'none');
        $currency = ResellerSetting::getCurrencyForReseller($resellerId);
        $defaultFactor = ($currency === 'rial') ? 10000 : 1000;
        $factor = (int) ResellerSetting::get($resellerId, ResellerSettingKey::ResellerRoundingFactor, $defaultFactor);

        if ($factor <= 0) {
            $factor = $defaultFactor;
        }

        return [
            'mode' => in_array($mode, ['none', 'nearest', 'up', 'down'], true) ? $mode : 'none',
            'factor' => $factor,
        ];
    }
}
