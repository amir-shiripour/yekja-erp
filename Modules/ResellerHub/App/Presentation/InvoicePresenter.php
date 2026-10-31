<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Presentation;

use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Models\ResellerInvoice;


final class InvoicePresenter
{
    public function statusEnum(ResellerInvoice $invoice): ?InvoiceStatus
    {
        $status = $invoice->getAttribute('status');

        return $status instanceof InvoiceStatus
            ? $status
            : InvoiceStatus::fromOrDefault((string)$status);
    }

    public function statusLabel(ResellerInvoice $invoice): string
    {
        return $this->statusEnum($invoice)?->label() ?? (string)$invoice->getAttribute('status');
    }

    public function statusBadgeClasses(ResellerInvoice $invoice): string
    {
        return $this->statusEnum($invoice)?->badgeClasses()
            ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }

    /**
     * @return array{bg: string, dot: string, color: string, label: string}
     */
    public function statusBadge(ResellerInvoice $invoice): array
    {
        $enum = $this->statusEnum($invoice);

        if ($enum) {
            return $enum->badgeConfig();
        }

        return [
            'bg' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700',
            'dot' => 'bg-gray-400',
            'color' => '#6b7280',
            'label' => (string)$invoice->getAttribute('status'),
        ];
    }

    public function barColor(ResellerInvoice $invoice): string
    {
        return $this->statusEnum($invoice)?->barColor() ?? 'bg-gray-400';
    }

    public function iconBoxClass(ResellerInvoice $invoice): string
    {
        return $this->statusEnum($invoice)?->iconBoxClass()
            ?? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
    }

    public function typeEnum(ResellerInvoice $invoice): ?InvoiceType
    {
        return InvoiceType::tryFrom((string)$invoice->getAttribute('type'));
    }

    public function typeLabel(ResellerInvoice $invoice): string
    {
        return $this->typeEnum($invoice)?->label() ?? (string)$invoice->getAttribute('type');
    }
}
