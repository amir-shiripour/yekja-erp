<?php

namespace Modules\ResellerHub\App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ResellerHub\App\Models\ResellerInvoice;

class InvoiceStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ResellerInvoice $invoice,
        public string          $newStatus,
        public ?string         $oldStatus = null
    )
    {
    }
}
