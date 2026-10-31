<?php

namespace Modules\ResellerHub\App\Enums;

enum OrderProvisionStatus: string
{
    case NOT_PROVISIONED = 'not_provisioned';
    case PROVISIONED = 'provisioned';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NOT_PROVISIONED => 'راه‌اندازی نشده',
            self::PROVISIONED => 'راه‌اندازی شده',
            self::FAILED => 'خطا در راه‌اندازی',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PROVISIONED => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
            self::NOT_PROVISIONED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
            self::FAILED => 'bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
