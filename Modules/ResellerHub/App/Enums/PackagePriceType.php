<?php

namespace Modules\ResellerHub\App\Enums;

enum PackagePriceType: string
{
    case SUBSCRIPTION = 'subscription';
    case ONE_TIME = 'one_time';
    case FREE = 'free';

    /**
     * Standard human Persian label for this pricing model.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'اشتراکی (دوره‌ای)',
            self::ONE_TIME => 'یک‌باره (مادام‌العمر)',
            self::FREE => 'رایگان',
        };
    }

    /**
     * Short Persian label for badges and compact displays.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'دوره‌ای',
            self::ONE_TIME => 'یک‌باره',
            self::FREE => 'رایگان',
        };
    }

    /**
     * Tailwind CSS classes for badges.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60',
            self::ONE_TIME => 'bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/60',
            self::FREE => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60',
        };
    }

    /**
     * Array structure with label and class for backward-compatibility.
     */
    public function badge(): array
    {
        return [
            'label' => $this->shortLabel(),
            'class' => $this->badgeClass(),
        ];
    }

    public function isSubscription(): bool
    {
        return $this === self::SUBSCRIPTION;
    }

    public function isOneTime(): bool
    {
        return $this === self::ONE_TIME;
    }

    public function isFree(): bool
    {
        return $this === self::FREE;
    }

    /**
     * Map of all price type values to their full labels.
     */
    public static function labels(): array
    {
        $res = [];
        foreach (self::cases() as $case) {
            $res[$case->value] = $case->label();
        }
        return $res;
    }

    /**
     * Map of all price type values to their badge configurations.
     */
    public static function badges(): array
    {
        $res = [];
        foreach (self::cases() as $case) {
            $res[$case->value] = $case->badge();
        }
        return $res;
    }

    /**
     * Safely resolve an instance with default fallback to SUBSCRIPTION.
     */
    public static function fromOrDefault(?string $value): self
    {
        if (empty($value)) {
            return self::SUBSCRIPTION;
        }

        return self::tryFrom(strtolower(trim($value))) ?? self::SUBSCRIPTION;
    }
}
