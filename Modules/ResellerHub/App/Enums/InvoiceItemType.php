<?php

namespace Modules\ResellerHub\App\Enums;

enum InvoiceItemType: string
{
    case PACKAGE = 'package';
    case PACKAGE_UPGRADE = 'package_upgrade';
    case MODULE = 'module';
    case SETUP_FEE = 'setup_fee';
    case CUSTOM_DOMAIN = 'custom_domain';
    case WHITE_LABEL = 'white_label';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::PACKAGE => 'پکیج اصلی',
            self::PACKAGE_UPGRADE => 'ارتقای پکیج',
            self::MODULE => 'ماژول پکیج',
            self::SETUP_FEE => 'راه‌اندازی',
            self::CUSTOM_DOMAIN => 'دامنه اختصاصی',
            self::WHITE_LABEL => 'وایت‌لیبل',
            self::CUSTOM => 'سفارشی',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PACKAGE => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            self::PACKAGE_UPGRADE => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            self::MODULE => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
            self::SETUP_FEE => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            self::CUSTOM_DOMAIN, self::WHITE_LABEL => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            self::CUSTOM => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    public function iconBadgeClasses(): string
    {
        return match ($this) {
            self::MODULE => 'bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400',
            self::PACKAGE => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400',
            self::PACKAGE_UPGRADE => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400',
            self::SETUP_FEE => 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
            self::CUSTOM_DOMAIN, self::WHITE_LABEL => 'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400',
            self::CUSTOM => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    public function rowBgClass(): string
    {
        return match ($this) {
            self::SETUP_FEE => 'bg-amber-50/20 dark:bg-amber-950/10',
            self::WHITE_LABEL => 'bg-purple-50/20 dark:bg-purple-950/10',
            default => '',
        };
    }

    public static function fromOrDefault(?string $type): ?self
    {
        if ($type === null || $type === '') {
            return null;
        }

        $t = strtolower(trim($type));
        return self::tryFrom($t);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
