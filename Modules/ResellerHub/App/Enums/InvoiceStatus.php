<?php

namespace Modules\ResellerHub\App\Enums;

enum InvoiceStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
    case DRAFT = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => __('resellerhub::messages.invoices.status_unpaid'),
            self::PAID => __('resellerhub::messages.invoices.status_paid'),
            self::OVERDUE => __('resellerhub::messages.invoices.status_overdue'),
            self::CANCELLED => __('resellerhub::messages.invoices.status_cancelled'),
            self::DRAFT => __('resellerhub::messages.invoices.status_draft'),
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
            self::DRAFT => [self::UNPAID, self::CANCELLED],
            self::UNPAID => [self::PAID, self::OVERDUE, self::CANCELLED],
            self::OVERDUE => [self::PAID, self::CANCELLED],
            self::PAID => [self::CANCELLED],
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
            self::PAID => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
            self::UNPAID, self::OVERDUE => 'bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border-amber-200 dark:border-amber-500/30',
            self::CANCELLED => 'bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
            self::DRAFT => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/30',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PAID => 'emerald',
            self::UNPAID, self::OVERDUE => 'amber',
            self::CANCELLED => 'rose',
            self::DRAFT => 'indigo',
        };
    }

    public function hexColor(): string
    {
        return match ($this) {
            self::UNPAID => '#ff8000',
            self::PAID => '#00ff40',
            self::OVERDUE => '#ff8000',
            self::CANCELLED => '#ff0000',
            self::DRAFT => '#6366f1',
        };
    }

    /**
     * Vertical status bar color class (e.g. for card edges).
     */
    public function barColor(): string
    {
        return match ($this) {
            self::PAID => 'bg-emerald-500',
            self::UNPAID, self::OVERDUE => 'bg-amber-500',
            self::CANCELLED => 'bg-rose-500',
            self::DRAFT => 'bg-indigo-500',
        };
    }

    /**
     * Icon container styling classes matching status.
     */
    public function iconBoxClass(): string
    {
        return match ($this) {
            self::PAID => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40',
            self::UNPAID, self::OVERDUE => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/40',
            self::CANCELLED => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/40',
            self::DRAFT => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/40',
        };
    }

    /**
     * UI Badge styling configuration for this status (bg, dot, color, label).
     *
     * @return array{bg: string, dot: string, color: string, label: string}
     */
    public function badgeConfig(): array
    {
        return [
            'bg' => match ($this) {
                self::DRAFT => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border-indigo-200/60 dark:border-indigo-800/50',
                self::UNPAID, self::OVERDUE => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/50',
                self::PAID => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/50',
                self::CANCELLED => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/60 dark:border-rose-800/50',
            },
            'dot' => match ($this) {
                self::DRAFT => 'bg-indigo-500',
                self::UNPAID, self::OVERDUE => 'bg-amber-500',
                self::PAID => 'bg-emerald-500',
                self::CANCELLED => 'bg-rose-500',
            },
            'color' => $this->hexColor(),
            'label' => $this->label(),
        ];
    }

    /**
     * Visual tone configuration for invoice state.
     */
    public function tone(): array
    {
        return match ($this) {
            self::PAID => [
                'bar' => 'bg-emerald-500',
                'icon' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                'badge' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/60',
                'dot' => 'bg-emerald-500',
            ],
            self::OVERDUE, self::CANCELLED => [
                'bar' => 'bg-red-500',
                'icon' => 'bg-red-50 dark:bg-red-500/15 text-red-600 dark:text-red-400',
                'badge' => 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border-red-200/60',
                'dot' => 'bg-red-500',
            ],
            default => [
                'bar' => 'bg-amber-400',
                'icon' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400',
                'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/60',
                'dot' => 'bg-amber-400',
            ],
        };
    }

    /**
     * Resolve tone from string or enum instance.
     */
    public static function toneFor(string|self|null $status): array
    {
        if ($status instanceof self) {
            return $status->tone();
        }

        $s = strtolower(trim((string)$status));
        $enum = match ($s) {
            'paid' => self::PAID,
            'overdue' => self::OVERDUE,
            'cancelled', 'canceled' => self::CANCELLED,
            'pending', 'unpaid' => self::UNPAID,
            'draft' => self::DRAFT,
            default => self::tryFrom($s) ?? self::UNPAID,
        };

        return $enum->tone();
    }

    public static function fromOrDefault(?string $status): ?self
    {
        if ($status === null || $status === '') {
            return null;
        }

        $s = strtolower(trim($status));
        return match ($s) {
            'paid' => self::PAID,
            'overdue' => self::OVERDUE,
            'cancelled', 'canceled' => self::CANCELLED,
            'pending', 'unpaid' => self::UNPAID,
            'draft' => self::DRAFT,
            default => self::tryFrom($s),
        };
    }

    public static function allForSelect(): array
    {
        return array_map(fn($s) => (object)[
            'id' => $s->value,
            'name' => $s->label(),
            'color' => $s->hexColor(),
        ], [
            self::UNPAID,
            self::PAID,
            self::OVERDUE,
            self::CANCELLED,
            self::DRAFT,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
