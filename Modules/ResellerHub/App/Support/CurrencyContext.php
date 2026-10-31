<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Throwable;


final class CurrencyContext
{
    public const DEFAULT_CURRENCY = Money::DEFAULT_CURRENCY;

    /** @var array<int, string> */
    private static array $perReseller = [];

    /** @var array<string, bool> */
    private static array $columns = [];

    public static function forReseller(?int $resellerId): string
    {
        if (!$resellerId) {
            return self::DEFAULT_CURRENCY;
        }

        if (isset(self::$perReseller[$resellerId])) {
            return self::$perReseller[$resellerId];
        }

        $currency = self::DEFAULT_CURRENCY;

        try {
            if (Schema::hasTable('reseller_settings')) {
                $configured = ResellerSetting::getCurrencyForReseller($resellerId);
                if (is_string($configured) && $configured !== '') {
                    $currency = Money::normalizeCurrency($configured);
                }
            }
        } catch (Throwable) {
            // Keep the default. A display/creation path must not 500 because a
            // settings row is temporarily unavailable.
        }

        return self::$perReseller[$resellerId] = $currency;
    }


    public static function snapshot(Model $model, string $column = 'currency'): void
    {
        if (!empty($model->getAttribute($column))) {
            return;
        }

        if (!self::supportsColumn($model->getTable(), $column)) {
            return;
        }

        $model->setAttribute($column, self::forReseller((int)$model->getAttribute('reseller_id')));
    }

    public static function supportsColumn(string $table, string $column = 'currency'): bool
    {
        $key = $table . '.' . $column;

        if (array_key_exists($key, self::$columns)) {
            return self::$columns[$key];
        }

        try {
            return self::$columns[$key] = Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return self::$columns[$key] = false;
        }
    }

    /**
     * requests, mirroring ResellerSetting's own runtime cache handling.
     */
    public static function clear(): void
    {
        self::$perReseller = [];
        self::$columns = [];
    }

    /**
     * Parse a free-form persisted value into a Money instance.
     */
    public static function money(int|float|string|null $amount, ?int $resellerId = null): Money
    {
        return Money::of($amount, self::forReseller($resellerId));
    }

}
