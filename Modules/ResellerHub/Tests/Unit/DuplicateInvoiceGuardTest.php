<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Modules\ResellerHub\App\Actions\Invoices\CreateInvoiceAction;
use Modules\ResellerHub\App\DTOs\CreateInvoiceDTO;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\Reseller;
use ReflectionMethod;
use Modules\ResellerHub\Tests\TestCase;
use Modules\ResellerHub\Tests\Unit\Concerns\UsesIsolatedDatabase;

/**
 * P1-5 regression: `CreateInvoiceAction` must refuse to create a second
 * unsettled invoice for the same tenant + package (double-submit guard),
 * while still allowing renewals (parent_invoice_id), settled invoices and
 * explicit `allow_duplicate_invoice` bypasses.
 */
class DuplicateInvoiceGuardTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootIsolatedDatabase();
        $this->createInvoicesTable();
        $this->createResellersTable();
    }

    protected function tearDown(): void
    {
        $this->shutdownIsolatedDatabase();
        parent::tearDown();
    }

    private function guard(CreateInvoiceDTO $dto): void
    {
        $action = app(CreateInvoiceAction::class);
        $reseller = new Reseller();
        $reseller->id = 1;

        $method = new ReflectionMethod($action, 'assertNoDuplicatePendingInvoice');
        $method->invoke($action, $reseller, $dto);
    }

    private function dto(array $meta = []): CreateInvoiceDTO
    {
        return CreateInvoiceDTO::fromArray([
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-TEST-0001',
            'meta' => $meta,
        ]);
    }

    public function test_blocks_second_pending_invoice_for_same_tenant_and_package(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0001',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $this->expectException(ResellerHubException::class);
        $this->guard($this->dto());
    }

    public function test_blocks_when_existing_invoice_is_overdue(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0002',
            'status' => ResellerInvoice::STATUS_OVERDUE,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $this->expectException(ResellerHubException::class);
        $this->guard($this->dto());
    }

    public function test_allows_when_existing_invoice_is_paid(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0003',
            'status' => ResellerInvoice::STATUS_PAID,
            'total' => 100000,
            'paid_amount' => 100000,
        ]);

        $this->expectNotToPerformAssertions();
        $this->guard($this->dto());
    }

    public function test_allows_when_existing_invoice_is_cancelled(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0004',
            'status' => ResellerInvoice::STATUS_CANCELLED,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $this->expectNotToPerformAssertions();
        $this->guard($this->dto());
    }

    public function test_allows_renewal_children_with_same_tenant_and_package(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'parent_invoice_id' => 55,
            'invoice_number' => 'INV-EXIST-0005',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $this->expectNotToPerformAssertions();
        $this->guard($this->dto());
    }

    public function test_meta_bypass_allows_duplicate(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0006',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $this->expectNotToPerformAssertions();
        $this->guard($this->dto(['allow_duplicate_invoice' => true]));
    }

    public function test_skips_guard_without_tenant_or_package(): void
    {
        ResellerInvoice::query()->create([
            'reseller_id' => 1,
            'tenant_id' => 112,
            'package_id' => 7,
            'invoice_number' => 'INV-EXIST-0007',
            'status' => ResellerInvoice::STATUS_UNPAID,
            'total' => 100000,
            'paid_amount' => 0,
        ]);

        $dto = CreateInvoiceDTO::fromArray(['invoice_number' => 'INV-TEST-0002']);
        $this->expectNotToPerformAssertions();
        $this->guard($dto);
    }
}
