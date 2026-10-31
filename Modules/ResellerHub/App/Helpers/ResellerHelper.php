<?php

namespace Modules\ResellerHub\App\Helpers;

use App\Models\Module;
use App\Models\Tenant;
use App\Services\Modules\ModuleMenuService;
use App\Tenancy\TenantManager;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;
use Modules\ResellerHub\App\Services\Support\MoneyFormatter;
use Modules\Settings\Entities\Setting;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Enums\PackagePriceType;
use Modules\ResellerHub\App\Enums\PackageDiscountType;
use Modules\ResellerHub\App\Enums\PackageColor;
use Modules\ResellerHub\App\Enums\BrandColor;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Services\Gateway\GatewayCatalog;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;
use Morilog\Jalali\Jalalian;
use Throwable;

class ResellerHelper
{
    /**
     * Resolve the title of a module efficiently using Cache to prevent Disk I/O bottlenecks.
     */
    public static function getModuleTitle(string $slug): string
    {
        return app(ModuleTitleResolver::class)->resolve($slug);
    }

    /**
     * Convert Western and Arabic numerals to standard Persian digits.
     */
    public static function toPersianDigits(mixed $str): string
    {
        return app(MoneyFormatter::class)->toPersianDigits($str);
    }

    /**
     * Convert Persian and Arabic numerals to Western/English digits (0-9).
     */
    public static function toEnglishDigits(mixed $str): string
    {
        return app(MoneyFormatter::class)->toEnglishDigits($str);
    }

    /**
     * Extract a clean numeric value (int or float) from any formatted Persian or English string.
     */
    public static function cleanNumber(mixed $val): int|float
    {
        return app(MoneyFormatter::class)->cleanNumber($val);
    }

    /**
     * Normalize Iranian mobile phone numbers (converts Persian/Arabic digits, strips prefixes like +98, 0098, 989, ensures standard 09xxxxxxxxx).
     */
    public static function normalizeMobile(?string $mobile): string
    {
        if ($mobile === null || $mobile === '') {
            return '';
        }

        $clean = self::toEnglishDigits(trim($mobile));
        $clean = preg_replace('/[^\d+]/', '', $clean);

        if (str_starts_with($clean, '+98')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '0098')) {
            $clean = '0' . substr($clean, 4);
        } elseif (str_starts_with($clean, '989')) {
            $clean = '09' . substr($clean, 3);
        } elseif (str_starts_with($clean, '9') && strlen($clean) === 10) {
            $clean = '0' . $clean;
        }

        return $clean;
    }


    public static function formatPrice(mixed $amount, ?string $currency = null, bool $persianDigits = true): string
    {
        return CurrencyConverter::formatPrice($amount, $currency, $persianDigits);
    }

    /**
     * Normalize and return the Persian currency label.
     * Dynamically falls back to reseller configuration.
     */
    public static function currencyLabel(?string $currency = null): string
    {
        return CurrencyConverter::currencyLabel($currency);
    }

    /**
     * Check if a given currency identifier represents Rial.
     */
    public static function isRial(?string $currency = null): bool
    {
        return CurrencyConverter::isRial($currency);
    }

    /**
     * Calculate conversion factor between two currencies (Rial / Toman).
     */
    public static function conversionFactor(?string $fromCurrency, ?string $toCurrency): float
    {
        return CurrencyConverter::conversionFactor($fromCurrency, $toCurrency);
    }

    /**
     * Calculate invoice tax amount based on taxable amount and tax rate.
     */
    public static function calculateTax(int $taxableAmount, float $taxPercent): int
    {
        return BillingCalculator::calculateTax($taxableAmount, $taxPercent);
    }

    /**
     * Compute full invoice totals breakdown (subtotal, tax, discount, final total).
     *
     * Tax is charged on the net (post-discount) base; see BillingCalculator.
     *
     * @param array<int, int|float> $lineAmounts Per-line net totals, required
     *         for a true per-line calculation when $taxMode is 'item'.
     */
    public static function computeTotals(
        int       $subtotal,
        float     $taxPercent = 0.0,
        string    $discountType = 'fixed',
        float|int $discountValue = 0,
        string    $taxMode = BillingCalculator::TAX_MODE_INVOICE,
        array     $lineAmounts = []
    ): array
    {
        return BillingCalculator::computeTotals(
            $subtotal,
            $taxPercent,
            $discountType,
            $discountValue,
            $taxMode,
            $lineAmounts
        );
    }

    /**
     * Round a price according to reseller rounding settings.
     */
    public static function roundPrice(float|int $amount, ?int $resellerId = null): int
    {
        return app(PriceRoundingService::class)->round($amount, $resellerId);
    }

    /**
     * Apply rounding and return [roundedTotal, roundingMeta].
     */
    public static function applyRounding(float|int $amount, ?int $resellerId = null): array
    {
        return app(PriceRoundingService::class)->apply($amount, $resellerId);
    }

    public static function toJalali(mixed $date): ?Jalalian
    {
        return JalaliDateHelper::toJalali($date);
    }

    /**
     * Format a date into Jalali string with Persian numerals.
     */
    public static function formatJalali(mixed $date, string $format = 'Y/m/d', bool $persianDigits = true): string
    {
        return JalaliDateHelper::formatJalali($date, $format, $persianDigits);
    }

    /**
     * Calculate remaining days between today and the target date (negative if past).
     */
    public static function daysRemaining(mixed $date): ?int
    {
        return JalaliDateHelper::daysRemaining($date);
    }

    /**
     * Return a localized Persian text describing remaining days or expiry status.
     */
    public static function humanRemaining(mixed $date): string
    {
        return JalaliDateHelper::humanRemaining($date);
    }

    /**
     * Parse any Jalali or Gregorian date string/object to a Gregorian Carbon instance.
     */
    public static function parseJalaliToGregorian(mixed $dateStr, ?string $format = null): ?Carbon
    {
        return JalaliDateHelper::parseToGregorian($dateStr, $format);
    }

    /**
     * Get current Jalali year.
     */
    public static function currentJalaliYear(): int
    {
        return JalaliDateHelper::currentYear();
    }

    /**
     * Add months to date respecting Jalali calendar.
     */
    public static function addJalaliMonths(mixed $date, int $months): Carbon
    {
        return JalaliDateHelper::addMonths($date, $months);
    }

    /**
     * Format a date into Jalali string without Persian numbers (or with them if requested).
     */
    public static function formatJalaliDate(mixed $date, string $format = 'Y/m/d', bool $persianDigits = false): ?string
    {
        return JalaliDateHelper::formatDate($date, $format, $persianDigits);
    }

    /**
     * Translate billing cycle or month count to a readable Persian label.
     */
    public static function durationLabel(int|string|null $months, bool $detailed = false): string
    {
        if ($months === null || $months === '') {
            return '-';
        }

        $cycle = BillingCycle::fromPeriodKey($months);
        if ($cycle !== BillingCycle::CUSTOM) {
            return $cycle->label($detailed);
        }

        $cleanMonths = (int)self::cleanNumber($months);
        if ($cleanMonths === 0 && ($months === 0 || $months === '0')) {
            return BillingCycle::LIFETIME->label($detailed);
        }

        return self::toPersianDigits($cleanMonths) . ' ماهه';
    }

    /**
     * Clean and strip protocol, www, port, and trailing paths from domain string.
     */
    public static function cleanDomain(?string $domain): string
    {
        if (!$domain) {
            return '';
        }
        $cleaned = trim($domain);
        $cleaned = preg_replace('#^https?://#i', '', $cleaned);
        $cleaned = preg_replace('#^www\.#i', '', $cleaned);
        $cleaned = explode('/', $cleaned)[0];
        $cleaned = explode(':', $cleaned)[0];
        return strtolower(trim($cleaned));
    }

    /**
     * Sanitize a subdomain string to contain only lowercase alphanumeric characters and hyphens.
     */
    public static function sanitizeSubdomain(?string $subdomain): string
    {
        if (!$subdomain) {
            return '';
        }
        $sub = self::toEnglishDigits(trim($subdomain));
        $sub = strtolower($sub);
        $sub = preg_replace('/[^a-z0-9\-]/', '-', $sub);
        $sub = preg_replace('/-+/', '-', $sub);
        return trim($sub, '-');
    }

    /**
     * Validate whether a subdomain is valid RFC-compliant and not in reserved system keywords.
     */
    public static function isValidSubdomain(?string $subdomain): bool
    {
        if (!$subdomain) {
            return false;
        }
        $sub = strtolower(trim($subdomain));
        if (strlen($sub) < 2 || strlen($sub) > 63) {
            return false;
        }
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?$/', $sub)) {
            return false;
        }
        $reserved = [
            'www', 'admin', 'administrator', 'api', 'mail', 'webmail', 'cpanel',
            'whm', 'directadmin', 'root', 'support', 'billing', 'ns1', 'ns2',
            'dns', 'smtp', 'pop', 'imap', 'ftp', 'ssh', 'sql', 'mysql', 'portal',
            'auth', 'login', 'signup', 'dashboard', 'app', 'cdn', 'static'
        ];
        return !in_array($sub, $reserved, true);
    }

    /**
     * Replace any Gregorian date patterns in a text string with localized Persian Jalali dates.
     */
    public static function formatGregorianInTextToJalali(?string $text): string
    {
        return JalaliDateHelper::formatGregorianInTextToJalali($text);
    }

    /**
     * Standard billing cycles and duration options across all forms and wizards.
     */
    public static function durationOptions(): array
    {
        return BillingCycle::durationOptions();
    }

    /**
     * Standard duration presets for forms, dropdowns, and wizards.
     */
    public static function standardDurationList(): array
    {
        return BillingCycle::standardList();
    }

    /**
     * @return array<int, array{name: string, hex: string}>
     */
    public static function whiteLabelColorPresets(): array
    {
        return BrandColor::presets();
    }

    /**
     * Standard package color presets for cards, badges and pickers.
     */
    public static function packageColorPresets(): array
    {
        return PackageColor::presets();
    }

    /**
     * Color presets for themes, cards and pickers.
     */
    public static function getColorPresets(): array
    {
        return PackageColor::presets();
    }

    /**
     * Standard package colors with full class styling.
     */
    public static function packageColors(): array
    {
        return PackageColor::allConfigs();
    }

    /**
     * Get specific color configuration for package.
     */
    public static function packageColorConfig(?string $colorKey = 'indigo'): array
    {
        return PackageColor::fromOrDefault($colorKey)->config();
    }

    /**
     * Resolve the base/central domain dynamically without hardcoding environment domains.
     */
    public static function getBaseDomain(): string
    {
        if (class_exists(TenantManager::class)) {
            try {
                $centralDomains = app(TenantManager::class)->getCentralDomains();
                if (is_array($centralDomains)) {
                    foreach ($centralDomains as $cd) {
                        $clean = strtolower(trim((string)$cd));
                        if (!empty($clean) && !filter_var($clean, FILTER_VALIDATE_IP) && $clean !== 'localhost') {
                            return $clean;
                        }
                    }
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        if (app()->bound('request') && request()) {
            $host = strtolower(trim((string)request()->getHost()));
            if (!empty($host) && !filter_var($host, FILTER_VALIDATE_IP) && $host !== 'localhost') {
                return $host;
            }
        }

        $appUrl = config('app.url');
        if (!empty($appUrl)) {
            $parsed = parse_url($appUrl, PHP_URL_HOST);
            if (!empty($parsed) && !filter_var($parsed, FILTER_VALIDATE_IP) && $parsed !== 'localhost') {
                return strtolower(trim($parsed));
            }
        }

        return request()?->getHost() ?: parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
    }

    /**
     * Standard human labels for package pricing models.
     */
    public static function priceTypeLabels(): array
    {
        return PackagePriceType::labels();
    }

    public static function priceTypeLabel(?string $type): string
    {
        return PackagePriceType::fromOrDefault($type)->label();
    }

    /**
     * Badges and styles for package pricing models.
     */
    public static function priceTypeBadges(): array
    {
        return PackagePriceType::badges();
    }

    /**
     * Badge configuration for single price type.
     */
    public static function priceTypeBadge(?string $type): array
    {
        return PackagePriceType::fromOrDefault($type)->badge();
    }

    /**
     * Unified Enterprise Package Meta resolver.
     * Single Source of Truth helper for package price types, cycles, colors, and discounts.
     */
    public static function packageMeta(string $aspect, mixed $value = null, mixed ...$args): mixed
    {
        return match (strtolower(trim($aspect))) {
            'price_type', 'price_types' => match (true) {
                $value === null => PackagePriceType::cases(),
                $value === 'labels' => PackagePriceType::labels(),
                $value === 'badges' => PackagePriceType::badges(),
                default => PackagePriceType::fromOrDefault($value),
            },
            'price_type_label' => PackagePriceType::fromOrDefault($value)->label(),
            'price_type_badge' => PackagePriceType::fromOrDefault($value)->badge(),
            'cycle', 'billing_cycle', 'cycles' => match (true) {
                $value === null => BillingCycle::cases(),
                $value === 'options' => BillingCycle::durationOptions(),
                $value === 'standard' || $value === 'list' => BillingCycle::standardList(),
                default => BillingCycle::fromPeriodKey($value),
            },
            'cycle_label', 'duration_label' => self::durationLabel($value, (bool)($args[0] ?? false)),
            'discount_type', 'discount_types' => match (true) {
                $value === null => PackageDiscountType::cases(),
                $value === 'labels' => PackageDiscountType::labels(),
                default => PackageDiscountType::fromOrDefault($value),
            },
            'color', 'colors' => match (true) {
                $value === null => PackageColor::cases(),
                $value === 'presets' => PackageColor::presets(),
                $value === 'configs' || $value === 'all' => PackageColor::allConfigs(),
                default => PackageColor::fromOrDefault($value),
            },
            'color_config' => PackageColor::fromOrDefault($value)->config(),
            'color_presets' => PackageColor::presets(),
            default => null,
        };
    }

    /**
     * Standard color and tone configuration for invoice statuses.
     * Delegated directly to InvoiceStatus Enum as Single Source of Truth.
     */
    public static function invoiceTone(string|InvoiceStatus|null $toneKey): array
    {
        return InvoiceStatus::toneFor($toneKey);
    }

    /**
     * Calculate available renewal periods for a tenant based on its package or order history.
     */
    public static function getTenantRenewalPeriods(Tenant $tenant): array
    {
        $tenantPackage = $tenant->package;
        $availablePeriods = [];

        if ($tenantPackage && $tenantPackage->isSubscription()) {
            $activeMonths = $tenantPackage->getActiveMonths();
            if (empty($activeMonths)) {
                $activeMonths = [1, 3, 6, 12];
            }
            foreach ($activeMonths as $m) {
                $rawPrice = $tenantPackage->getPriceForMonths($m);
                $finalPrice = $tenantPackage->getFinalPriceForMonths($m);
                $hasDiscount = $tenantPackage->hasActiveDiscountForMonths($m) && $finalPrice < $rawPrice;
                $badge = match ($m) {
                    1 => 'پایه',
                    3 => 'فصلی',
                    6 => 'نیم‌سال',
                    12 => 'پیشنهادی',
                    default => ''
                };
                $availablePeriods[] = [
                    'months' => $m,
                    'label' => self::durationLabel($m),
                    'price' => $finalPrice,
                    'raw_price' => $rawPrice,
                    'has_discount' => $hasDiscount,
                    'price_formatted' => self::toPersianDigits(number_format($finalPrice)),
                    'raw_price_formatted' => self::toPersianDigits(number_format($rawPrice)),
                    'discount_expires_at_jalali' => $tenantPackage->getDiscountExpiresAtJalaliForMonths($m),
                    'badge' => $badge,
                    'is_recommended' => ($m === 12),
                ];
            }
        } else {
            $defaultMonths = [1, 3, 6, 12, 24];
            $latestOrder = $tenant->orders ? $tenant->orders->sortByDesc('id')->first() : null;
            $baseOrderPrice = (int)($latestOrder?->renewal_price ?: ($latestOrder?->total_amount ?: 0));
            $orderMonths = 12;

            foreach ($defaultMonths as $m) {
                $calcPrice = $baseOrderPrice > 0 ? (int)round(($baseOrderPrice / $orderMonths) * $m) : 0;
                $availablePeriods[] = [
                    'months' => $m,
                    'label' => self::durationLabel($m),
                    'price' => $calcPrice,
                    'price_formatted' => $calcPrice > 0 ? self::toPersianDigits(number_format($calcPrice)) : 'رایگان',
                    'badge' => ($m === 12 ? 'پیشنهادی' : ''),
                    'is_recommended' => ($m === 12),
                ];
            }
        }

        return $availablePeriods;
    }

    /**
     * Generate sort URL for column toggle.
     */
    public static function sortUrl(string $col, string $defaultCol = 'latest', string $defaultOrder = 'desc'): string
    {
        $currentCol = request('sort_by', $defaultCol);
        $currentOrder = strtolower((string)request('sort_order', $defaultOrder));

        if ($currentCol === $col . '_asc') {
            $currentCol = $col;
            $currentOrder = 'asc';
        } elseif ($currentCol === $col . '_desc') {
            $currentCol = $col;
            $currentOrder = 'desc';
        }

        $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
    }

    /**
     * Generate sort direction icon HTML for column.
     */
    public static function sortIcon(string $col, string $defaultCol = 'latest', string $defaultOrder = 'desc'): string
    {
        $currentCol = request('sort_by', $defaultCol);
        $currentOrder = strtolower((string)request('sort_order', $defaultOrder));

        if ($currentCol === $col . '_asc') {
            $currentCol = $col;
            $currentOrder = 'asc';
        } elseif ($currentCol === $col . '_desc') {
            $currentCol = $col;
            $currentOrder = 'desc';
        }

        if ($currentCol === $col) {
            if ($currentOrder === 'asc') {
                return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>';
            } else {
                return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';
            }
        }

        return '<svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-100 transition-opacity inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
    }

    /**
     * Dynamically resolve human Persian title for any resource quota / limit key.
     * Uses QuotaKey Enum as Single Source of Truth, with module & synonym fallbacks.
     */
    public static function getQuotaTitle(string $key): string
    {
        $normalized = strtolower(trim($key));

        $aliasMap = [
            'client' => 'clients',
            'customer' => 'clients',
            'customers' => 'clients',
            'patient' => 'clients',
            'patients' => 'clients',
            'user' => 'users',
            'users' => 'users',
            'invoice' => 'invoices',
            'product' => 'products',
            'appointment' => 'appointments',
            'provider' => 'booking_providers',
            'providers' => 'booking_providers',
            'warehouse' => 'warehouses',
            'store' => 'stores',
            'order' => 'orders',
        ];

        $target = $aliasMap[$normalized] ?? $normalized;
        $quota = QuotaKey::tryFrom($target);
        if ($quota) {
            return $quota->title();
        }

        if ($target === 'users') {
            return 'سقف کاربران مجاز سامانه';
        }

        // Try module title fallback
        $modTitle = self::getModuleTitle($normalized);
        if (!empty($modTitle) && strtolower($modTitle) !== $normalized) {
            return 'سقف مصرف ' . $modTitle;
        }

        return ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Dynamically resolve unit for any resource quota / limit key.
     */
    public static function getQuotaUnit(string $key): ?string
    {
        $normalized = strtolower(trim($key));
        $aliasMap = [
            'client' => 'clients',
            'customer' => 'clients',
            'customers' => 'clients',
            'patient' => 'clients',
            'patients' => 'clients',
            'invoice' => 'invoices',
            'product' => 'products',
            'appointment' => 'appointments',
            'provider' => 'booking_providers',
            'providers' => 'booking_providers',
            'warehouse' => 'warehouses',
            'store' => 'stores',
            'order' => 'orders',
        ];
        $target = $aliasMap[$normalized] ?? $normalized;
        if ($target === 'users') {
            return 'کاربر';
        }
        return QuotaKey::tryFrom($target)?->unit();
    }

    /**
     * Dynamically resolve title for any feature capability key.
     */
    public static function getFeatureTitle(string $key): string
    {
        $normalized = strtolower(trim($key));
        $quota = QuotaKey::tryFrom($normalized);
        if ($quota) {
            return $quota->title();
        }

        return self::getModuleTitle($key);
    }

    /**
     * Filter an array of feature strings down to ONLY valid commercial capabilities defined in QuotaKey.
     * Strips away any raw Spatie/Laravel permission keys.
     */
    public static function filterCommercialFeatures(array $features): array
    {
        $validKeys = array_map(fn(QuotaKey $c) => $c->value, array_filter(QuotaKey::cases(), fn(QuotaKey $c) => $c->isFeature()));
        return array_values(array_filter($features, fn($f) => in_array($f, $validKeys, true)));
    }

    /**
     * Get all actually active billing periods across real non-free packages.
     * Prevents rendering dummy unused periods in the filter bar.
     */
    public static function getAllAvailablePeriods(): array
    {
        $activeMonths = collect();

        if (class_exists(ResellerPackage::class)) {
            $allPackages = ResellerPackage::all();
            foreach ($allPackages as $p) {
                if ($p->isFree()) {
                    continue;
                }
                foreach ($p->getActiveMonths() as $m) {
                    if ($m >= 1) {
                        $activeMonths->push((int)$m);
                    }
                }
            }
        }

        $uniqueMonths = $activeMonths->unique()->values();
        $sortedMonths = $uniqueMonths->isEmpty() ? [1, 3, 6, 12] : $uniqueMonths->all();
        sort($sortedMonths, SORT_NUMERIC);

        $allOptions = collect(self::durationOptions());
        $availablePeriods = [];

        foreach ($sortedMonths as $m) {
            $key = ResellerPackage::resolvePeriodKeyFromMonth($m);
            $match = $allOptions->firstWhere('months', $m) ?? $allOptions->firstWhere('key', $key);
            $availablePeriods[$key] = [
                'value' => (string)$m,
                'months' => $m,
                'key' => $key,
                'label' => $match ? $match['label'] : self::durationLabel($m, true),
            ];
        }

        return $availablePeriods;
    }

    /**
     * Get all currently active online payment gateways configured in Settings module.
     *
     * @return array<string, array{id: string, name: string, title: string, description: string, icon: string, is_default: bool}>
     */
    public static function getAvailableGateways(): array
    {
        return GatewayCatalog::getAvailableGateways();
    }

    /**
     * Get default active online payment gateway key.
     */
    public static function getDefaultGateway(): string
    {
        return GatewayCatalog::getDefaultGateway();
    }

    /**
     * Check if a specific gateway is active.
     */
    public static function isGatewayActive(string $gateway): bool
    {
        return GatewayCatalog::isGatewayActive($gateway);
    }

    /**
     * Get Persian label for gateway.
     */
    public static function getGatewayName(string $gateway): string
    {
        return GatewayCatalog::getGatewayName($gateway);
    }

    /**
     * Generate a unique tenant subdomain based on reseller pattern (like invoice pattern).
     */
    public static function generateTenantSubdomain(?int $resellerId = null, ?string $mobile = null, ?string $name = null): string
    {
        $pattern = 'crm-{rand}';

        if ($resellerId) {
            $settingPattern = ResellerSetting::get($resellerId, 'subdomain_pattern');
            if (!empty($settingPattern)) {
                $pattern = trim($settingPattern);
            }
        }

        $sub = $pattern;

        // Process {name} tag
        if (stripos($sub, '{name}') !== false) {
            $nameSlug = '';
            if (!empty($name)) {
                $slug = Str::slug($name);
                $nameClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $slug));
                if (!empty($nameClean) && strlen($nameClean) >= 2) {
                    $nameSlug = substr($nameClean, 0, 15);
                }
            }

            if (empty($nameSlug)) {
                // Fallback if Persian name or empty: use random 5-char or mobile
                $nameSlug = 'user' . random_int(100, 999);
            }

            $sub = str_ireplace('{name}', $nameSlug, $sub);
        }

        $sub = preg_replace_callback('/\{rand(?::(\d+))?\}/i', function ($m) {
            $len = isset($m[1]) && (int)$m[1] > 0 ? (int)$m[1] : 6;
            $min = (int)pow(10, $len - 1);
            $max = (int)pow(10, $len) - 1;
            return (string)random_int($min, $max);
        }, $sub);

        $sub = preg_replace_callback('/\{alpha(?::(\d+))?\}/i', function ($m) {
            $len = isset($m[1]) && (int)$m[1] > 0 ? (int)$m[1] : 6;
            return Str::lower(Str::random($len));
        }, $sub);

        if ($mobile) {
            $digits = preg_replace('/\D/', '', $mobile);
            $tail = substr($digits, -5) ?: (string)random_int(10000, 99999);
            $sub = str_ireplace('{mobile_tail}', $tail, $sub);
        } else {
            $sub = str_ireplace('{mobile_tail}', (string)random_int(10000, 99999), $sub);
        }

        $jYear = (string)JalaliDateHelper::currentYear();
        $sub = str_ireplace('{date}', $jYear, $sub);

        // Clean up: only lowercase letters, digits, and hyphens
        $cleaned = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $sub));
        $cleaned = trim(preg_replace('/-+/', '-', $cleaned), '-');

        if (empty($cleaned) || strlen($cleaned) < 3) {
            $cleaned = 'crm-' . random_int(100000, 999999);
        }

        $tenantManager = app(TenantManager::class);
        $reserved = $tenantManager->getReservedSubdomains();

        $candidate = $cleaned;
        $counter = 1;

        while (Tenant::where('subdomain', $candidate)->exists() || in_array($candidate, $reserved, true)) {
            $candidate = $cleaned . '-' . $counter++;
        }

        return $candidate;
    }

    public const ONLINE_SOURCES = [
        'portal_purchase',
        'portal_direct_registration',
        'portal_free_registration',
        'online_purchase',
        'online_registration',
        'online_free_registration',
        'website_purchase',
        'get_started',
        'dashboard_onboarding_selection',
        'subscription_free_selection',
        'tenant_upgrade_request',
        'user_action',
    ];

    /**
     * Check if a source identifier represents an online/website action.
     */
    public static function isOnlineSource(?string $source): bool
    {
        if (empty($source)) {
            return false;
        }

        if (in_array($source, ['manual', 'manual_reseller', 'manual_provision'], true)) {
            return false;
        }

        if (in_array($source, self::ONLINE_SOURCES, true)) {
            return true;
        }

        return str_starts_with($source, 'portal_')
            || str_starts_with($source, 'online_')
            || str_starts_with($source, 'dashboard_')
            || str_starts_with($source, 'website_');
    }
}
