<?php

namespace Modules\ResellerHub\App\Services\Billing;

final readonly class ProrationResult
{
    public function __construct(
        public int $fullDifference,
        public int $proratedAmount,
        public int $remainingDays,
        public int $totalDays,
    )
    {
    }
}
