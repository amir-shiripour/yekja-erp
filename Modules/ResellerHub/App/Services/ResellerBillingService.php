<?php

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Actions\Invoices\CreateInvoiceAction;
use Modules\ResellerHub\App\Actions\Invoices\UpdateInvoiceAction;
use Modules\ResellerHub\App\Actions\Invoices\ApplyTaxModeAction;
use Modules\ResellerHub\App\Actions\Invoices\MarkInvoicePaidAction;
use Modules\ResellerHub\App\Actions\Orders\RenewOrderAction;
use Modules\ResellerHub\App\DTOs\CreateInvoiceDTO;
use Modules\ResellerHub\App\DTOs\PaymentRecordDTO;
use Modules\ResellerHub\App\DTOs\RenewOrderDTO;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Billing\OrderProvisioningService;
use Modules\ResellerHub\App\Services\Billing\RenewalInvoiceService;
use Modules\ResellerHub\App\Services\Billing\UpgradeInvoiceService;

class ResellerBillingService
{
    public function __construct(
        protected CreateInvoiceAction      $createInvoiceAction,
        protected UpdateInvoiceAction      $updateInvoiceAction,
        protected ApplyTaxModeAction       $applyTaxModeAction,
        protected MarkInvoicePaidAction    $markInvoicePaidAction,
        protected RenewOrderAction         $renewOrderAction,
        protected RenewalInvoiceService    $renewalInvoiceService,
        protected UpgradeInvoiceService    $upgradeInvoiceService,
        protected OrderProvisioningService $orderProvisioningService,
    )
    {
    }

    /**
     * Create a new invoice with line items and calculated totals.
     */
    public function createInvoice(Reseller $reseller, CreateInvoiceDTO|array $data, ?User $creator = null): ResellerInvoice
    {
        return $this->createInvoiceAction->execute($reseller, $data, $creator);
    }

    /**
     * Update an existing invoice and its items.
     */
    public function updateInvoice(ResellerInvoice $invoice, array $data): ResellerInvoice
    {
        return $this->updateInvoiceAction->execute($invoice, $data);
    }

    /**
     * Mark an invoice as paid and automatically generate/update order.
     */
    public function markAsPaid(ResellerInvoice $invoice, PaymentRecordDTO|array $paymentData = []): ResellerOrder
    {
        return $this->markInvoicePaidAction->execute($invoice, $paymentData);
    }

    /**
     * Convert an invoice into a ResellerOrder and provision tenant modules.
     */
    public function convertInvoiceToOrder(ResellerInvoice $invoice, array $orderData = []): ResellerOrder
    {
        return $this->orderProvisioningService->convertInvoiceToOrder($invoice, $orderData);
    }

    /**
     * Synchronize orders and tenant status based on invoice payment state.
     */
    public function syncOrdersForInvoice(ResellerInvoice $invoice): void
    {
        $this->orderProvisioningService->syncOrdersForInvoice($invoice);
    }

    /**
     * Activate the ordered modules on the specified tenant.
     */
    public function provisionOrderToTenant(ResellerOrder $order): bool
    {
        return $this->orderProvisioningService->provisionOrderToTenant($order);
    }

    /**
     * Create a renewal invoice from an existing invoice/order.
     */
    public function createRenewalInvoice(ResellerInvoice|ResellerOrder $source, RenewOrderDTO|array $options = []): ResellerInvoice
    {
        return $this->renewOrderAction->execute($source, $options);
    }

    /**
     * Create a renewal invoice from an existing order.
     */
    public function createRenewalInvoiceFromOrder(ResellerOrder $order, array $options = []): ResellerInvoice
    {
        return $this->renewalInvoiceService->createRenewalInvoiceFromOrder($order, $options);
    }

    /**
     * Validate whether a tenant can perform an advance renewal based on reseller settings.
     *
     * @return array{allowed: bool, max_cycles: int, current_expires_at: ?Carbon, projected_expires_at: ?Carbon, max_allowed_date: ?Carbon, message: ?string}
     */
    public function validateAdvanceRenewalLimit(
        Tenant $tenant,
        int    $renewalMonths = 12,
        ?int   $resellerId = null,
        ?int   $excludeInvoiceId = null
    ): array
    {
        return $this->renewalInvoiceService->validateAdvanceRenewalLimit($tenant, $renewalMonths, $resellerId, $excludeInvoiceId);
    }

    /**
     * Assert that advance renewal is allowed, or throw an exception.
     *
     * @throws ResellerHubException
     */
    public function assertCanRenewAdvance(
        Tenant $tenant,
        int    $renewalMonths = 12,
        ?int   $resellerId = null,
        ?int   $excludeInvoiceId = null
    ): void
    {
        $this->renewalInvoiceService->assertCanRenewAdvance($tenant, $renewalMonths, $resellerId, $excludeInvoiceId);
    }

    /**
     * Create an upgrade invoice from an existing order to a new package.
     */
    public function createUpgradeInvoiceFromOrder(ResellerOrder $order, int $newPackageId, array $options = []): ResellerInvoice
    {
        return $this->upgradeInvoiceService->createUpgradeInvoiceFromOrder($order, $newPackageId, $options);
    }

    /**
     * Upgrade an order package directly without requiring an unpaid invoice payment.
     */
    public function upgradeOrderPackageWithoutInvoice(ResellerOrder $order, int $newPackageId, array $options = []): ResellerOrder
    {
        return $this->upgradeInvoiceService->upgradeOrderPackageWithoutInvoice($order, $newPackageId, $options);
    }

    /**
     * Parse Jalali date to Gregorian Carbon instance.
     */
    public function parseJalaliToGregorian(mixed $dateStr): ?Carbon
    {
        return JalaliDateHelper::parseToGregorian($dateStr);
    }

    /**
     * Apply tax mode and recalculate taxes on invoice.
     */
    public function applyTaxModeToInvoice(ResellerInvoice $invoice, string $taxMode, float $taxRate, ?string $source = null): ResellerInvoice
    {
        return $this->applyTaxModeAction->execute($invoice, $taxMode, $taxRate, $source);
    }

    /**
     * Add modules or features to an existing order.
     */
    public function addItemsToOrder(
        ResellerOrder $order,
        array         $items,
        Reseller      $reseller,
        ?string       $notes = null,
        bool          $issueInvoice = true,
        ?int          $actorId = null
    ): array
    {
        return $this->orderProvisioningService->addItemsToOrder($order, $items, $reseller, $notes, $issueInvoice, $actorId);
    }

    /**
     * Remove modules or features from an existing order.
     */
    public function removeItemsFromOrder(ResellerOrder $order, array $itemsList, Reseller $reseller): array
    {
        return $this->orderProvisioningService->removeItemsFromOrder($order, $itemsList, $reseller);
    }
}
