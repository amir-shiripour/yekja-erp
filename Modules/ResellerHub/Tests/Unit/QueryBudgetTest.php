<?php

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerAllowedModule;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Query-budget guards.
 *
 * Performance work that is not measured regresses silently, so these tests
 * assert the *shape* of the queries rather than a wall-clock number: a stable
 * query count is deterministic in CI, a millisecond threshold is not.
 */
class QueryBudgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_allowed_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->string('module_slug');
            $table->timestamps();
        });

        Schema::create('reseller_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('source_order_id')->nullable();
            $table->unsignedBigInteger('parent_invoice_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->string('type', 20)->default('official');
            $table->string('source', 64)->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->string('sub_account_name', 150)->nullable();
            $table->string('sub_account_mobile', 50)->nullable();
            $table->string('sub_account_email', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->string('discount_type', 20)->default('fixed');
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reseller_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('item_type', 20)->default('custom');
            $table->string('item_key')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_months')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // recalculateTotals() -> PriceRoundingService -> ResellerSetting.
        // Only the columns the rounding path reads are declared here; the real
        // table carries ~50 more, none of which this code path touches.
        Schema::create('reseller_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->string('currency')->default('toman');
            $table->string('tax_mode')->nullable();
            $table->string('reseller_rounding_mode', 20)->default('none');
            $table->unsignedInteger('reseller_rounding_factor')->default(1000);
            $table->timestamps();
        });

        // ResellerSetting memoizes per process, so without this the second
        // measured call in a test would silently skip its lookup and the two
        // halves of a comparison would not be measured under equal conditions.
        ResellerSetting::clearRuntimeCache();

        Event::fake();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    private function resellerWithModules(array $slugs): Reseller
    {
        $user = User::create(['name' => 'u', 'email' => uniqid('', true) . '@e.test']);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company_name' => 'r',
            'status' => 'active',
        ]);

        foreach ($slugs as $slug) {
            ResellerAllowedModule::create(['reseller_id' => $reseller->id, 'module_slug' => $slug]);
        }

        return $reseller;
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }

    /**
     * Capture the SQL issued by a callback, with the settings memo cleared
     * first so two measurements in one test start from the same cold state.
     *
     * @return array<int, string>
     */
    private function queriesDuring(callable $callback): array
    {
        ResellerSetting::clearRuntimeCache();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $log = DB::getQueryLog();

        DB::disableQueryLog();

        return array_map(static fn(array $entry): string => (string)$entry['query'], $log);
    }

    /**
     * @param array<int, string> $queries
     */
    private function countWrites(array $queries): int
    {
        return count(array_filter(
            $queries,
            static fn(string $sql): bool => (bool)preg_match('/^\s*(insert|update|delete)\b/i', $sql)
        ));
    }

    private function invoiceWithLines(int $lineCount, array $overrides = []): ResellerInvoice
    {
        $invoice = ResellerInvoice::create(array_merge([
            'reseller_id' => 1,
            'invoice_number' => 'INV-' . uniqid('', true),
            'type' => 'official',
            'status' => 'unpaid',
            'subtotal' => 0,
            'total' => 0,
            'paid_amount' => 0,
        ], $overrides));

        for ($i = 0; $i < $lineCount; $i++) {
            ResellerInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'custom',
                'title' => 'line ' . $i,
                'quantity' => 1,
                'unit_price' => 100_000,
                'total_price' => 100_000,
            ]);
        }

        return ResellerInvoice::findOrFail($invoice->id);
    }

    /**
     * `canSellModule()` used to run a fresh query per call, which turned the
     * tenant-create screen into ~50 queries. It is memoized per instance now.
     */
    public function test_can_sell_module_is_memoized_per_instance(): void
    {
        $reseller = $this->resellerWithModules(['crm', 'accounting']);

        $queries = $this->countQueries(function () use ($reseller): void {
            for ($i = 0; $i < 20; $i++) {
                $reseller->canSellModule('crm');
            }
        });

        $this->assertLessThanOrEqual(
            1,
            $queries,
            "20 calls must resolve from memory, not issue {$queries} queries."
        );
    }

    public function test_can_sell_module_still_answers_correctly_after_memoization(): void
    {
        $reseller = $this->resellerWithModules(['crm']);

        $this->assertTrue($reseller->canSellModule('crm'));
        $this->assertTrue($reseller->canSellModule('CRM'), 'Matching is case-insensitive.');
        $this->assertFalse($reseller->canSellModule('accounting'));

        $reseller->forgetAllowedModulesCache();
        $this->assertFalse($reseller->canSellModule('accounting'));
    }

    public function test_a_reseller_with_no_restrictions_may_sell_everything(): void
    {
        $reseller = $this->resellerWithModules([]);

        $this->assertTrue($reseller->canSellModule('anything'));
    }

    /**
     * Recalculating totals must not scale its query count with the number of
     * lines. Comparing two line counts is the honest assertion here: an
     * absolute constant would silently drift every time the rounding path
     * gains a lookup, while the *difference* is what "no N+1" actually means.
     */
    public function test_recalculate_totals_does_not_issue_a_query_per_line(): void
    {
        $small = $this->invoiceWithLines(3);
        $large = $this->invoiceWithLines(10);

        $smallQueries = $this->queriesDuring(fn() => $small->recalculateTotals());
        $largeQueries = $this->queriesDuring(fn() => $large->recalculateTotals());

        $this->assertSame(
            count($smallQueries),
            count($largeQueries),
            'Line count must not drive query count: 3 lines cost '
            . count($smallQueries) . ', 10 lines cost ' . count($largeQueries) . ".\n"
            . implode("\n", $largeQueries)
        );

        // 1 for the lines + 1 for the rounding settings. No per-line reads.
        $this->assertLessThanOrEqual(
            2,
            count($largeQueries),
            '10 lines must cost at most 2 queries, got ' . count($largeQueries) . ":\n"
            . implode("\n", $largeQueries)
        );

        $this->assertSame(1_000_000, (int)$large->subtotal);
        $this->assertSame(1_000_000, (int)$large->total);
    }

    /**
     * Per-line tax mode has to persist each line's own tax figure. That is a
     * legitimate write per line on the first pass — but a second pass with no
     * change must write nothing at all.
     */
    public function test_recalculate_totals_writes_only_the_changed_line_metadata(): void
    {
        $invoice = $this->invoiceWithLines(3, [
            'tax_percent' => 10,
            'meta' => ['tax_mode' => 'item'],
        ]);

        $firstPass = $this->queriesDuring(fn() => $invoice->recalculateTotals());

        $this->assertSame(
            3,
            $this->countWrites($firstPass),
            "First pass must write exactly the 3 line metas.\n" . implode("\n", $firstPass)
        );
        $this->assertSame(30_000, (int)$invoice->tax_amount);
        $this->assertSame(330_000, (int)$invoice->total);

        // Nothing changed, so nothing may be written.
        $secondPass = $this->queriesDuring(fn() => $invoice->recalculateTotals());

        $this->assertSame(
            0,
            $this->countWrites($secondPass),
            "A no-op recalculate must issue no writes.\n" . implode("\n", $secondPass)
        );

        // But it is not inert: change one line and exactly that line is written.
        $items = $invoice->items()->orderBy('id')->get();
        $items[1]->total_price = 50_000;
        $items[1]->saveQuietly();
        $items[1]->meta = null;
        $items[1]->saveQuietly();

        $invoice->unsetRelation('items');
        $invoice->refresh();

        $thirdPass = $this->queriesDuring(fn() => $invoice->recalculateTotals());

        $this->assertSame(
            1,
            $this->countWrites($thirdPass),
            "Only the drifted line may be rewritten.\n" . implode("\n", $thirdPass)
        );
    }
}
