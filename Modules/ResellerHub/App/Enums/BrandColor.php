<?php

namespace Modules\ResellerHub\App\Enums;

enum BrandColor: string
{
    case CLASSIC_INDIGO = '#4f46e5';
    case MEDICAL_CYAN = '#0ea5e9';
    case HEALTH_EMERALD = '#10b981';
    case ROYAL_PURPLE = '#8b5cf6';
    case HEALTH_CRIMSON = '#e11d48';
    case MODERN_AMBER = '#f59e0b';
    case CORPORATE_NAVY = '#1e3a8a';
    case DARK_SLATE = '#334155';

    /**
     * Persian name/label for the brand color preset.
     */
    public function label(): string
    {
        return match ($this) {
            self::CLASSIC_INDIGO => 'نیلی کلاسیک',
            self::MEDICAL_CYAN => 'فیروزه‌ای پزشکی',
            self::HEALTH_EMERALD => 'زمردی سلامت',
            self::ROYAL_PURPLE => 'بنفش رویال',
            self::HEALTH_CRIMSON => 'زرشکی سلامت',
            self::MODERN_AMBER => 'کهربایی مدرن',
            self::CORPORATE_NAVY => 'آبی تیره سازمانی',
            self::DARK_SLATE => 'دودی تیره',
        };
    }

    /**
     * Hex color code.
     */
    public function hex(): string
    {
        return $this->value;
    }

    /**
     * Presets formatted for UI selectors, wizards, and settings.
     *
     * @return array<int, array{name: string, hex: string}>
     */
    public static function presets(): array
    {
        return array_map(fn(self $color) => [
            'name' => $color->label(),
            'hex' => $color->value,
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
