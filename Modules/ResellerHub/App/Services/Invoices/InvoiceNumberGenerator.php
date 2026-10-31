<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Invoices;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerSetting;


final class InvoiceNumberGenerator
{
    public function generate(int $resellerId, string $type = ResellerInvoice::TYPE_OFFICIAL): string
    {
        $generator = function () use ($resellerId, $type): string {
            return DB::transaction(function () use ($resellerId, $type) {
                $settings = ResellerSetting::allForReseller($resellerId);

                $prefix = (string)($settings['invoice_prefix'] ?? config('resellerhub.invoice_prefix', 'INV-'));
                $middle = (string)($settings['invoice_middle_prefix'] ?? JalaliDateHelper::currentYear());
                $suffix = (string)($settings['invoice_suffix'] ?? '');
                $padding = max(1, (int)($settings['invoice_padding'] ?? 4));

                $middlePart = $middle !== '' ? $middle . '-' : '';
                $pattern = $prefix . $middlePart;

                $lastNumber = ResellerInvoice::withTrashed()
                    ->where('reseller_id', $resellerId)
                    ->where('type', $type)
                    ->whereNotNull('invoice_number')
                    ->where('invoice_number', 'LIKE', $pattern . '%')
                    ->lockForUpdate()
                    ->latest('id')
                    ->value('invoice_number');

                $sequence = $this->sequenceFrom((string)$lastNumber, $pattern, $suffix);
                $sequence = max($sequence + 1, 1);

                do {
                    $number = sprintf('%s%s%0' . $padding . 'd%s', $prefix, $middlePart, $sequence, $suffix);
                    $sequence++;
                } while (
                    ResellerInvoice::withTrashed()
                        ->where('reseller_id', $resellerId)
                        ->where('invoice_number', $number)
                        ->exists()
                );

                return $number;
            });
        };

        try {
            $lock = Cache::lock("reseller_invoice_seq_lock_{$resellerId}_{$type}", 5);
            return $lock->block(4, $generator);
        } catch (\Throwable) {
            return $generator();
        }
    }

    private function sequenceFrom(string $lastNumber, string $pattern, string $suffix): int
    {
        if ($lastNumber === '') {
            return 0;
        }

        $numeric = str_starts_with($lastNumber, $pattern)
            ? substr($lastNumber, strlen($pattern))
            : $lastNumber;

        if ($suffix !== '' && str_ends_with($numeric, $suffix)) {
            $numeric = substr($numeric, 0, -strlen($suffix));
        }

        return is_numeric($numeric) ? (int)$numeric : 0;
    }
}
