<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Orders;

use Illuminate\Support\Facades\Cache;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\ResellerOrder;

/**
 * Mirrors {@see \Modules\ResellerHub\App\Services\Invoices\InvoiceNumberGenerator}:
 * an atomic lock serialises allocation, and a uniqueness probe covers numbers
 * created outside this code path.
 */
final class OrderNumberGenerator
{
    public function generate(int $resellerId): string
    {
        $lock = Cache::lock("reseller_order_seq_lock_{$resellerId}", 5);

        return $lock->block(4, function () use ($resellerId): string {
            $prefix = sprintf('%s-%d-', (string)config('resellerhub.order_prefix', 'RORD'), JalaliDateHelper::currentYear());

            $lastNumber = ResellerOrder::withTrashed()
                ->where('reseller_id', $resellerId)
                ->whereNotNull('order_number')
                ->where('order_number', 'LIKE', $prefix . '%')
                ->latest('id')
                ->value('order_number');

            $sequence = 0;

            if (is_string($lastNumber) && str_starts_with($lastNumber, $prefix)) {
                $numeric = substr($lastNumber, strlen($prefix));
                $sequence = is_numeric($numeric) ? (int)$numeric : 0;
            }

            $sequence = max($sequence + 1, 1);

            do {
                $number = sprintf('%s%04d', $prefix, $sequence);
                $sequence++;
            } while (
                ResellerOrder::withTrashed()
                    ->where('reseller_id', $resellerId)
                    ->where('order_number', $number)
                    ->exists()
            );

            return $number;
        });
    }
}
