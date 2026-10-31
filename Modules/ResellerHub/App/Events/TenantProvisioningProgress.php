<?php

namespace Modules\ResellerHub\App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantProvisioningProgress implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tenantId;
    public string $step;
    public int $progress;
    public string $message;
    public ?string $redirectUrl;

    public function __construct(
        int     $tenantId,
        string  $step,
        int     $progress,
        string  $message,
        ?string $redirectUrl = null
    )
    {
        $this->tenantId = $tenantId;
        $this->step = $step;
        $this->progress = $progress;
        $this->message = $message;
        $this->redirectUrl = $redirectUrl;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("tenants.{$this->tenantId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TenantProvisioningProgress';
    }

    public function broadcastWith(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'step' => $this->step,
            'progress' => $this->progress,
            'message' => $this->message,
            'redirect_url' => $this->redirectUrl,
        ];
    }
}
