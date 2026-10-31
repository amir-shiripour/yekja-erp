<?php

namespace Modules\ResellerHub\App\Actions\Orders;

use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\ResellerBillingService;

final class RequestOrderUpgradeAction
{
    public function __construct(
        private ResellerBillingService $billingService
    )
    {
    }

    /**
     * The upgrade is only provisioned to the tenant if the invoice is free or upon payment.
     */
    public function execute(ResellerOrder $order, int $newPackageId, array $options = []): ResellerInvoice
    {
        return $this->billingService->createUpgradeInvoiceFromOrder($order, $newPackageId, $options);
    }
}
