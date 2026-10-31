<?php

namespace Modules\ResellerHub\App\DTOs;

readonly class InvoiceItemDTO
{
    public function __construct(
        public string  $itemType,
        public ?string $itemKey,
        public string  $title,
        public ?string $description,
        public int     $durationMonths,
        public int     $quantity,
        public int     $unitPrice,
        public int     $discountAmount = 0,
        public ?array  $meta = null,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            itemType: (string)($data['item_type'] ?? 'module'),
            itemKey: isset($data['item_key']) ? (string)$data['item_key'] : (isset($data['item_id']) ? (string)$data['item_id'] : null),
            title: trim((string)($data['title'] ?? ($data['description'] ?? 'آیتم فاکتور'))),
            description: isset($data['description']) ? (string)$data['description'] : (isset($data['title']) ? (string)$data['title'] : null),
            durationMonths: (int)($data['duration_months'] ?? 12),
            quantity: max(1, (int)($data['quantity'] ?? 1)),
            unitPrice: max(0, (int)($data['unit_price'] ?? 0)),
            discountAmount: max(0, (int)($data['discount_amount'] ?? 0)),
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'item_type' => $this->itemType,
            'item_key' => $this->itemKey,
            'title' => $this->title,
            'description' => $this->description,
            'duration_months' => $this->durationMonths,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'discount_amount' => $this->discountAmount,
            'meta' => $this->meta,
        ];
    }
}
