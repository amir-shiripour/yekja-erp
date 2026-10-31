<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Payment;

use Modules\ResellerHub\App\Helpers\CurrencyConverter;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\Settings\Entities\Setting;
use Throwable;


final class GatewayAmountResolver
{
    /** Request-scoped cache of the global settings row set. */
    private static ?array $globalSettings = null;

    /**
     * Everything needed to initialise or verify a gateway payment.
     *
     * @return array{amount: int, currency: string, factor: float, source_currency: string}
     */
    public function forInvoice(ResellerInvoice $invoice): array
    {
        return $this->resolve(
            (int)$invoice->total,
            $this->sourceCurrency($invoice)
        );
    }

    /**
     * @return array{amount: int, currency: string, factor: float, source_currency: string}
     */
    public function resolve(int $amount, ?string $sourceCurrency): array
    {
        $target = $this->paymentCurrency();
        $source = $sourceCurrency ?: $target;
        $factor = CurrencyConverter::conversionFactor($source, $target);

        return [
            'amount' => (int)round($amount * $factor),
            'currency' => $target,
            'factor' => $factor,
            'source_currency' => $source,
        ];
    }

    /**
     * Convert an invoice-denominated amount into the gateway currency.
     */
    public function convert(int $amount, ?string $sourceCurrency): int
    {
        return $this->resolve($amount, $sourceCurrency)['amount'];
    }

    /**
     * The currency the invoice itself is expressed in.
     */
    public function sourceCurrency(ResellerInvoice $invoice): string
    {
        return (string)($invoice->currency ?: $this->paymentCurrency());
    }

    /**
     * The currency every gateway in this installation charges in.
     */
    public function paymentCurrency(): string
    {
        $settings = $this->globalSettings();

        return strtolower(trim((string)($settings['payment_currency'] ?? 'toman'))) ?: 'toman';
    }

    /**
     * Reset the request-scoped cache (used by tests and long-running workers).
     */
    public static function flush(): void
    {
        self::$globalSettings = null;
    }

    /**
     * @return array<string, mixed>
     */
    private function globalSettings(): array
    {
        if (self::$globalSettings !== null) {
            return self::$globalSettings;
        }

        try {
            self::$globalSettings = Setting::query()->pluck('value', 'key')->toArray();
        } catch (Throwable) {
            self::$globalSettings = [];
        }

        return self::$globalSettings;
    }
}
