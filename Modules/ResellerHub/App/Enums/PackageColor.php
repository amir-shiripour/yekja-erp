<?php

namespace Modules\ResellerHub\App\Enums;

enum PackageColor: string
{
    case INDIGO = 'indigo';
    case PURPLE = 'purple';
    case BLUE = 'blue';
    case CYAN = 'cyan';
    case TEAL = 'teal';
    case EMERALD = 'emerald';
    case AMBER = 'amber';
    case ORANGE = 'orange';
    case ROSE = 'rose';
    case GRAY = 'gray';

    /**
     * Persian label for the color.
     */
    public function label(): string
    {
        return match ($this) {
            self::INDIGO => 'نیلی',
            self::PURPLE => 'بنفش',
            self::BLUE => 'آبی',
            self::CYAN => 'فیروزه‌ای',
            self::TEAL => 'کله‌غازی',
            self::EMERALD => 'زمردی',
            self::AMBER => 'کهربایی',
            self::ORANGE => 'نارنجی',
            self::ROSE => 'رز',
            self::GRAY => 'خاکستری',
        };
    }

    /**
     * Hex code of the color.
     */
    public function hex(): string
    {
        return match ($this) {
            self::INDIGO => '#4f46e5',
            self::PURPLE => '#9333ea',
            self::BLUE => '#2563eb',
            self::CYAN => '#0891b2',
            self::TEAL => '#0d9488',
            self::EMERALD => '#059669',
            self::AMBER => '#d97706',
            self::ORANGE => '#ea580c',
            self::ROSE => '#e11d48',
            self::GRAY => '#4b5563',
        };
    }

    /**
     * Background class for badges/cards.
     */
    public function bgClass(): string
    {
        return match ($this) {
            self::INDIGO => 'bg-indigo-600',
            self::PURPLE => 'bg-purple-600',
            self::BLUE => 'bg-blue-600',
            self::CYAN => 'bg-cyan-600',
            self::TEAL => 'bg-teal-600',
            self::EMERALD => 'bg-emerald-600',
            self::AMBER => 'bg-amber-600',
            self::ORANGE => 'bg-orange-600',
            self::ROSE => 'bg-rose-600',
            self::GRAY => 'bg-gray-600',
        };
    }

    /**
     * Light badge classes with border and dark-mode support.
     */
    public function lightClass(): string
    {
        return match ($this) {
            self::INDIGO => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
            self::PURPLE => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
            self::BLUE => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800',
            self::CYAN => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border-cyan-200 dark:border-cyan-800',
            self::TEAL => 'bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-800',
            self::EMERALD => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
            self::AMBER => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
            self::ORANGE => 'bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800',
            self::ROSE => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800',
            self::GRAY => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700',
        };
    }

    /**
     * Full configuration array for backward compatibility.
     */
    public function config(): array
    {
        return [
            'name' => $this->label(),
            'bg' => $this->bgClass(),
            'light' => $this->lightClass(),
            'hex' => $this->hex(),
        ];
    }

    /**
     * Standard color presets for color-pickers.
     */
    public static function presets(): array
    {
        $res = [];
        foreach (self::cases() as $case) {
            $res[] = [
                'key' => $case->value,
                'label' => $case->label(),
                'name' => $case->label(),
                'hex' => $case->hex(),
            ];
        }
        return $res;
    }

    /**
     * All configs mapped by key.
     */
    public static function allConfigs(): array
    {
        $res = [];
        foreach (self::cases() as $case) {
            $res[$case->value] = $case->config();
        }
        return $res;
    }

    public static function fromOrDefault(?string $value): self
    {
        if (empty($value)) {
            return self::INDIGO;
        }

        $clean = strtolower(trim($value));
        return self::tryFrom($clean) ?? self::INDIGO;
    }
}
