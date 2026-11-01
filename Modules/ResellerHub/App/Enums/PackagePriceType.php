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
            self::ONE_TIME => 'مادام‌العمر',
            self::FREE => 'رایگان',
        };
    }

    /**
     * Primary theme color name.
     */
    public function color(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'indigo',
            self::ONE_TIME => 'purple',
            self::FREE => 'emerald',
        };
    }

    /**
     * Tailwind CSS classes for badges.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/60',
            self::ONE_TIME => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/70 dark:border-purple-800/60',
            self::FREE => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60',
        };
    }

    /**
     * Text color CSS classes for typography.
     */
    public function textClass(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'text-indigo-600 dark:text-indigo-400',
            self::ONE_TIME => 'text-purple-600 dark:text-purple-400',
            self::FREE => 'text-emerald-600 dark:text-emerald-400',
        };
    }

    /**
     * Tailwind CSS classes for license validity badge in tables.
     */
    public function licenseBadgeClass(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/60',
            self::ONE_TIME => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/70 dark:border-purple-800/60',
            self::FREE => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60',
        };
    }

    /**
     * Persian label displayed for license validity in tables and detail cards.
     */
    public function licenseBadgeLabel(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'اشتراک دوره‌ای',
            self::ONE_TIME => 'مادام‌العمر (دائمی)',
            self::FREE => 'رایگان (همیشگی)',
        };
    }

    /**
     * Persian subtitle text displayed under license validity badge.
     */
    public function licenseSubtitle(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'نیازمند تمدید دوره‌ای',
            self::ONE_TIME => 'دسترسی همیشگی - بدون انقضا',
            self::FREE => 'دسترسی رایگان - بدون نیاز به تمدید',
        };
    }

    /**
     * Whether this pricing model has recurring renewals.
     */
    public function hasRenewal(): bool
    {
        return $this === self::SUBSCRIPTION;
    }

    /**
     * Whether this pricing model supports duration/billing periods.
     */
    public function hasPeriods(): bool
    {
        return $this === self::SUBSCRIPTION;
    }

    /**
     * Whether this pricing model requires monetary payment.
     */
    public function requiresPayment(): bool
    {
        return $this !== self::FREE;
    }

    /**
     * Array structure with label and class for backward-compatibility.
     */
    public function badge(): array
    {
        return [
            'label' => $this->shortLabel(),
            'full_label' => $this->label(),
            'class' => $this->badgeClass(),
            'color' => $this->color(),
            'text_class' => $this->textClass(),
            'has_renewal' => $this->hasRenewal(),
        ];
    }

    /**
     * Full license metadata array for views.
     */
    public function licenseMeta(): array
    {
        return [
            'badge_label' => $this->licenseBadgeLabel(),
            'badge_class' => $this->licenseBadgeClass(),
            'subtitle' => $this->licenseSubtitle(),
            'text_class' => $this->textClass(),
            'has_renewal' => $this->hasRenewal(),
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
