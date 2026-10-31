<?php

namespace Modules\ResellerHub\App\DTOs;

readonly class PaymentRecordDTO
{
    public function __construct(
        public ?string $paymentMethod = null,
        public ?string $transactionRef = null,
        public ?string $gateway = null,
        public ?string $notes = null,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            paymentMethod: isset($data['payment_method']) ? (string)$data['payment_method'] : null,
            transactionRef: isset($data['transaction_ref']) ? (string)$data['transaction_ref'] : null,
            gateway: isset($data['gateway']) ? (string)$data['gateway'] : null,
            notes: isset($data['notes']) ? (string)$data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'payment_method' => $this->paymentMethod,
            'transaction_ref' => $this->transactionRef,
            'gateway' => $this->gateway,
            'notes' => $this->notes,
        ];
    }
}
