<?php

namespace Modules\ResellerHub\App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ResellerHub\App\Models\ResellerOrder;

class OrderStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ResellerOrder $order,
        public string        $newStatus,
        public ?string       $oldStatus = null
    )
    {
    }
}
