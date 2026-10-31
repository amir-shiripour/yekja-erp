<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Invoices;

use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;


final class InvoiceOrderResolver
{
    public function resolve(ResellerInvoice $invoice): ?ResellerOrder
    {
        if ($invoice->relationLoaded('order') && $invoice->order) {
            return $invoice->order;
        }

        if ($invoice->source_order_id) {
            return $invoice->sourceOrder ?? ResellerOrder::query()->find($invoice->source_order_id);
        }

        $direct = ResellerOrder::query()->where('invoice_id', $invoice->getKey())->first();

        if ($direct) {
            return $direct;
        }

        $meta = $invoice->meta;
        $metaOrderId = is_array($meta) ? ($meta['source_order_id'] ?? null) : null;

        if (!empty($metaOrderId)) {
            return ResellerOrder::query()->find((int)$metaOrderId);
        }

        if ($invoice->parent_invoice_id) {
            return ResellerOrder::query()->where('invoice_id', $invoice->parent_invoice_id)->first();
        }

        return null;
    }
}
