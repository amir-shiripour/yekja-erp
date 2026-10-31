<?php

namespace Modules\ResellerHub\App\Enums;

use Modules\ResellerHub\App\Helpers\ResellerHelper;

enum PackageDiscountType: string
{
    case AMOUNT = 'amount';

    /**
     * Persian human label for discount type.
     */
    public function label(): string
    {
        return 'مبلغ ثابت';
    }

    /**
     * Suffix / symbol for discount input.
     */
    public function symbol(?string $currency = null): string
    {
        return ResellerHelper::currencyLabel($currency);
    }

    /**
     * Calculate discount reduction amount in Rial/Toman.
     */
    public function calculateDiscount(int $rawPrice, float|int $discountValue): int
    {
        if ($rawPrice <= 0 || $discountValue <= 0) {
            return 0;
        }

        return (int)min($rawPrice, max(0, (float)$discountValue));
    }

    /**
     * Format discount value with symbol in Persian numerals.
     */
    public function formatValue(float|int $value, ?string $currency = null): string
    {
        return ResellerHelper::formatPrice($value, $currency);
    }

    public static function labels(): array
    {
        return [
            self::AMOUNT->value => self::AMOUNT->label(),
        ];
    }

    public static function fromOrDefault(?string $value): self
    {
        return self::AMOUNT;
    }
}
