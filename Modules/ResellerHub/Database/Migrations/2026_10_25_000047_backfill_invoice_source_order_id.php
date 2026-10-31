<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `reseller_invoices.source_order_id` is the single source of truth for the
 * order → invoice link. Historically the same relationship was expressed three
 * different ways:
 *
 *   reseller_orders.invoice_id          (order → the invoice that created it)
 *   reseller_invoices.meta->source_order_id  (JSON, unindexed, int or string)
 *   reseller_invoices.tenant_id + package    (guessing, removed)
 *
 * `ResellerOrder::invoices()` used to guess by tenant, which is why the relation
 * returned unrelated invoices. Now that the relation reads the FK only, existing
 * rows need to be linked once so nothing disappears from the UI.
 *
 * The migration is idempotent and only fills NULLs — it never overwrites a link
 * that the application already wrote.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reseller_orders')
            || !Schema::hasTable('reseller_invoices')
            || !Schema::hasColumn('reseller_invoices', 'source_order_id')) {
            return;
        }

        // 1) order.invoice_id → invoice.source_order_id
        DB::table('reseller_orders')
            ->whereNotNull('invoice_id')
            ->select(['id', 'invoice_id'])
            ->orderBy('id')
            ->chunk(500, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('reseller_invoices')
                        ->where('id', $order->invoice_id)
                        ->whereNull('source_order_id')
                        ->update(['source_order_id' => $order->id]);
                }
            });

        // 2) invoice.meta->source_order_id → invoice.source_order_id
        //    JSON linkage was written with both int and string values over time.
        if (Schema::hasColumn('reseller_invoices', 'meta')) {
            DB::table('reseller_invoices')
                ->whereNull('source_order_id')
                ->whereNotNull('meta')
                ->select(['id', 'meta'])
                ->orderBy('id')
                ->chunk(500, function ($invoices): void {
                    foreach ($invoices as $invoice) {
                        $meta = json_decode((string) $invoice->meta, true);
                        if (!is_array($meta)) {
                            continue;
                        }

                        $candidate = $meta['source_order_id'] ?? null;
                        if ($candidate === null || !is_numeric($candidate)) {
                            continue;
                        }

                        $orderId = (int) $candidate;
                        if ($orderId <= 0) {
                            continue;
                        }

                        $exists = DB::table('reseller_orders')->where('id', $orderId)->exists();
                        if (!$exists) {
                            continue;
                        }

                        DB::table('reseller_invoices')
                            ->where('id', $invoice->id)
                            ->whereNull('source_order_id')
                            ->update(['source_order_id' => $orderId]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Data backfill only — nothing to roll back. Removing the links would
        // destroy information that cannot be reconstructed from a single column.
    }
};
