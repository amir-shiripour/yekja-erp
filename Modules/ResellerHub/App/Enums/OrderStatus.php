<?php

namespace Modules\ResellerHub\App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('resellerhub::messages.orders.status_pending'),
            self::ACTIVE, self::COMPLETED => __('resellerhub::messages.orders.status_active'),
            self::INACTIVE, self::SUSPENDED => __('resellerhub::messages.orders.status_inactive'),
            self::CANCELLED => __('resellerhub::messages.orders.status_cancelled'),
        };
    }

    /**
     * Allowed status transitions from the current status.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::ACTIVE, self::CANCELLED],
            self::ACTIVE, self::COMPLETED => [self::INACTIVE, self::SUSPENDED, self::CANCELLED],
            self::INACTIVE, self::SUSPENDED => [self::ACTIVE, self::CANCELLED],
            self::CANCELLED => [],
        };
    }

    /**
     * Check whether transition to target status is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return in_array($target, $this->allowedTransitions(), true);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border-amber-200 dark:border-amber-500/30',
            self::ACTIVE, self::COMPLETED => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
            self::INACTIVE, self::SUSPENDED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
            self::CANCELLED => 'bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::ACTIVE, self::COMPLETED => 'emerald',
            self::INACTIVE, self::SUSPENDED => 'gray',
            self::CANCELLED => 'rose',
        };
    }

    public function hexColor(): string
    {
        return match ($this) {
            self::PENDING => '#f59e0b',
            self::ACTIVE, self::COMPLETED => '#10b981',
            self::INACTIVE, self::SUSPENDED => '#6b7280',
            self::CANCELLED => '#ef4444',
        };
    }

    /**
     * Vertical status bar color class (e.g. for card edges).
     */
    public function barColor(): string
    {
        return match ($this) {
            self::ACTIVE, self::COMPLETED => 'bg-emerald-500',
            self::PENDING => 'bg-amber-500',
            self::CANCELLED => 'bg-rose-500',
            self::INACTIVE, self::SUSPENDED => 'bg-gray-400',
        };
    }

    /**
     * Icon container styling classes matching status.
     */
    public function iconBoxClass(): string
    {
        return match ($this) {
            self::ACTIVE, self::COMPLETED => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40',
            self::PENDING => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/40',
            self::CANCELLED => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/40',
            self::INACTIVE, self::SUSPENDED => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700',
        };
    }

    public static function allForSelect(): array
    {
        return [
            (object)['id' => self::PENDING->value, 'name' => self::PENDING->label(), 'color' => self::PENDING->hexColor()],
            (object)['id' => self::ACTIVE->value, 'name' => self::ACTIVE->label(), 'color' => self::ACTIVE->hexColor()],
            (object)['id' => self::INACTIVE->value, 'name' => self::INACTIVE->label(), 'color' => self::INACTIVE->hexColor()],
            (object)['id' => self::CANCELLED->value, 'name' => self::CANCELLED->label(), 'color' => self::CANCELLED->hexColor()],
        ];
    }

    public function badgeConfig(): array
    {
        return [
            'bg' => match ($this) {
                self::PENDING => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/50',
                self::ACTIVE, self::COMPLETED => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/50',
                self::INACTIVE, self::SUSPENDED => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                self::CANCELLED => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/60 dark:border-rose-800/50',
            },
            'dot' => match ($this) {
                self::PENDING => 'bg-amber-500',
                self::ACTIVE, self::COMPLETED => 'bg-emerald-500',
                self::INACTIVE, self::SUSPENDED => 'bg-gray-500',
                self::CANCELLED => 'bg-rose-500',
            },
            'color' => $this->hexColor(),
            'label' => $this->label(),
        ];
    }

    public static function fromOrDefault(?string $status): ?self
    {
        if ($status === null || $status === '') {
            return null;
        }

        $s = strtolower(trim($status));
        return match ($s) {
            'pending' => self::PENDING,
            'active', 'completed' => self::ACTIVE,
            'inactive', 'suspended' => self::INACTIVE,
            'cancelled', 'canceled' => self::CANCELLED,
            default => self::tryFrom($s),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
