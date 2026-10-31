<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ResellerHub\App\Services\Checkout\PendingCheckoutService;

class ExpirePendingCheckoutsCommand extends Command
{
    protected $signature = 'resellerhub:expire-pending-checkouts {--dry-run} {--limit=200}';

    protected $description = 'Reclaim stale pending tenant checkout reservations and release subdomains.';

    public function handle(PendingCheckoutService $service): int
    {
        $expired = $service->expireStale((int)$this->option('limit'), (bool)$this->option('dry-run'));

        $this->info(sprintf(
            '%d pending checkout(s) %s.',
            count($expired),
            $this->option('dry-run') ? 'matched (dry run)' : 'reclaimed'
        ));

        return self::SUCCESS;
    }
}
