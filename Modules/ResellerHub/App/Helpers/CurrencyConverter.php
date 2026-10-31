<?php

namespace Modules\ResellerHub\App\Helpers;

use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Support\Money;

final class CurrencyConverter
{
    /**
     * Format numerical amount to currency string with proper separators and symbol.
     */
    public static function formatPrice(mixed $amount, ?string $currency = null, bool $persianDigits = true): string
    {
        if ($amount instanceof Money) {
            $currency = $currency ?? $amount->currency;
            $amount = $amount->amount;
        }

        $num = ResellerHelper::cleanNumber($amount);
        $formatted = number_format($num);
        if ($persianDigits) {
            $formatted = ResellerHelper::toPersianDigits($formatted);
        }
        $currLabel = self::currencyLabel($currency);
        return $formatted . ($currLabel ? (' ' . $currLabel) : '');
    }

    /**
     * Normalize and return the Persian currency label.
     * Dynamically falls back to reseller configuration.
     */
    public static function currencyLabel(?string $currency = null): string
    {
        if (!$currency) {
            return ResellerSetting::getCurrencyLabelForReseller();
        }

        $c = mb_strtolower(trim($currency));

        return in_array($c, ['rial', 'irr', 'ریال'], true) ? 'ریال' : 'تومان';
    }

    /**
     * Check if a given currency identifier represents Rial.
     */
    public static function isRial(?string $currency = null): bool
    {
        if (!$currency) {
            return false;
        }

        $c = mb_strtolower(trim($currency));

        return in_array($c, ['rial', 'irr', 'ریال'], true);
    }

    /**
     * Calculate conversion factor between two currencies.
     */
    public static function conversionFactor(?string $fromCurrency, ?string $toCurrency): float
    {
        $fromRial = self::isRial($fromCurrency);
        $toRial = self::isRial($toCurrency);

        if ($fromRial && !$toRial) {
            return 0.1;
        }

        if (!$fromRial && $toRial) {
            return 10.0;
        }

        return 1.0;
    }
}
