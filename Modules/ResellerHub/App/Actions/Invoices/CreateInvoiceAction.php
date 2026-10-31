<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Actions\Invoices;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\DTOs\CreateInvoiceDTO;
use Modules\ResellerHub\App\DTOs\InvoiceItemDTO;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Events\InvoiceCreated;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\BillingCalculator;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Throwable;

class CreateInvoiceAction
{
    /**
     * Execute invoice creation with typed DTO / array compatibility, line items, and deadlock retry.
     * @param Reseller $reseller
     * @param CreateInvoiceDTO|array $data
     * @param User|null $creator
     * @return ResellerInvoice
     * @throws ResellerHubException|Exception|Throwable
     */
    public function execute(Reseller $reseller, CreateInvoiceDTO|array $data, ?User $creator = null): ResellerInvoice
    {
        $dto = $data instanceof CreateInvoiceDTO ? $data : CreateInvoiceDTO::fromArray($data);

        /** @var ResellerInvoice $invoice */
        $invoice = DB::transaction(static function () use ($reseller, $dto, $creator): ResellerInvoice {
            $invoiceNumber = !empty($dto->invoiceNumber)
                ? trim($dto->invoiceNumber)
                : ResellerInvoice::generateNextNumber(
                    $reseller->id,
                    $dto->type instanceof InvoiceType ? $dto->type->value : (string)$dto->type
                );

            $issueDate = self::parseJalaliToGregorian($dto->issueDate) ?? Carbon::now();
            $dueDate = self::parseJalaliToGregorian($dto->dueDate) ?? (clone $issueDate)->addDays(7);
            $subAccount = null;
            if ($dto->subAccountId) {
                /** @var ResellerSubAccount|null $subAccount */
                $subAccount = ResellerSubAccount::where('reseller_id', $reseller->id)
                    ->find($dto->subAccountId);

                if (!$subAccount) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.sub_account_foreign'));
                }
            }

            $tenant = null;
            if ($dto->tenantId) {
                /** @var Tenant|null $tenant */
                $tenant = Tenant::with(['subAccount.user', 'users'])
                    ->where('reseller_id', $reseller->id)
                    ->find($dto->tenantId);

                if (!$tenant) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.tenant_foreign'));
                }

                if (!$subAccount) {
                    $subAccount = $tenant->subAccount;
                }
            }

            $isPackagePurchase = !empty($dto->packageId)
                || !empty($dto->meta['package_id'])
                || !empty($dto->meta['to_package_id'])
                || ($dto->meta['source'] ?? '') === 'dashboard_onboarding_selection'
                || collect($dto->items)->contains(
                    static fn($item) => ($item instanceof InvoiceItemDTO ? $item->itemType : ($item['item_type'] ?? '')) === 'package'
                );

            if (!$isPackagePurchase && $subAccount && !is_null($subAccount->max_invoices) && (int)$subAccount->max_invoices > 0) {
                if ($subAccount->invoices()->count() >= (int)$subAccount->max_invoices) {
                    throw new ResellerHubException(__('resellerhub::messages.errors.sub_account_invoice_limit', [
                        'name' => $subAccount->name,
                        'max' => ResellerHelper::toPersianDigits((int)$subAccount->max_invoices),
                    ]));
                }
            }

            $tenantAdminUser = $subAccount?->user ?? $tenant?->users->first();
            $subAccountName = trim($subAccount?->name ?? ($tenant?->name ?? ($dto->subAccountName ?? 'زیرمجموعه')));
            $subAccountMobile = $subAccount?->mobile ?? ($tenantAdminUser?->mobile ?? $dto->subAccountMobile);
            $subAccountEmail = $subAccount?->email ?? ($tenantAdminUser?->email ?? $dto->subAccountEmail);

            $meta = $dto->meta;
            if ($subAccount) {
                $meta['sub_account_id'] = $subAccount->id;
            }

            if ($dto->packageId && !ResellerPackage::where('reseller_id', $reseller->id)->whereKey($dto->packageId)->exists()) {
                throw new ResellerHubException(__('resellerhub::messages.errors.package_foreign'));
            }

            self::assertNoDuplicatePendingInvoice($reseller, $dto);

            /** @var ResellerInvoice $createdInvoice */
            $createdInvoice = ResellerInvoice::create([
                'reseller_id' => $reseller->id,
                'tenant_id' => $dto->tenantId,
                'package_id' => $dto->packageId,
                'invoice_number' => $invoiceNumber,
                'type' => $dto->type,
                'status' => $dto->status,
                'sub_account_name' => $subAccountName,
                'sub_account_mobile' => $subAccountMobile,
                'sub_account_email' => $subAccountEmail,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'subtotal' => 0,
                'discount_type' => $dto->discountType,
                'discount_value' => $dto->discountValue,
                'discount_amount' => 0,
                'tax_percent' => $dto->taxPercent,
                'tax_amount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'payment_method' => $dto->paymentMethod,
                'transaction_ref' => $dto->transactionRef,
                'notes' => $dto->notes,
                'terms' => $dto->terms,
                'meta' => $meta,
                'created_by' => $creator?->id ?? auth()->id(),
            ]);

            // Add line items
            foreach ($dto->items as $item) {

                $itemDTO = $item instanceof InvoiceItemDTO ? $item : InvoiceItemDTO::fromArray((array)$item);
                $qty = max(1, $itemDTO->quantity);
                $unitPrice = max(0, $itemDTO->unitPrice);
                $itemDiscount = max(0, $itemDTO->discountAmount);
                $lineTotal = BillingCalculator::calculateLineTotal($qty, $unitPrice, $itemDiscount);

                ResellerInvoiceItem::create([
                    'invoice_id' => $createdInvoice->id,
                    'item_type' => $itemDTO->itemType,
                    'item_key' => $itemDTO->itemKey,
                    'title' => $itemDTO->title,
                    'description' => $itemDTO->description,
                    'duration_months' => $itemDTO->durationMonths,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'total_price' => $lineTotal,
                    'meta' => $itemDTO->meta,
                ]);
            }

            // Recalculate totals
            $createdInvoice->load('items');
            $createdInvoice->recalculateTotals()->save();

            // Automatically create order from invoice
            if ($createdInvoice->status === ResellerInvoice::STATUS_PAID && !$createdInvoice->paid_at) {
                $createdInvoice->paid_at = Carbon::now();
                $createdInvoice->paid_amount = $createdInvoice->total;
                $createdInvoice->save();
            }

            app(ResellerBillingService::class)->convertInvoiceToOrder($createdInvoice);

            return $createdInvoice;
        }, 3);

        event(new InvoiceCreated($invoice));

        return $invoice;
    }

    /**
     * Parse Jalali date to Gregorian Carbon instance.
     */
    protected static function parseJalaliToGregorian(?string $jalaliDate): ?Carbon
    {
        return JalaliDateHelper::parseToGregorian($jalaliDate);
    }

    protected static function assertNoDuplicatePendingInvoice(Reseller $reseller, CreateInvoiceDTO $dto): void
    {
        if (empty($dto->tenantId) || empty($dto->packageId)) {
            return;
        }

        if (filter_var($dto->meta['allow_duplicate_invoice'] ?? false, FILTER_VALIDATE_BOOL)) {
            return;
        }

        $duplicateExists = ResellerInvoice::query()
            ->where('reseller_id', $reseller->id)
            ->where('tenant_id', $dto->tenantId)
            ->where('package_id', $dto->packageId)
            ->whereNull('parent_invoice_id')
            ->whereIn('status', [ResellerInvoice::STATUS_UNPAID, ResellerInvoice::STATUS_OVERDUE])
            ->exists();

        if ($duplicateExists) {
            throw new ResellerHubException(__('resellerhub::messages.errors.duplicate_pending_invoice'));
        }
    }
}
