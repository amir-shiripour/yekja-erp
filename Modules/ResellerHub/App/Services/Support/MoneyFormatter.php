<?php

namespace Modules\ResellerHub\App\Services\Support;

use Modules\ResellerHub\App\Helpers\CurrencyConverter;

class MoneyFormatter
{
    /**
     * Convert Western and Arabic numerals to standard Persian digits.
     */
    public function toPersianDigits(mixed $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $str = str_replace($western, $persian, (string)$str);
        return str_replace($arabic, $persian, $str);
    }

    /**
     * Convert Persian and Arabic numerals to Western/English digits (0-9).
     */
    public function toEnglishDigits(mixed $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $str = str_replace($persian, $english, (string)$str);
        return str_replace($arabic, $english, $str);
    }

    /**
     * Extract a clean integer/float from any formatted Persian or English string.
     */
    public function cleanNumber(mixed $val): int|float
    {
        if ($val instanceof \Modules\ResellerHub\App\Support\Money) {
            return $val->amount;
        }

        if ($val === null || $val === '') {
            return 0;
        }
        $clean = $this->toEnglishDigits((string)$val);
        $clean = preg_replace('/[^\d\.\-]/', '', $clean);
        if ($clean === '' || $clean === '-') {
            return 0;
        }
        return str_contains($clean, '.') ? (float)$clean : (int)$clean;
    }

    /**
     * Format a price with currency symbol and optional Persian digits.
     */
    public function formatPrice(mixed $amount, ?string $currency = null, bool $persianDigits = true): string
    {
        return CurrencyConverter::formatPrice($amount, $currency, $persianDigits);
    }


    public function getCurrencyLabel(?string $currency = null): string
    {
        return CurrencyConverter::currencyLabel($currency);
    }
}
