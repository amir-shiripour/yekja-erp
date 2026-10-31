<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Gateway;

use App\Services\PaymentService;

class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentService
    {
        return app(PaymentService::class, ['gateway' => $gateway]);
    }
}
