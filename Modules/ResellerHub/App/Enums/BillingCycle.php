<?php

namespace Modules\ResellerHub\App\Enums;

use Modules\ResellerHub\App\Helpers\ResellerHelper;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMI_ANNUAL = 'semi_annual';
    case ANNUAL = 'annual';
    case BIENNIAL = 'biennial';
    case TRIENNIAL = 'triennial';
    case LIFETIME = 'lifetime';
    case CUSTOM = 'custom';

    /**
     * Standard human Persian label for this cycle.
     */
    public function label(bool $detailed = false): string
    {
        if ($detailed) {
            return match ($this) {
                self::MONTHLY => '۱ ماهه (ماهانه)',
                self::QUARTERLY => '۳ ماهه (فصلی)',
                self::SEMI_ANNUAL => '۶ ماهه (نیم‌سال)',
                self::ANNUAL => '۱۲ ماهه (سالانه)',
                self::BIENNIAL => '۲۴ ماهه (۲ ساله)',
                self::TRIENNIAL => '۳۶ ماهه (۳ ساله)',
                self::LIFETIME => 'مادام‌العمر (یک‌باره)',
                self::CUSTOM => 'دوره دلخواه',
            };
        }

        return match ($this) {
            self::MONTHLY => '۱ ماهه',
            self::QUARTERLY => '۳ ماهه',
            self::SEMI_ANNUAL => '۶ ماهه',
            self::ANNUAL => '۱ ساله',
            self::BIENNIAL => '۲ ساله',
            self::TRIENNIAL => '۳ ساله',
            self::LIFETIME => 'مادام‌العمر',
            self::CUSTOM => 'دوره دلخواه',
        };
    }

    /**
     * Duration in months.
     */
    public function defaultMonths(): int
    {
        return match ($this) {
            self::MONTHLY => 1,
            self::QUARTERLY => 3,
            self::SEMI_ANNUAL => 6,
            self::ANNUAL => 12,
            self::BIENNIAL => 24,
            self::TRIENNIAL => 36,
            self::LIFETIME => 0,
            self::CUSTOM => 1,
        };
    }

    /**
     * ResellerPackage period column key .
     */
    public function periodKey(): string
    {
        return match ($this) {
            self::MONTHLY => 'monthly_price',
            self::QUARTERLY => 'quarterly_price',
            self::SEMI_ANNUAL => 'semi_annual_price',
            self::ANNUAL => 'yearly_price',
            self::BIENNIAL => 'biennial_price',
            self::TRIENNIAL => 'triennial_price',
            self::LIFETIME => 'one_time_price',
            self::CUSTOM => 'custom_price',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MONTHLY => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            self::QUARTERLY => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            self::SEMI_ANNUAL => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
            self::ANNUAL => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::BIENNIAL, self::TRIENNIAL => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
            self::LIFETIME => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
            self::CUSTOM => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    /**
     * Resolve enum instance from month number or string.
     */
    public static function fromMonths(int|string|null $months): self
    {
        if ($months === null || $months === '') {
            return self::ANNUAL;
        }

        if (is_string($months) && !is_numeric($months)) {
            return self::fromPeriodKey($months);
        }

        $clean = (int)ResellerHelper::cleanNumber($months);

        return match ($clean) {
            0 => self::LIFETIME,
            1 => self::MONTHLY,
            3 => self::QUARTERLY,
            6 => self::SEMI_ANNUAL,
            12 => self::ANNUAL,
            24 => self::BIENNIAL,
            36 => self::TRIENNIAL,
            default => self::CUSTOM,
        };
    }

    /**
     * Resolve enum instance from period key or cycle slug.
     */
    public static function fromPeriodKey(string|int|null $key): self
    {
        if ($key === null || $key === '') {
            return self::ANNUAL;
        }

        if (is_numeric($key)) {
            return self::fromMonths((int)$key);
        }

        $k = strtolower(trim((string)$key));

        return match ($k) {
            'monthly', 'monthly_price', 'period_1_price', '1', 'month_1' => self::MONTHLY,
            'quarterly', 'quarterly_price', 'period_3_price', '3', 'month_3' => self::QUARTERLY,
            'semi_annual', 'semi_annual_price', 'period_6_price', '6', 'month_6' => self::SEMI_ANNUAL,
            'annual', 'yearly', 'yearly_price', 'period_12_price', '12', 'month_12' => self::ANNUAL,
            'biennial', 'biennial_price', 'period_24_price', '24', 'month_24' => self::BIENNIAL,
            'triennial', 'triennial_price', 'period_36_price', '36', 'month_36' => self::TRIENNIAL,
            'lifetime', 'one_time', 'one_time_price', '0' => self::LIFETIME,
            default => self::CUSTOM,
        };
    }

    /**
     * Resolve month count from any key or string.
     */
    public static function resolveMonthFromPeriodKey(string|int|null $key): int
    {
        if (is_numeric($key)) {
            $m = (int)$key;
            return ($m >= 0 && $m <= 1200) ? $m : 12;
        }

        $cycle = self::fromPeriodKey($key);
        if ($cycle !== self::CUSTOM) {
            return $cycle->defaultMonths();
        }

        return (int)preg_replace('/\D/', '', (string)$key) ?: 12;
    }

    /**
     * Resolve column key from month count.
     */
    public static function resolvePeriodKeyFromMonth(int $months): string
    {
        return match ($months) {
            0 => 'one_time_price',
            1 => 'monthly_price',
            3 => 'quarterly_price',
            6 => 'semi_annual_price',
            12 => 'yearly_price',
            24 => 'biennial_price',
            36 => 'triennial_price',
            default => "period_{$months}_price",
        };
    }

    /**
     * Standard duration presets for forms, dropdowns, and wizards.
     */
    public static function standardList(): array
    {
        return [
            ['value' => '1', 'months' => 1, 'label' => self::MONTHLY->label()],
            ['value' => '3', 'months' => 3, 'label' => self::QUARTERLY->label()],
            ['value' => '6', 'months' => 6, 'label' => self::SEMI_ANNUAL->label()],
            ['value' => '12', 'months' => 12, 'label' => self::ANNUAL->label() . ' (۱۲ ماهه)'],
            ['value' => '24', 'months' => 24, 'label' => self::BIENNIAL->label() . ' (۲۴ ماهه)'],
            ['value' => '36', 'months' => 36, 'label' => self::TRIENNIAL->label() . ' (۳۶ ماهه)'],
        ];
    }

    /**
     * Full duration options list across all forms and wizards.
     */
    public static function durationOptions(): array
    {
        return [
            [
                'value' => '1',
                'months' => 1,
                'key' => 'monthly_price',
                'label' => '۱ ماهه (ماهانه)',
                'badge' => 'پایه',
                'badgeClass' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            ],
            [
                'value' => '2',
                'months' => 2,
                'key' => 'period_2_price',
                'label' => '۲ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '3',
                'months' => 3,
                'key' => 'quarterly_price',
                'label' => '۳ ماهه (فصلی)',
                'badge' => 'فصلی',
                'badgeClass' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
            ],
            [
                'value' => '4',
                'months' => 4,
                'key' => 'period_4_price',
                'label' => '۴ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '5',
                'months' => 5,
                'key' => 'period_5_price',
                'label' => '۵ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '6',
                'months' => 6,
                'key' => 'semi_annual_price',
                'label' => '۶ ماهه (نیم‌سال)',
                'badge' => '۵٪ تخفیف',
                'badgeClass' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            ],
            [
                'value' => '7',
                'months' => 7,
                'key' => 'period_7_price',
                'label' => '۷ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '8',
                'months' => 8,
                'key' => 'period_8_price',
                'label' => '۸ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '9',
                'months' => 9,
                'key' => 'period_9_price',
                'label' => '۹ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '10',
                'months' => 10,
                'key' => 'period_10_price',
                'label' => '۱۰ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '11',
                'months' => 11,
                'key' => 'period_11_price',
                'label' => '۱۱ ماهه',
                'badge' => '',
                'badgeClass' => '',
            ],
            [
                'value' => '12',
                'months' => 12,
                'key' => 'yearly_price',
                'label' => '۱۲ ماهه (سالانه)',
                'badge' => 'پیشنهادی',
                'badgeClass' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50',
            ],
            [
                'value' => '18',
                'months' => 18,
                'key' => 'period_18_price',
                'label' => '۱۸ ماهه (۱.۵ ساله)',
                'badge' => '۱.۵ ساله',
                'badgeClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
            ],
            [
                'value' => '24',
                'months' => 24,
                'key' => 'period_24_price',
                'label' => '۲۴ ماهه (۲ ساله)',
                'badge' => '۲ ساله',
                'badgeClass' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            ],
            [
                'value' => '36',
                'months' => 36,
                'key' => 'period_36_price',
                'label' => '۳۶ ماهه (۳ ساله)',
                'badge' => '۳ ساله',
                'badgeClass' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 border-teal-200 dark:border-teal-800/50',
            ],
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
