<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\DTOs;

use Carbon\Carbon;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;

readonly class UpdateInvoiceDTO
{
    /**
     * @param InvoiceItemDTO[]|array[]|null $items
     */
    public function __construct(
        public ?int           $tenantId = null,
        public ?int           $packageId = null,
        public ?InvoiceType   $type = null,
        public ?InvoiceStatus $status = null,
        public ?string        $subAccountName = null,
        public ?string        $subAccountMobile = null,
        public ?string        $subAccountEmail = null,
        public ?Carbon        $issueDate = null,
        public ?Carbon        $dueDate = null,
        public ?string        $discountType = null,
        public ?int           $discountValue = null,
        public ?float         $taxPercent = null,
        public ?string        $paymentMethod = null,
        public ?string        $transactionRef = null,
        public ?string        $notes = null,
        public ?string        $terms = null,
        public ?array         $items = null,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        $issueDate = !empty($data['issue_date'])
            ? JalaliDateHelper::toCarbon($data['issue_date'])
            : null;

        $dueDate = !empty($data['due_date'])
            ? JalaliDateHelper::toCarbon($data['due_date'])
            : null;

        $status = null;
        if (!empty($data['status'])) {
            $status = $data['status'] instanceof InvoiceStatus
                ? $data['status']
                : InvoiceStatus::fromOrDefault((string)$data['status']);
        }

        $type = null;
        if (!empty($data['type'])) {
            $type = $data['type'] instanceof InvoiceType
                ? $data['type']
                : InvoiceType::tryFrom((string)$data['type']);
        }

        return new self(
            tenantId: isset($data['tenant_id']) && $data['tenant_id'] !== '' ? (int)$data['tenant_id'] : null,
            packageId: isset($data['package_id']) && $data['package_id'] !== '' ? (int)$data['package_id'] : null,
            type: $type,
            status: $status,
            subAccountName: isset($data['sub_account_name']) ? trim((string)$data['sub_account_name']) : null,
            subAccountMobile: isset($data['sub_account_mobile']) ? trim((string)$data['sub_account_mobile']) : null,
            subAccountEmail: isset($data['sub_account_email']) ? trim((string)$data['sub_account_email']) : null,
            issueDate: $issueDate,
            dueDate: $dueDate,
            discountType: isset($data['discount_type']) ? (string)$data['discount_type'] : null,
            discountValue: isset($data['discount_value']) ? (int)$data['discount_value'] : null,
            taxPercent: isset($data['tax_percent']) ? (float)$data['tax_percent'] : null,
            paymentMethod: isset($data['payment_method']) ? (string)$data['payment_method'] : null,
            transactionRef: isset($data['transaction_ref']) ? (string)$data['transaction_ref'] : null,
            notes: isset($data['notes']) ? (string)$data['notes'] : null,
            terms: isset($data['terms']) ? (string)$data['terms'] : null,
            items: isset($data['items']) && is_array($data['items']) ? $data['items'] : null,
        );
    }
}
