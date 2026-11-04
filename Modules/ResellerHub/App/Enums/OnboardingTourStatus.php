<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Enums;

enum OnboardingTourStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case POSTPONED = 'postponed';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار شروع',
            self::IN_PROGRESS => 'در حال انجام',
            self::POSTPONED => 'به‌تعویق‌افتاده (گام‌های اختیاری)',
            self::COMPLETED => 'تکمیل‌شده و خاتمه‌یافته',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200/70 dark:border-emerald-800/70',
            self::POSTPONED => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200/70 dark:border-amber-800/70',
            self::IN_PROGRESS => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border-indigo-200/70 dark:border-indigo-800/70',
            self::PENDING => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
        };
    }

    public static function fromOrDefault(?string $status): self
    {
        if ($status === null || $status === '') {
            return self::PENDING;
        }

        return self::tryFrom(strtolower(trim($status))) ?? self::PENDING;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
