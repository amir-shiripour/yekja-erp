<?php

namespace Modules\ResellerHub\App\Enums;

enum InvoiceType: string
{
    case OFFICIAL = 'official';

    public function label(): string
    {
        return match ($this) {
            self::OFFICIAL => 'فاکتور رسمی',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
