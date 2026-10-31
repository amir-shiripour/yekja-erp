<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the currency of every financial row explicit.
 *
 * Before this, all amounts were bare `bigint` values with no unit recorded
 * anywhere on the row, while `reseller_settings.currency` is per-reseller and
 * `PriceRoundingService` changes its rounding factor tenfold between `toman`
 * and `rial`. The consequence: if a reseller switched currency, every historical
 * invoice became ambiguous — 1,000,000 toman and 1,000,000 rial were stored
 * identically and could not be told apart afterwards.
 *
 * The column is a *snapshot*, taken when the row is created and never updated
 * afterwards: an invoice must keep meaning what it meant when it was issued.
 *
 * The column stays nullable at the schema level so no `doctrine/dbal` column
 * modification is needed; the model accessor falls back to `toman`, and the
 * backfill below fills every existing row.
 */
return new class extends Migration
{
    private const DEFAULT_CURRENCY = 'toman';

    public function up(): void
    {
        foreach (['reseller_invoices', 'reseller_orders'] as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'currency')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $anchor = Schema::hasColumn($table, 'reseller_id') ? 'reseller_id' : null;
                $column = $blueprint->string('currency', 8)->nullable();

                if ($anchor !== null) {
                    $column->after($anchor);
                }
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        foreach (['reseller_invoices', 'reseller_orders'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'currency')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropColumn('currency');
                });
            }
        }
    }

    /**
     * Copy each row's reseller currency onto the row itself.
     *
     * Rows whose reseller has no settings row fall back to the default, which
     * matches what the application displayed for them before the column existed.
     */
    private function backfill(): void
    {
        $hasSettings = Schema::hasTable('reseller_settings')
            && Schema::hasColumn('reseller_settings', 'currency');

        foreach (['reseller_invoices', 'reseller_orders'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'currency')) {
                continue;
            }

            if ($hasSettings) {
                DB::table($table)
                    ->join('reseller_settings', 'reseller_settings.reseller_id', '=', $table . '.reseller_id')
                    ->whereNull($table . '.currency')
                    ->whereNotNull('reseller_settings.currency')
                    ->update([$table . '.currency' => DB::raw('reseller_settings.currency')]);
            }

            DB::table($table)
                ->whereNull('currency')
                ->update(['currency' => self::DEFAULT_CURRENCY]);
        }
    }
};
