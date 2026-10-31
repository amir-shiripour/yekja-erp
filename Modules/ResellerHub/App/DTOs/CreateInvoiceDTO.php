<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\DTOs;

use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Models\ResellerInvoice;

readonly class CreateInvoiceDTO
{
    /**
     * @param InvoiceItemDTO[] $items
     */
    public function __construct(
        public ?int                 $tenantId,
        public ?int                 $subAccountId,
        public ?int                 $packageId,
        public ?string              $invoiceNumber,
        public InvoiceType|string   $type,
        public InvoiceStatus|string $status,
        public ?string              $subAccountName,
        public ?string              $subAccountMobile,
        public ?string              $subAccountEmail,
        public ?string              $issueDate,
        public ?string              $dueDate,
        public string               $discountType,
        public int                  $discountValue,
        public float                $taxPercent,
        public ?string              $paymentMethod,
        public ?string              $transactionRef,
        public ?string              $notes,
        public ?string              $terms,
        public array                $meta,
        public array                $items,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        $parsedItems = [];
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if ($item instanceof InvoiceItemDTO) {
                    $parsedItems[] = $item;
                } elseif (is_array($item)) {
                    $parsedItems[] = InvoiceItemDTO::fromArray($item);
                }
            }
        }

        $subName = isset($data['sub_account_name']) ? trim((string)$data['sub_account_name']) : null;
        $subMobile = isset($data['sub_account_mobile']) ? trim((string)$data['sub_account_mobile']) : null;
        $subEmail = isset($data['sub_account_email']) ? trim((string)$data['sub_account_email']) : null;

        $type = $data['type'] ?? InvoiceType::OFFICIAL;
        if (is_string($type)) {
            $type = InvoiceType::tryFrom($type) ?? InvoiceType::OFFICIAL;
        }

        $status = $data['status'] ?? InvoiceStatus::UNPAID;
        if (is_string($status)) {
            $status = InvoiceStatus::tryFrom($status) ?? InvoiceStatus::UNPAID;
        }

        return new self(
            tenantId: isset($data['tenant_id']) && $data['tenant_id'] !== '' ? (int)$data['tenant_id'] : null,
            subAccountId: isset($data['sub_account_id']) && $data['sub_account_id'] !== '' ? (int)$data['sub_account_id'] : null,
            packageId: isset($data['package_id']) && $data['package_id'] !== '' ? (int)$data['package_id'] : null,
            invoiceNumber: !empty($data['invoice_number']) ? trim((string)$data['invoice_number']) : null,
            type: $type,
            status: $status,
            subAccountName: $subName,
            subAccountMobile: $subMobile,
            subAccountEmail: $subEmail,
            issueDate: isset($data['issue_date']) ? (string)$data['issue_date'] : null,
            dueDate: isset($data['due_date']) ? (string)$data['due_date'] : null,
            discountType: (string)($data['discount_type'] ?? 'fixed'),
            discountValue: (int)($data['discount_value'] ?? 0),
            taxPercent: (float)($data['tax_percent'] ?? 0),
            paymentMethod: isset($data['payment_method']) ? (string)$data['payment_method'] : null,
            transactionRef: isset($data['transaction_ref']) ? (string)$data['transaction_ref'] : null,
            notes: isset($data['notes']) ? (string)$data['notes'] : null,
            terms: isset($data['terms']) ? (string)$data['terms'] : null,
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [],
            items: $parsedItems,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'sub_account_id' => $this->subAccountId,
            'package_id' => $this->packageId,
            'invoice_number' => $this->invoiceNumber,
            'type' => $this->type instanceof InvoiceType ? $this->type->value : (string)$this->type,
            'status' => $this->status instanceof InvoiceStatus ? $this->status->value : (string)$this->status,
            'sub_account_name' => $this->subAccountName,
            'sub_account_mobile' => $this->subAccountMobile,
            'sub_account_email' => $this->subAccountEmail,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'tax_percent' => $this->taxPercent,
            'payment_method' => $this->paymentMethod,
            'transaction_ref' => $this->transactionRef,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'meta' => $this->meta,
            'items' => array_map(fn(InvoiceItemDTO $i) => $i->toArray(), $this->items),
        ];
    }
}
