<?php

namespace Modules\ResellerHub\App\DTOs;

use Modules\ResellerHub\App\Models\ResellerInvoice;

readonly class RenewOrderDTO
{
    public function __construct(
        public string  $type = ResellerInvoice::TYPE_OFFICIAL,
        public ?string $notes = null,
        public bool    $manual = false,
        public bool    $createdByWorkflow = false,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: (string)($data['type'] ?? ResellerInvoice::TYPE_OFFICIAL),
            notes: isset($data['notes']) ? (string)$data['notes'] : null,
            manual: !empty($data['manual']),
            createdByWorkflow: !empty($data['created_by_workflow']),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'notes' => $this->notes,
            'manual' => $this->manual,
            'created_by_workflow' => $this->createdByWorkflow,
        ];
    }
}
