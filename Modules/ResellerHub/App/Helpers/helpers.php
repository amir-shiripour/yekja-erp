<?php

use App\Models\Tenant;
use Carbon\Carbon;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Morilog\Jalali\Jalalian;

if (!function_exists('reseller_fa_num')) {
    /**
     * Convert English and Arabic numbers to Persian numbers.
     */
    function reseller_fa_num(mixed $str): string
    {
        return ResellerHelper::toPersianDigits($str);
    }
}

if (!function_exists('reseller_en_num')) {
    /**
     * Convert Persian and Arabic numbers to English numbers.
     */
    function reseller_en_num(mixed $str): string
    {
        return ResellerHelper::toEnglishDigits($str);
    }
}

if (!function_exists('reseller_price')) {
    /**
     * Dynamically uses reseller's configured currency if not specified.
     */
    function reseller_price(mixed $amount, ?string $currency = null, bool $persianDigits = true): string
    {
        return ResellerHelper::formatPrice($amount, $currency, $persianDigits);
    }
}

if (!function_exists('reseller_currency_label')) {
    /**
     * Normalize and return Persian currency label.
     * Dynamically uses reseller's configured currency if not specified.
     */
    function reseller_currency_label(?string $currency = null): string
    {
        return ResellerHelper::currencyLabel($currency);
    }
}

if (!function_exists('reseller_is_rial')) {
    /**
     * Check if a given currency represents Rial.
     */
    function reseller_is_rial(?string $currency = null): bool
    {
        return ResellerHelper::isRial($currency);
    }
}

if (!function_exists('reseller_currency_conversion_factor')) {
    /**
     * Calculate conversion factor between two currencies.
     */
    function reseller_currency_conversion_factor(?string $fromCurrency, ?string $toCurrency): float
    {
        return ResellerHelper::conversionFactor($fromCurrency, $toCurrency);
    }
}

if (!function_exists('reseller_to_jalali')) {

    function reseller_to_jalali(mixed $date): ?Jalalian
    {
        return ResellerHelper::toJalali($date);
    }
}

if (!function_exists('reseller_format_jalali')) {
    /**
     * Format any date into Jalali string with Persian numbers.
     */
    function reseller_format_jalali(mixed $date, string $format = 'Y/m/d', bool $persianDigits = true): string
    {
        return ResellerHelper::formatJalali($date, $format, $persianDigits);
    }
}

if (!function_exists('reseller_to_carbon')) {
    /**
     * Parse any Jalali date string or date object to Gregorian Carbon.
     */
    function reseller_to_carbon(mixed $dateStr, ?string $format = null): ?Carbon
    {
        return ResellerHelper::parseJalaliToGregorian($dateStr, $format);
    }
}

if (!function_exists('reseller_jalali_year')) {
    /**
     * Get current Jalali solar year.
     */
    function reseller_jalali_year(): int
    {
        return ResellerHelper::currentJalaliYear();
    }
}

if (!function_exists('reseller_add_jalali_months')) {
    /**
     * Add months to date respecting Jalali solar calendar.
     */
    function reseller_add_jalali_months(mixed $date, int $months): Carbon
    {
        return ResellerHelper::addJalaliMonths($date, $months);
    }
}

if (!function_exists('reseller_duration_label')) {
    /**
     * Get the human Persian label for a billing cycle / month duration.
     */
    function reseller_duration_label(int|string|null $months, bool $detailed = false): string
    {
        return ResellerHelper::durationLabel($months, $detailed);
    }
}

if (!function_exists('reseller_human_remaining')) {
    /**
     * Get a human-readable days remaining badge text in Persian.
     */
    function reseller_human_remaining(mixed $date): string
    {
        return ResellerHelper::humanRemaining($date);
    }
}

if (!function_exists('reseller_days_remaining')) {
    /**
     * Calculate remaining days between today and the target date.
     */
    function reseller_days_remaining(mixed $date): ?int
    {
        return ResellerHelper::daysRemaining($date);
    }
}

if (!function_exists('reseller_clean_domain')) {
    /**
     * Sanitize and strip protocol/port/path from domain input.
     */
    function reseller_clean_domain(?string $domain): string
    {
        return ResellerHelper::cleanDomain($domain);
    }
}

if (!function_exists('reseller_format_text_dates')) {
    /**
     * Replace any Gregorian date patterns in a text string with Persian Jalali dates.
     */
    function reseller_format_text_dates(?string $text): string
    {
        return ResellerHelper::formatGregorianInTextToJalali($text);
    }
}

if (!function_exists('reseller_invoice_tone')) {
    /**
     * Get visual tone CSS classes and localized label for an invoice state from InvoiceStatus Enum.
     */
    function reseller_invoice_tone(string|InvoiceStatus|null $toneKey): array
    {
        $status = $toneKey instanceof InvoiceStatus ? $toneKey : InvoiceStatus::fromOrDefault((string)$toneKey);
        $tone = ResellerHelper::invoiceTone($toneKey);
        $tone['label'] = $status?->label() ?? ($toneKey ? (string)$toneKey : '-');
        $tone['status_label'] = $tone['label'];
        $tone['badge_classes'] = $status?->badgeClasses() ?? ($tone['badge'] ?? '');
        return $tone;
    }
}

if (!function_exists('reseller_payment_method_label')) {
    /**
     * Decode and format raw payment method/gateway strings to human-readable Persian names.
     */
    function reseller_payment_method_label(?string $method, ?string $gateway = null): string
    {
        return ResellerInvoicePayment::formatMethodName($method, $gateway);
    }
}

if (!function_exists('reseller_tenant_renewal_periods')) {
    /**
     * Get available renewal period choices for a tenant.
     */
    function reseller_tenant_renewal_periods(Tenant $tenant): array
    {
        return ResellerHelper::getTenantRenewalPeriods($tenant);
    }
}

if (!function_exists('reseller_can_tenant_renew')) {
    /**
     * Check if a tenant's license can be renewed.
     */
    function reseller_can_tenant_renew(Tenant $tenant): bool
    {
        return ResellerHelper::canTenantRenew($tenant);
    }
}

if (!function_exists('reseller_package_color_presets')) {
    /**
     * Standard color presets for reseller packages.
     */
    function reseller_package_color_presets(): array
    {
        return ResellerHelper::packageColorPresets();
    }
}

if (!function_exists('reseller_standard_duration_list')) {
    /**
     * Standard duration presets for forms and dropdowns.
     */
    function reseller_standard_duration_list(): array
    {
        return ResellerHelper::standardDurationList();
    }
}

if (!function_exists('reseller_sort_url')) {
    /**
     * Generate sort toggle URL for table column.
     */
    function reseller_sort_url(string $col, string $defaultCol = 'latest', string $defaultOrder = 'desc'): string
    {
        return ResellerHelper::sortUrl($col, $defaultCol, $defaultOrder);
    }
}

if (!function_exists('reseller_sort_icon')) {
    /**
     * Generate sort direction icon HTML for table column.
     */
    function reseller_sort_icon(string $col, string $defaultCol = 'latest', string $defaultOrder = 'desc'): string
    {
        return ResellerHelper::sortIcon($col, $defaultCol, $defaultOrder);
    }
}

if (!function_exists('reseller_package_meta')) {
    function reseller_package_meta(string $aspect, mixed $value = null, mixed ...$args): mixed
    {
        return ResellerHelper::packageMeta($aspect, $value, ...$args);
    }
}

if (!function_exists('reseller_normalize_mobile')) {
    /**
     * Normalize Iranian mobile phone number to standard format.
     */
    function reseller_normalize_mobile(?string $mobile): string
    {
        return ResellerHelper::normalizeMobile($mobile);
    }
}

if (!function_exists('reseller_base_domain')) {
    /**
     * Resolve the base/central domain dynamically.
     */
    function reseller_base_domain(): string
    {
        return ResellerHelper::getBaseDomain();
    }
}

if (!function_exists('reseller_calculate_tax')) {
    /**
     * Calculate invoice tax amount based on taxable amount and tax rate.
     */
    function reseller_calculate_tax(int $taxableAmount, float $taxPercent): int
    {
        return ResellerHelper::calculateTax($taxableAmount, $taxPercent);
    }
}

if (!function_exists('reseller_compute_totals')) {
    /**
     * Compute full invoice totals breakdown.
     *
     * Tax is charged on the net base.
     *
     * @param array<int, int|float> $lineAmounts Per-line net totals for 'item' mode.
     */
    function reseller_compute_totals(
        int       $subtotal,
        float     $taxPercent = 0.0,
        string    $discountType = 'fixed',
        float|int $discountValue = 0,
        string    $taxMode = 'invoice',
        array     $lineAmounts = []
    ): array
    {
        return ResellerHelper::computeTotals(
            $subtotal,
            $taxPercent,
            $discountType,
            $discountValue,
            $taxMode,
            $lineAmounts
        );
    }
}
