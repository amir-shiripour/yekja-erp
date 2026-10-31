<?php

namespace Modules\ResellerHub\App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ResellerHub\App\Enums\QuotaKey;

class QuotaThresholdExceeded implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tenant          $tenant,
        public QuotaKey|string $quotaKey,
        public ?int            $limit,
        public int             $used,
        public string          $severity = 'warning'
    )
    {
    }
}
