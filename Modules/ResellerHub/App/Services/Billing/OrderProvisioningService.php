<?php

namespace Modules\ResellerHub\App\Services\Billing;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\ActivityLogger;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Events\OrderCreated;
use Modules\ResellerHub\App\Events\OrderProvisioned;
use Modules\ResellerHub\App\Events\OrderRenewed;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoiceItem;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\Tenant\TenantStatusSyncService;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Throwable;

class OrderProvisioningService
{
    /**
     * Activate the ordered modules on the specified tenant.
     */
    public function provisionOrderToTenant(ResellerOrder $order): bool
    {
        if (!$order->tenant_id) {
            return false;
        }

        // Only provision modules and activate tenant if the order is active
        if (!$order->isActive()) {
            return false;
        }

        return DB::transaction(function () use ($order) {
            $tenant = Tenant::find($order->tenant_id);
            if (!$tenant) {
                $order->provisioning_status = ResellerOrder::PROVISION_FAILED;
                $order->save();
                return false;
            }

            // Sync package_id, limits, and features if associated with a package
            $pkgId = $order->invoice?->package_id ?? $tenant->package_id;
            if ($pkgId) {
                $isPackageChange = ((int)$tenant->package_id !== (int)$pkgId);
                $tenant->package_id = $pkgId;
                $pkg = ResellerPackage::find($pkgId);
                if ($pkg) {
                    $currentSettings = is_array($tenant->settings) ? $tenant->settings : [];
                    if (!empty($pkg->limits)) {
                        // TEN-06: On package upgrade/change, apply the new package limits directly
                        if ($isPackageChange || empty($currentSettings['limits'])) {
                            $currentSettings['limits'] = (array)$pkg->limits;
                        } else {
                            $currentSettings['limits'] = $this->mergeLimitsTightest(
                                (array)$pkg->limits,
                                (array)($currentSettings['limits'] ?? [])
                            );
                        }
                    }
                    if (!empty($pkg->features) && (empty($currentSettings['features']) || $isPackageChange)) {
                        $flatPkg = [];
                        foreach ((array)$pkg->features as $pf) {
                            if (is_array($pf)) {
                                foreach ($pf as $subPf) {
                                    if (is_string($subPf) && $subPf !== '') $flatPkg[] = $subPf;
                                }
                            } elseif (is_string($pf) && $pf !== '') {
                                $flatPkg[] = $pf;
                            }
                        }
                        $currentSettings['features'] = array_values(array_unique($flatPkg));
                    }
                    $tenant->settings = $currentSettings;

                    if ($tenant->subAccount) {
                        $tenant->subAccount->update(['limits' => $currentSettings['limits'] ?? []]);
                    }
                }
            }

            // Extend tenant plan if new end date is greater
            if ($order->end_date) {
                if (!$tenant->plan_expires_at || Carbon::parse($order->end_date)->greaterThan($tenant->plan_expires_at)) {
                    $tenant->plan_expires_at = $order->end_date;
                    // TEN-08: Do not un-suspend a tenant who was administratively suspended for violations
                    if ($tenant->status !== Tenant::STATUS_SUSPENDED) {
                        $tenant->status = Tenant::STATUS_ACTIVE;
                    }
                }
            }
            $tenant->save();

            // Activate modules for tenant
            $snapshot = $order->modules_snapshot ?? [];
            foreach ($snapshot as $item) {
                if (($item['type'] ?? '') === 'module' && !empty($item['slug'])) {
                    $slug = strtolower(trim($item['slug']));
                    $expiresAt = $order->end_date;

                    TenantModule::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'module_slug' => $slug,
                        ],
                        [
                            'is_active' => true,
                            'activated_at' => Carbon::now(),
                            'expires_at' => $expiresAt,
                        ]
                    );
                }
            }

            $order->provisioning_status = ResellerOrder::PROVISION_DONE;
            $order->save();

            return true;
        }, 3);
    }

    /**
     * @param array<string, int|string|null> $packageLimits
     * @param array<string, int|string|null> $existingLimits
     * @return array<string, int|string|null>
     */
    protected function mergeLimitsTightest(array $packageLimits, array $existingLimits): array
    {
        $merged = $packageLimits;

        foreach ($existingLimits as $key => $value) {
            if (!array_key_exists($key, $merged)) {
                continue;
            }

            $packageValue = $merged[$key];

            if ($value === null || $value === '') {
                continue;
            }

            if ($packageValue === null || $packageValue === '') {
                $merged[$key] = $value;
                continue;
            }

            if (is_numeric($packageValue) && is_numeric($value)) {
                $merged[$key] = min((int)$packageValue, (int)$value);
            }
        }

        return $merged;
    }

    /**
     * Convert an invoice into a ResellerOrder and provision tenant modules.
     */
    public function convertInvoiceToOrder(ResellerInvoice $invoice, array $orderData = []): ResellerOrder
    {
        return DB::transaction(function () use ($invoice, $orderData) {
            // Re-read under a row lock: this method is reachable from several
            // entry points (payment callback, mark-as-paid, status update) and
            // must be safe to replay.
            $invoice = ResellerInvoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $isRenewal = $invoice->parent_invoice_id || !empty($invoice->meta['is_renewal']) || !empty($invoice->meta['renewal_of_order_id']);
            if ($isRenewal) {
                $renewalOrder = null;
                if (!empty($invoice->meta['renewal_of_order_id'])) {
                    $renewalOrder = ResellerOrder::with('tenant')->find($invoice->meta['renewal_of_order_id']);
                }
                if (!$renewalOrder && !empty($invoice->meta['source_order_id']) && !empty($invoice->meta['is_renewal'])) {
                    $renewalOrder = ResellerOrder::with('tenant')->find($invoice->meta['source_order_id']);
                }
                if (!$renewalOrder && $invoice->parent_invoice_id) {
                    $renewalOrder = ResellerOrder::with('tenant')->where('invoice_id', $invoice->parent_invoice_id)->first();
                }
                if (!$renewalOrder && $invoice->tenant_id) {
                    $baseQuery = ResellerOrder::with('tenant')
                        ->where('tenant_id', $invoice->tenant_id)
                        ->where('reseller_id', $invoice->reseller_id);

                    if ($invoice->package_id) {
                        $renewalOrder = (clone $baseQuery)->where('package_id', $invoice->package_id)->orderBy('id', 'desc')->first();
                    }

                    if (!$renewalOrder) {
                        $renewalOrder = (clone $baseQuery)
                            ->whereNotIn('billing_cycle', ['addon', 'custom_addition'])
                            ->orderBy('id', 'desc')
                            ->first();
                    }
                }

                if ($renewalOrder) {
                    if (!$invoice->source_order_id) {
                        $invoice->source_order_id = $renewalOrder->id;
                        $invoice->save();
                    }

                    if (!$invoice->isPaid()) {
                        return $renewalOrder;
                    }

                    // Idempotency gate: a paid renewal/upgrade invoice extends
                    // its order exactly once. Any replay (double callback, a
                    // "mark as paid" click after an online payment, a manual
                    // status change) returns the order untouched.
                    if ($invoice->renewal_applied_at !== null) {
                        Log::info('[OrderProvisioningService] Renewal already applied, skipping replay.', [
                            'invoice_id' => $invoice->id,
                            'order_id' => $renewalOrder->id,
                            'applied_at' => $invoice->renewal_applied_at?->toIso8601String(),
                        ]);

                        return $renewalOrder;
                    }

                    if (!empty($invoice->meta['is_upgrade'])) {
                        $toPackageId = (int)($invoice->meta['to_package_id'] ?? $invoice->package_id);
                        $newPackage = $toPackageId ? ResellerPackage::find($toPackageId) : null;

                        if ($newPackage && $renewalOrder->tenant) {
                            $renewalOrder->tenant->update(['package_id' => $newPackage->id]);
                        }

                        // Merge new package modules into modules_snapshot
                        if ($newPackage && is_array($newPackage->modules)) {
                            $currentSnapshot = $renewalOrder->modules_snapshot ?? [];
                            $existingSlugs = collect($currentSnapshot)->pluck('slug')->toArray();
                            $cycle = $renewalOrder->billing_cycle ?? 'yearly';
                            $cycleMonths = ResellerOrder::cycleToMonths($cycle);
                            foreach ($newPackage->modules as $mSlug) {
                                if (!in_array($mSlug, $existingSlugs)) {
                                    $currentSnapshot[] = [
                                        'type' => 'module',
                                        'slug' => $mSlug,
                                        'title' => ResellerHelper::getModuleTitle($mSlug),
                                        'duration_months' => $cycleMonths,
                                    ];
                                }
                            }
                            $renewalOrder->modules_snapshot = $currentSnapshot;
                            $renewalOrder->renewal_price = $newPackage->getPriceForMonths($cycleMonths);
                        }

                        $renewalOrder->status = ResellerOrder::STATUS_ACTIVE;
                        $renewalOrder->total_amount = (int)$renewalOrder->total_amount + (int)$invoice->total;
                        $renewalOrder->contract_value = (int)$invoice->total;
                        $renewalOrder->save();

                        $this->provisionOrderToTenant($renewalOrder);
                        $this->markRenewalApplied($invoice);
                        event(new OrderProvisioned($renewalOrder));
                        return $renewalOrder;
                    }

                    $durationMonths = (int)($invoice->meta['duration_months'] ?? 12);
                    if ($durationMonths <= 0) {
                        $durationMonths = 12;
                    }

                    $durationDays = (int)($invoice->meta['duration_days'] ?? 0);

                    $baseStart = ($renewalOrder->end_date && Carbon::parse($renewalOrder->end_date)->isFuture())
                        ? Carbon::parse($renewalOrder->end_date)
                        : Carbon::now();

                    $newEndDate = ($durationDays > 0)
                        ? (clone $baseStart)->addDays($durationDays)
                        : (clone $baseStart)->addMonths($durationMonths);

                    $renewalOrder->update([
                        'status' => ResellerOrder::STATUS_ACTIVE,
                        'end_date' => $newEndDate,
                        'renewal_date' => $newEndDate,
                        // total_amount accumulates lifetime revenue; contract_value tracks
                        // the value of the term that is currently in force.
                        'total_amount' => (int)$renewalOrder->total_amount + (int)$invoice->total,
                        'contract_value' => (int)$invoice->total,
                        'renewal_price' => (int)$invoice->total,
                    ]);

                    $this->provisionOrderToTenant($renewalOrder);
                    $this->markRenewalApplied($invoice);
                    event(new OrderRenewed($renewalOrder));
                    return $renewalOrder;
                }
            }

            // 2. If an order already exists for this invoice
            if ($invoice->order) {
                $existingOrder = $invoice->order;
                if ($invoice->isPaid() && $existingOrder->status !== ResellerOrder::STATUS_ACTIVE) {
                    $existingOrder->update(['status' => ResellerOrder::STATUS_ACTIVE]);
                    if ($invoice->tenant_id) {
                        $this->provisionOrderToTenant($existingOrder);
                    }
                }
                return $existingOrder;
            }

            // Collect modules snapshot from invoice items
            $modulesSnapshot = [];
            $maxDuration = 0;

            $catalogService = app(ModuleCapabilityCatalogService::class);
            $fullCatalog = $catalogService->getFullCatalog($invoice->reseller);
            $moduleTitlesMap = $fullCatalog->pluck('title', 'slug')->toArray();

            foreach ($invoice->items as $item) {
                if ($item->item_type === 'module' && !empty($item->item_key)) {
                    $mTitle = $item->title;
                    if (empty($mTitle) || $mTitle === $item->item_key) {
                        $mTitle = $moduleTitlesMap[$item->item_key] ?? ($moduleTitlesMap[strtolower($item->item_key)] ?? $item->item_key);
                    }
                    $modulesSnapshot[] = [
                        'type' => 'module',
                        'slug' => $item->item_key,
                        'title' => $mTitle,
                        'duration_months' => $item->duration_months,
                    ];
                    $maxDuration = max($maxDuration, $item->duration_months);
                } elseif (in_array($item->item_type, ['package', 'package_upgrade'])) {
                    $effectivePkg = $invoice->package ?: ($invoice->tenant?->package ?: ($invoice->package_id ? ResellerPackage::find($invoice->package_id) : null));
                    $pkgModules = $effectivePkg?->modules ?? ($invoice->tenant?->modules ?? []);
                    if (!empty($pkgModules) && is_array($pkgModules)) {
                        foreach ($pkgModules as $mSlug) {
                            $mTitle = $moduleTitlesMap[$mSlug] ?? ($moduleTitlesMap[strtolower($mSlug)] ?? $mSlug);
                            $modulesSnapshot[] = [
                                'type' => 'module',
                                'slug' => $mSlug,
                                'title' => $mTitle,
                                'duration_months' => $item->duration_months,
                            ];
                        }
                    }
                    $maxDuration = max($maxDuration, $item->duration_months);
                } elseif ($item->item_type === 'feature' && !empty($item->item_key)) {
                    $modulesSnapshot[] = [
                        'type' => 'feature',
                        'key' => $item->item_key,
                        'title' => $item->title,
                        'duration_months' => $item->duration_months,
                    ];
                }
            }

            // Fallback: If snapshot is still empty, populate from package or tenant modules
            if (empty($modulesSnapshot)) {
                $effectivePkg = $invoice->package ?: ($invoice->tenant?->package ?: ($invoice->package_id ? ResellerPackage::find($invoice->package_id) : null));
                $fallbackModules = $effectivePkg?->modules ?? ($invoice->tenant?->modules ?? []);
                if (!empty($fallbackModules) && is_array($fallbackModules)) {
                    foreach ($fallbackModules as $mSlug) {
                        $mTitle = $moduleTitlesMap[$mSlug] ?? ($moduleTitlesMap[strtolower($mSlug)] ?? $mSlug);
                        $modulesSnapshot[] = [
                            'type' => 'module',
                            'slug' => $mSlug,
                            'title' => $mTitle,
                            'duration_months' => $invoice->duration_months ?: 12,
                        ];
                    }
                }
            }

            if ($maxDuration <= 0) {
                $maxDuration = (int)($invoice->meta['duration_months'] ?? 0);
            }
            if ($maxDuration <= 0) {
                $maxDuration = 12;
            }

            $startDate = Carbon::now();
            $endDate = (clone $startDate)->addMonths($maxDuration);

            $billingCycle = match (true) {
                $maxDuration <= 1 => 'monthly',
                $maxDuration <= 3 => 'quarterly',
                $maxDuration <= 6 => 'semi_annual',
                default => 'yearly',
            };

            $isPaid = $invoice->isPaid();

            $order = ResellerOrder::create([
                'reseller_id' => $invoice->reseller_id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'order_number' => ResellerOrder::generateNextNumber($invoice->reseller_id),
                'sub_account_name' => $invoice->sub_account_name,
                'sub_account_mobile' => $invoice->sub_account_mobile,
                'sub_account_email' => $invoice->sub_account_email,
                'status' => $isPaid ? ResellerOrder::STATUS_ACTIVE : ResellerOrder::STATUS_PENDING,
                'billing_cycle' => $billingCycle,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'renewal_date' => $endDate,
                'total_amount' => $invoice->total,
                'contract_value' => $invoice->total,
                'renewal_price' => $invoice->total,
                'renewal_price_type' => 'auto',
                'modules_snapshot' => $modulesSnapshot,
                'provisioning_status' => ResellerOrder::PROVISION_NOT,
                'notes' => $orderData['notes'] ?? "سفارش صادر شده از فاکتور {$invoice->invoice_number}",
                'created_by' => $invoice->created_by ?? auth()->id(),
            ]);

            // If tenant is attached, provision modules only if invoice is paid
            if ($invoice->tenant_id) {
                if ($isPaid) {
                    $this->provisionOrderToTenant($order);
                } else {
                    $tenant = Tenant::find($invoice->tenant_id);
                    if ($tenant) {
                        foreach ($modulesSnapshot as $item) {
                            if (($item['type'] ?? '') === 'module' && !empty($item['slug'])) {
                                $slug = strtolower(trim($item['slug']));
                                $existingModule = TenantModule::where('tenant_id', $tenant->id)
                                    ->where('module_slug', $slug)
                                    ->first();

                                if (!$existingModule) {
                                    TenantModule::create([
                                        'tenant_id' => $tenant->id,
                                        'module_slug' => $slug,
                                        'is_active' => false,
                                        'expires_at' => $endDate,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            event(new OrderCreated($order));

            return $order;
        }, 3);
    }

    /**
     * Stamp the invoice so a replay of the same renewal can never extend the
     * subscription a second time.
     */
    private function markRenewalApplied(ResellerInvoice $invoice): void
    {
        $invoice->forceFill(['renewal_applied_at' => now()])->save();
    }

    /**
     * Synchronize orders and tenant status based on invoice payment state.
     */
    public function syncOrdersForInvoice(ResellerInvoice $invoice): void
    {
        $invoice->loadMissing(['order', 'tenant', 'parentInvoice.order']);

        if ($invoice->isCancelled()) {
            if ($invoice->order) {
                $invoice->order->update(['status' => ResellerOrder::STATUS_CANCELLED]);
            }
            return;
        }

        if ($invoice->isPaid()) {
            $isRenewal = $invoice->parent_invoice_id || !empty($invoice->meta['is_renewal']) || !empty($invoice->meta['source_order_id']);
            if ($isRenewal) {
                try {
                    $this->convertInvoiceToOrder($invoice);
                } catch (Throwable $e) {
                    Log::error('[OrderProvisioningService] Failed to extend order for renewal invoice in syncOrdersForInvoice: ' . $e->getMessage());
                }
                return;
            }

            if ($invoice->order) {
                if ($invoice->order->status !== ResellerOrder::STATUS_ACTIVE) {
                    $invoice->order->update(['status' => ResellerOrder::STATUS_ACTIVE]);
                }
                if ($invoice->tenant_id) {
                    $this->provisionOrderToTenant($invoice->order);
                }
            } else {
                try {
                    $this->convertInvoiceToOrder($invoice);
                } catch (Throwable $e) {
                    Log::error('[OrderProvisioningService] Failed to convert invoice to order upon full payment in syncOrdersForInvoice: ' . $e->getMessage());
                }
            }
            return;
        }

        if ($invoice->order) {
            if ($invoice->order->status === ResellerOrder::STATUS_ACTIVE) {
                $invoice->order->update(['status' => ResellerOrder::STATUS_PENDING]);
            } elseif ($invoice->order->status !== ResellerOrder::STATUS_INACTIVE && $invoice->order->status !== ResellerOrder::STATUS_PENDING) {
                $invoice->order->update(['status' => ResellerOrder::STATUS_PENDING]);
            }
            app(TenantStatusSyncService::class)->syncTenantStatus($invoice->order);
        }
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
        $billingCycle = $order->billing_cycle ?? 'yearly';
        $durationMonths = ResellerOrder::cycleToMonths($billingCycle);

        if (!$issueInvoice) {
            DB::transaction(function () use ($order, $items, $notes, $durationMonths) {
                $snapshot = $order->modules_snapshot ?? [];
                $existingSlugs = collect($snapshot)->pluck('slug')->map(fn($s) => Str::lower($s))->toArray();

                foreach ($items as $item) {
                    if (!in_array(Str::lower($item['slug']), $existingSlugs)) {
                        $snapshot[] = [
                            'type' => $item['type'],
                            'slug' => $item['slug'],
                            'title' => $item['title'],
                            'duration_months' => $durationMonths,
                            'is_custom_add' => true,
                        ];
                    }
                }

                $order->modules_snapshot = $snapshot;
                if (!empty($notes)) {
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') . "افزودن ماژول/قابلیت (بدون صدور فاکتور): " . $notes;
                }
                $order->save();

                if ($order->tenant) {
                    $order->tenant->update(['package_id' => null]);
                }

                if ($order->tenant_id) {
                    foreach ($items as $item) {
                        if ($item['type'] === 'module') {
                            TenantModule::updateOrCreate(
                                [
                                    'tenant_id' => $order->tenant_id,
                                    'module_slug' => Str::lower($item['slug']),
                                ],
                                [
                                    'is_active' => true,
                                    'installed_at' => now(),
                                    'expires_at' => $order->end_date ?? now()->addMonths($durationMonths),
                                ]
                            );
                        }
                    }
                }
            });

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log(
                    'order_items_added',
                    "افزودن " . ResellerHelper::toPersianDigits(count($items)) . " ماژول و قابلیت به سفارش " . ResellerHelper::toPersianDigits($order->order_number) . " (بدون صدور فاکتور)",
                    $order,
                    [
                        'items_count' => count($items),
                        'items' => $items,
                        'reseller_id' => $reseller->id,
                    ]
                );
            }

            return [
                'success' => true,
                'message' => "موارد انتخابی با موفقیت به سفارش افزوده شدند (بدون صدور فاکتور).",
                'invoice_id' => null,
                'redirect_url' => route('reseller.orders.show', $order->id),
            ];
        }

        $invoice = DB::transaction(function () use ($reseller, $order, $items, $notes, $durationMonths, $actorId) {
            $subtotal = collect($items)->sum('price');
            $initialStatus = $subtotal <= 0 ? ResellerInvoice::STATUS_PAID : ResellerInvoice::STATUS_UNPAID;

            $invoice = ResellerInvoice::create([
                'reseller_id' => $reseller->id,
                'tenant_id' => $order->tenant_id,
                'source_order_id' => $order->id,
                'source' => 'order_item_addition',
                'invoice_number' => ResellerInvoice::generateNextNumber($reseller->id),
                'type' => ResellerInvoice::TYPE_OFFICIAL,
                'status' => $initialStatus,
                'sub_account_name' => $order->sub_account_name,
                'sub_account_mobile' => $order->sub_account_mobile,
                'sub_account_email' => $order->sub_account_email,
                'issue_date' => now(),
                'due_date' => now()->addDays(7),
                'subtotal' => $subtotal,
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
                'paid_amount' => 0,
                'paid_at' => $subtotal <= 0 ? now() : null,
                'notes' => $notes ?: ("افزودن ماژول/فیچر به سفارش " . ResellerHelper::toPersianDigits($order->order_number)),
                'meta' => [
                    'is_item_addition' => true,
                    'is_renewal' => false,
                    'source_order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                'created_by' => $actorId,
            ]);

            foreach ($items as $item) {
                $itemTitle = trim($item['title'] ?? '') ?: ($item['type'] === 'feature' ? (PermissionCatalog::translate($item['slug'] ?? '') ?: $item['slug']) : ResellerHelper::getModuleTitle($item['slug'] ?? ''));
                ResellerInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['type'] === 'feature' ? 'feature' : 'module',
                    'item_key' => $item['slug'],
                    'title' => $itemTitle,
                    'description' => $itemTitle,
                    'duration_months' => $durationMonths,
                    'quantity' => 1,
                    'unit_price' => (int)$item['price'],
                    'discount_amount' => 0,
                    'total_price' => (int)$item['price'],
                    'meta' => ['source_order_id' => $order->id],
                ]);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            if ($invoice->total <= 0) {
                $invoice->status = ResellerInvoice::STATUS_PAID;
                $invoice->paid_at = now();
            }
            $invoice->save();

            $snapshot = $order->modules_snapshot ?? [];
            $existingSlugs = collect($snapshot)->pluck('slug')->map(fn($s) => Str::lower($s))->toArray();

            foreach ($items as $item) {
                if (!in_array(Str::lower($item['slug']), $existingSlugs)) {
                    $itemTitle = trim($item['title'] ?? '') ?: ($item['type'] === 'feature' ? (PermissionCatalog::translate($item['slug'] ?? '') ?: $item['slug']) : ResellerHelper::getModuleTitle($item['slug'] ?? ''));
                    $snapshot[] = [
                        'type' => $item['type'],
                        'slug' => $item['slug'],
                        'title' => $itemTitle,
                        'duration_months' => $durationMonths,
                        'is_custom_add' => true,
                    ];
                }
            }

            $order->modules_snapshot = $snapshot;
            $order->save();

            if ($order->tenant) {
                $order->tenant->update(['package_id' => null]);
            }

            if ($invoice->status === ResellerInvoice::STATUS_PAID && $order->tenant_id) {
                foreach ($items as $item) {
                    if ($item['type'] === 'module') {
                        TenantModule::updateOrCreate(
                            [
                                'tenant_id' => $order->tenant_id,
                                'module_slug' => Str::lower($item['slug']),
                            ],
                            [
                                'is_active' => true,
                                'installed_at' => now(),
                                'expires_at' => $order->end_date ?? now()->addMonths($durationMonths),
                            ]
                        );
                    }
                }
            }

            $order->is_custom_package = true;
            $order->save();

            return $invoice;
        });

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log(
                'order_items_added',
                "افزودن " . ResellerHelper::toPersianDigits(count($items)) . " ماژول و قابلیت به سفارش " . ResellerHelper::toPersianDigits($order->order_number) . " با فاکتور #" . ResellerHelper::toPersianDigits($invoice->invoice_number),
                $order,
                [
                    'items_count' => count($items),
                    'items' => $items,
                    'invoice_id' => $invoice->id,
                    'reseller_id' => $reseller->id,
                ]
            );
        }

        return [
            'success' => true,
            'message' => "فاکتور «" . ResellerHelper::toPersianDigits($invoice->invoice_number) . "» با موفقیت صادر شد.",
            'invoice_id' => $invoice->id,
            'redirect_url' => route('reseller.invoices.show', $invoice->id),
        ];
    }

    /**
     * Remove modules or features from an existing order.
     */
    public function removeItemsFromOrder(ResellerOrder $order, array $itemsList, Reseller $reseller): array
    {
        $catalog = app(ModuleCapabilityCatalogService::class)->getCatalogForReseller($reseller);
        $moduleTitlesMap = $catalog->pluck('title', 'slug')->toArray();

        $resolvedItems = [];
        $removedNames = [];

        foreach ($itemsList as $it) {
            $type = $it['type'];
            $slug = Str::lower(trim($it['slug']));
            $title = $it['title'] ?? null;

            if (empty($title) || $title === $slug) {
                if ($type === 'feature') {
                    $title = PermissionCatalog::translate($slug);
                    if ($title === $slug || empty($title)) {
                        foreach ($catalog as $cMod) {
                            $f = collect($cMod['features'] ?? [])->firstWhere('key', $slug);
                            if ($f && !empty($f['title'])) {
                                $title = $f['title'];
                                break;
                            }
                        }
                    }
                } else {
                    $title = $moduleTitlesMap[$slug] ?? ResellerHelper::getModuleTitle($slug);
                }
            }

            $resolvedItems[] = [
                'type' => $type,
                'slug' => $slug,
                'title' => $title,
            ];
            $removedNames[] = "«{$title}»";
        }

        DB::transaction(function () use ($order, $resolvedItems, $removedNames) {
            $snapshot = $order->modules_snapshot ?? [];
            $modulesToRemove = [];
            $featuresToRemove = [];

            foreach ($resolvedItems as $it) {
                $slug = Str::lower(trim($it['slug']));
                if ($it['type'] === 'module') {
                    $modulesToRemove[] = $slug;
                } else {
                    $featuresToRemove[] = $slug;
                }
            }

            $snapshot = collect($snapshot)->filter(function ($item) use ($modulesToRemove, $featuresToRemove) {
                if (is_string($item)) {
                    $itemSlug = Str::lower(trim($item));
                    return !in_array($itemSlug, $modulesToRemove, true) && !in_array($itemSlug, $featuresToRemove, true);
                }
                if (!is_array($item)) return true;

                $rawSlug = $item['slug'] ?? ($item['key'] ?? '');
                if (!is_string($rawSlug)) return true;
                $itemSlug = Str::lower(trim($rawSlug));
                $itemType = $item['type'] ?? '';

                if ($itemType === 'module') {
                    if (in_array($itemSlug, $modulesToRemove, true)) return false;
                } elseif ($itemType === 'feature') {
                    if (in_array($itemSlug, $featuresToRemove, true)) return false;
                    foreach ($modulesToRemove as $mSlug) {
                        $parentSlug = is_string($item['parentSlug'] ?? null) ? Str::lower(trim($item['parentSlug'])) : '';
                        if (Str::startsWith($itemSlug, $mSlug . '.') || $parentSlug === $mSlug) return false;
                    }
                } else {
                    if (in_array($itemSlug, $modulesToRemove, true) || in_array($itemSlug, $featuresToRemove, true)) return false;
                }
                return true;
            })->values()->toArray();

            $order->modules_snapshot = $snapshot;
            $shamsiDate = ResellerHelper::formatJalali(now(), 'Y/m/d H:i');
            $removedSummary = implode('، ', $removedNames);
            $order->notes = ($order->notes ? $order->notes . "\n" : '') . "کاهش/حذف از سفارش در تاریخ {$shamsiDate} شامل: {$removedSummary}";
            $order->save();

            if ($order->tenant_id && $order->tenant) {
                $tenant = $order->tenant;
                $tenant->update(['package_id' => null]);

                if (!empty($modulesToRemove)) {
                    TenantModule::where('tenant_id', $order->tenant_id)
                        ->whereIn('module_slug', $modulesToRemove)
                        ->update(['is_active' => false]);
                }

                $settings = $tenant->settings ?? [];
                if (!empty($settings['features']) && is_array($settings['features'])) {
                    $featuresSetting = $settings['features'];
                    if (Arr::isAssoc($featuresSetting)) {
                        foreach ($featuresSetting as $modKey => $featList) {
                            if (in_array(Str::lower($modKey), $modulesToRemove, true)) {
                                unset($featuresSetting[$modKey]);
                                continue;
                            }
                            if (is_array($featList)) {
                                $featuresSetting[$modKey] = array_values(array_filter($featList, function ($f) use ($featuresToRemove) {
                                    return is_string($f) ? !in_array(Str::lower(trim($f)), $featuresToRemove, true) : true;
                                }));
                            } elseif (is_string($featList) && in_array(Str::lower(trim($featList)), $featuresToRemove, true)) {
                                unset($featuresSetting[$modKey]);
                            }
                        }
                    } else {
                        $featuresSetting = array_values(array_filter($featuresSetting, function ($f) use ($featuresToRemove, $modulesToRemove) {
                            if (is_string($f)) {
                                $fLower = Str::lower(trim($f));
                                if (in_array($fLower, $featuresToRemove, true)) return false;
                                foreach ($modulesToRemove as $mSlug) {
                                    if (Str::startsWith($fLower, $mSlug . '.')) return false;
                                }
                            }
                            return true;
                        }));
                    }
                    $settings['features'] = $featuresSetting;
                    $tenant->settings = $settings;
                    $tenant->save();
                }

                $user = $tenant->subAccount?->user ?? $tenant->users()->first();
                if ($user) {
                    foreach ($featuresToRemove as $fSlug) {
                        try {
                            $user->revokePermissionTo($fSlug);
                        } catch (Throwable) {
                        }
                    }
                    foreach ($modulesToRemove as $mSlug) {
                        try {
                            $userPerms = $user->permissions->pluck('name')->toArray();
                            foreach ($userPerms as $pName) {
                                if (is_string($pName) && Str::startsWith(Str::lower(trim($pName)), $mSlug . '.')) {
                                    $user->revokePermissionTo($pName);
                                }
                            }
                        } catch (Throwable) {
                        }
                    }
                }

                $order->is_custom_package = true;
                $order->save();
            }
        });

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log(
                'order_items_removed',
                "حذف " . ResellerHelper::toPersianDigits(count($resolvedItems)) . " مورد از سفارش " . ResellerHelper::toPersianDigits($order->order_number) . ": " . implode('، ', $removedNames),
                $order,
                [
                    'items_count' => count($resolvedItems),
                    'items' => $resolvedItems,
                    'reseller_id' => $reseller->id,
                ]
            );
        }

        $itemsWord = count($resolvedItems) > 1 ? (ResellerHelper::toPersianDigits(count($resolvedItems)) . ' مورد انتخابی') : ($removedNames[0] ?? 'مورد انتخابی');

        return [
            'success' => true,
            'message' => "{$itemsWord} با موفقیت از این سفارش حذف گردید.",
            'removed_slugs' => collect($resolvedItems)->pluck('slug')->toArray(),
            'redirect_url' => route('reseller.orders.show', $order->id),
        ];
    }
}
