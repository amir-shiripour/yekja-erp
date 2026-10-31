<?php

namespace Modules\ResellerHub\App\Enums;

enum AccountStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::SUSPENDED => 'معلق',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200/70 dark:border-emerald-800/70',
            self::SUSPENDED => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200/70 dark:border-rose-800/70',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'emerald',
            self::SUSPENDED => 'rose',
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
