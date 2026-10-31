<?php

namespace Modules\ResellerHub\App\Enums;

enum OnboardingStepStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار انجام',
            self::COMPLETED => 'تکمیل شده',
            self::SKIPPED => 'رد شده',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200/70 dark:border-emerald-800/70',
            self::SKIPPED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
            self::PENDING => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200/70 dark:border-amber-800/70',
        };
    }

    public static function fromOrDefault(?string $status): ?self
    {
        if ($status === null || $status === '') {
            return null;
        }

        $s = strtolower(trim($status));
        return self::tryFrom($s);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
