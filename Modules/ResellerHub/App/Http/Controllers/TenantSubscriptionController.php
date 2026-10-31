<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\ResellerHub\App\Actions\Invoices\CancelInvoiceAction;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\RequestTenantUpgradeRequest;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\TenantQuotaService;


class TenantSubscriptionController extends Controller
{
    public function __construct(
        protected ?CancelInvoiceAction $cancelInvoiceAction = null
    )
    {
        $this->cancelInvoiceAction = $cancelInvoiceAction
            ?: app(CancelInvoiceAction::class);
    }

    /**
     * Get all subscription, reseller and live quotas data for a tenant.
     */
    public function getSubscriptionData(?Tenant $tenant = null): ?array
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenant ?? $tenantManager->getTenant();

        if (!$tenant && auth()->check() && !empty(auth()->user()->tenant_id)) {
            $tenant = Tenant::find(auth()->user()->tenant_id);
            if ($tenant) {
                $tenantManager->setTenant($tenant);
            }
        }

        if (!$tenant) {
            return null;
        }

        $tenant->loadMissing(['reseller.user', 'package']);
        $reseller = $tenant->reseller;

        $package = $tenant->package;
        $hasNoPackage = is_null($package);
        $hasActivePlan = (bool)($package && $tenant->hasActivePaidPlan());
        $isExpired = $package ? $tenant->isExpired() : false;
        $quotaService = app(TenantQuotaService::class);
        $quotasSummary = $quotaService->getQuotasSummary($tenant);
        $now = Carbon::now();
        $expiresAt = $tenant->plan_expires_at;
        $daysRemaining = 0;
        $expiresAtJalali = __('resellerhub::messages.subscription.awaiting_plan');
        $isExpiringSoon = false;
        $daysRemainingClass = 'text-amber-600 dark:text-amber-400';

        if ($hasActivePlan && $expiresAt) {
            $expiresAtJalali = reseller_format_jalali($expiresAt);

            if (!$isExpired) {
                $daysRemaining = max(0, (int)round($now->diffInDays($expiresAt, false)));
                $isExpiringSoon = $daysRemaining > 0 && $daysRemaining <= 7;
                $daysRemainingClass = $isExpiringSoon ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400';
            } else {
                $daysRemainingClass = 'text-rose-600 dark:text-rose-400';
            }
        } elseif ($isExpired) {
            $daysRemainingClass = 'text-rose-600 dark:text-rose-400';
            if ($expiresAt) {
                $expiresAtJalali = reseller_format_jalali($expiresAt);
            }
        }

        // Reseller branding
        $resellerSettings = $reseller ? ResellerSetting::allForReseller($reseller->id) : [];
        $resellerInfo = [
            'company_name' => $reseller?->company_name ?: ($reseller?->user?->name ?? __('resellerhub::messages.common.official_reseller')),
            'brand_title' => $tenant->getBranding('brand_title') ?: ($reseller?->brand_title ?: ($reseller?->company_name ?? __('resellerhub::messages.common.default_brand'))),
            'brand_logo' => $tenant->getBranding('brand_logo') ?: ($reseller?->brand_logo ?? null),
            'support_phone' => $tenant->getBranding('support_phone') ?: ($reseller?->support_phone ?? ($resellerSettings['support_phone'] ?? null)),
            'support_email' => $tenant->getBranding('support_email') ?: ($reseller?->support_email ?? ($resellerSettings['support_email'] ?? null)),
            'footer_text' => $tenant->getBranding('brand_footer_text') ?: ($reseller?->brand_footer_text ?? null),
        ];

        $packageName = $package?->name ?? __('resellerhub::messages.subscription.no_active_plan');
        $currentMonthlyPrice = 0;
        $currentYearlyPrice = 0;
        if ($package && !$package->isFree()) {
            $currentMonthlyPrice = (int)$package->getPriceForMonths(1);
            $currentYearlyPrice = (int)$package->getPriceForMonths(12);
        }

        // Available upgrade packages: strictly packages belonging to the same reseller with HIGHER price
        $availableUpgradePackages = collect();
        if ($reseller) {
            $currentModules = is_array($package?->modules) ? $package->modules : [];
            $currentLimits = is_array($package?->limits) ? $package->limits : [];

            $currentModuleObjects = array_map(function ($slug) {
                return [
                    'slug' => $slug,
                    'title' => ResellerHelper::getModuleTitle($slug) ?: $slug,
                ];
            }, $currentModules);

            $availableUpgradePackages = ResellerPackage::where('reseller_id', $reseller->id)
                ->active()
                ->when($package, fn($q) => $q->where('id', '!=', $package->id))
                ->orderBy('monthly_price')
                ->get()
                ->filter(fn(ResellerPackage $candidate) => $candidate->isUpgradeOver($package))
                ->map(function (ResellerPackage $candidate) use ($currentModules, $currentLimits, $currentMonthlyPrice, $currentYearlyPrice) {
                    $candModules = is_array($candidate->modules) ? $candidate->modules : [];
                    $newModuleSlugs = array_values(array_diff($candModules, $currentModules));
                    $newModules = array_map(function ($slug) {
                        return [
                            'slug' => $slug,
                            'title' => ResellerHelper::getModuleTitle($slug) ?: $slug,
                        ];
                    }, $newModuleSlugs);

                    $allCandidateModules = array_map(function ($slug) {
                        return [
                            'slug' => $slug,
                            'title' => ResellerHelper::getModuleTitle($slug) ?: $slug,
                        ];
                    }, $candModules);

                    $candLimits = is_array($candidate->limits) ? $candidate->limits : [];
                    $quotaDiffs = [];
                    foreach ($candLimits as $qKey => $qLimit) {
                        $curLimit = $currentLimits[$qKey] ?? 0;
                        if ((int)$qLimit > (int)$curLimit || (int)$qLimit === 0 || (int)$qLimit === -1) {
                            $quotaDiffs[] = [
                                'key' => $qKey,
                                'title' => ResellerHelper::getQuotaTitle($qKey),
                                'from' => $curLimit,
                                'to' => $qLimit,
                                'is_unlimited' => (int)$qLimit === 0 || (int)$qLimit === -1,
                            ];
                        }
                    }

                    $candRawMonthly = (int)$candidate->getPriceForMonths(1);
                    $candFinalMonthly = (int)$candidate->getFinalPriceForMonths(1);
                    $candRawYearly = (int)$candidate->getPriceForMonths(12);
                    $candFinalYearly = (int)$candidate->getFinalPriceForMonths(12);

                    $candActiveMonths = $candidate->getActiveMonths();
                    if (empty($candActiveMonths)) {
                        $candActiveMonths = [1, 3, 6, 12];
                    }
                    $candPeriods = [];
                    foreach ($candActiveMonths as $cm) {
                        $cRaw = $candidate->getPriceForMonths($cm);
                        $cFinal = $candidate->getFinalPriceForMonths($cm);
                        $cHasDiscount = $candidate->hasActiveDiscountForMonths($cm) && $cFinal < $cRaw;
                        $candPeriods[] = [
                            'months' => $cm,
                            'label' => ResellerHelper::durationLabel($cm, true),
                            'raw_price' => $cRaw,
                            'final_price' => $cFinal,
                            'price_formatted' => ResellerHelper::toPersianDigits(number_format($cFinal)),
                            'raw_price_formatted' => ResellerHelper::toPersianDigits(number_format($cRaw)),
                            'has_discount' => $cHasDiscount,
                            'discount_expires_at_jalali' => $candidate->getDiscountExpiresAtJalaliForMonths($cm),
                        ];
                    }

                    $candidate->setAttribute('available_periods', $candPeriods);
                    $candidate->setAttribute('new_modules', $newModules);
                    $candidate->setAttribute('new_modules_count', count($newModules));
                    $candidate->setAttribute('all_modules', $allCandidateModules);
                    $candidate->setAttribute('quota_diffs', $quotaDiffs);
                    $candidate->setAttribute('raw_monthly_price', $candRawMonthly);
                    $candidate->setAttribute('final_monthly_price', $candFinalMonthly);
                    $candidate->setAttribute('raw_yearly_price', $candRawYearly);
                    $candidate->setAttribute('final_yearly_price', $candFinalYearly);
                    $candidate->setAttribute('has_monthly_discount', $candidate->hasActiveDiscountForMonths(1));
                    $candidate->setAttribute('has_yearly_discount', $candidate->hasActiveDiscountForMonths(12));
                    $candidate->setAttribute('has_active_discount', $candidate->isSubscription() ? ($candidate->hasActiveDiscountForMonths(1) || $candidate->hasActiveDiscountForMonths(12)) : $candidate->hasActiveDiscount());
                    $candidate->setAttribute('discount_value', (float)$candidate->discount_value);
                    $candidate->setAttribute('discount_type', $candidate->discount_type);
                    $candidate->setAttribute('discount_expires_at_jalali', $candidate->discount_expires_at_jalali);
                    $candidate->setAttribute('diff_monthly_price', max(0, $candFinalMonthly - (int)$currentMonthlyPrice));
                    $candidate->setAttribute('diff_yearly_price', max(0, $candFinalYearly - (int)$currentYearlyPrice));

                    return $candidate;
                })
                ->values();

            // Find any pending/unpaid upgrade invoices for this tenant to flag candidate packages
            $pendingUpgradeInvoices = ResellerInvoice::where('tenant_id', $tenant->id)
                ->whereIn('status', [
                    ResellerInvoice::STATUS_UNPAID,
                    ResellerInvoice::STATUS_OVERDUE,
                    ResellerInvoice::STATUS_DRAFT,
                    'pending',
                ])
                ->latest('id')
                ->get();

            $availableUpgradePackages->transform(function ($pkg) use ($pendingUpgradeInvoices) {
                $matchedInvoice = $pendingUpgradeInvoices->first(function ($inv) use ($pkg) {
                    return (int)$inv->package_id === (int)$pkg->id
                        || (int)($inv->meta['to_package_id'] ?? 0) === (int)$pkg->id;
                });

                if ($matchedInvoice) {
                    $pkg->setAttribute('pending_invoice', [
                        'id' => $matchedInvoice->id,
                        'invoice_number' => $matchedInvoice->invoice_number,
                        'total' => $matchedInvoice->total,
                        'total_formatted' => ResellerHelper::formatPrice($matchedInvoice->total, 'toman'),
                        'due_date_jalali' => $matchedInvoice->due_date ? ResellerHelper::toJalali($matchedInvoice->due_date, 'Y/m/d') : null,
                    ]);
                } else {
                    $pkg->setAttribute('pending_invoice', null);
                }

                return $pkg;
            });
        }

        // Financial totals calculated via single aggregate query
        $invoiceStats = ResellerInvoice::where('tenant_id', $tenant->id)
            ->selectRaw("COUNT(*) as total_count, COALESCE(SUM(total), 0) as total_sum, COALESCE(SUM(paid_amount), 0) as paid_sum")
            ->first();
        $invoicesTotalCount = (int)($invoiceStats->total_count ?? 0);
        $invoicesTotalSum = (int)($invoiceStats->total_sum ?? 0);
        $invoicesPaidSum = (int)($invoiceStats->paid_sum ?? 0);
        $invoicesUnpaidSum = max(0, $invoicesTotalSum - $invoicesPaidSum);

        // Fetch paginated invoices for this tenant
        $invoicesPerPage = (int)request('invoices_per_page', 10);
        if ($invoicesPerPage < 1 || $invoicesPerPage > 100) {
            $invoicesPerPage = 10;
        }
        $tenantInvoices = ResellerInvoice::where('tenant_id', $tenant->id)
            ->with(['package'])
            ->orderBy('id', 'desc')
            ->paginate($invoicesPerPage, ['*'], 'invoices_page')
            ->withQueryString();

        $currency = $resellerSettings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);

        return compact(
            'tenant',
            'reseller',
            'resellerInfo',
            'package',
            'packageName',
            'hasActivePlan',
            'hasNoPackage',
            'quotasSummary',
            'expiresAtJalali',
            'daysRemaining',
            'isExpired',
            'isExpiringSoon',
            'daysRemainingClass',
            'availableUpgradePackages',
            'currentModuleObjects',
            'tenantInvoices',
            'invoicesTotalCount',
            'invoicesTotalSum',
            'invoicesPaidSum',
            'invoicesUnpaidSum',
            'currencyLabel'
        );
    }

    /**
     * Display the subscription, reseller info, and live quotas to the tenant admin.
     */
    public function show(Request $request): JsonResponse|View|RedirectResponse
    {
        $data = $this->getSubscriptionData();

        if (!$data) {
            return redirect()->route('user.dashboard')
                ->with('error', __('resellerhub::messages.subscription.not_found'));
        }

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        if ($request->has('standalone') || $request->has('direct')) {
            return view('resellerhub::tenant.subscription', $data);
        }

        return redirect()->route('profile.show', ['active_tab' => 'subscription']);
    }

    /**
     * Submit an upgrade request from tenant admin, generating an upgrade invoice and redirecting to payment.
     */
    public function requestUpgrade(RequestTenantUpgradeRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->getTenant() ?: Tenant::find(auth()->user()?->tenant_id);

        if (!$tenant || !$tenant->reseller) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('resellerhub::messages.subscription.reseller_not_found')], 404);
            }
            return back()->with('error', __('resellerhub::messages.subscription.reseller_not_found'));
        }

        $user = auth()->user();
        $message = trim((string)($validated['message'] ?? ''));
        $requestedPackageId = !empty($validated['package_id']) ? (int)$validated['package_id'] : null;

        if (!$requestedPackageId) {
            $err = __('resellerhub::messages.subscription.select_package');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        $candidatePackage = ResellerPackage::where('reseller_id', $tenant->reseller_id)->active()->find($requestedPackageId);
        if (!$candidatePackage) {
            $err = __('resellerhub::messages.subscription.package_unavailable');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        $currentPackage = $tenant->package;
        $currentMonthly = ($currentPackage && !$currentPackage->isFree()) ? (int)$currentPackage->getPriceForMonths(1) : 0;
        $currentYearly = ($currentPackage && !$currentPackage->isFree()) ? (int)$currentPackage->getPriceForMonths(12) : 0;

        // Enterprise guard: If same package is selected, treat as renewal and check advance renewal limit
        if ($currentPackage && (int)$candidatePackage->id === (int)$currentPackage->id) {
            /** @var ResellerBillingService $billingService */
            $billingService = app(ResellerBillingService::class);
            $limitCheck = $billingService->validateAdvanceRenewalLimit($tenant, 12, $tenant->reseller_id);
            if (!$limitCheck['allowed']) {
                $expiresAtJalali = !empty($limitCheck['current_expires_at'])
                    ? reseller_format_jalali($limitCheck['current_expires_at'])
                    : '';
                $maxCycles = $limitCheck['max_cycles'] ?? 1;
                $payload = [
                    'success' => false,
                    'renewal_limit_exceeded' => true,
                    'tenant_name' => $tenant->name,
                    'package_name' => $currentPackage->name,
                    'max_cycles' => $maxCycles,
                    'max_cycles_label' => ResellerHelper::toPersianDigits((string)$maxCycles) . ' ' . __('resellerhub::messages.orders.billing_cycle'),
                    'expires_at_jalali' => ResellerHelper::toPersianDigits($expiresAtJalali),
                    'message' => $limitCheck['message'],
                ];

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json($payload, 422);
                }
                return back()->with('renewal_limit_payload', $payload)->with('error', $limitCheck['message']);
            }
        } elseif ($currentPackage && !$candidatePackage->isUpgradeOver($currentPackage)) {
            $err = __('resellerhub::messages.subscription.upgrade_only_higher');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        if (!$currentPackage && $candidatePackage->isFree()) {
            $tenant->update([
                'package_id' => $candidatePackage->id,
                'plan_expires_at' => now()->addMonths(12),
            ]);
            $billingService = app(ResellerBillingService::class);
            $freeInvoice = $billingService->createInvoice($tenant->reseller, [
                'tenant_id' => $tenant->id,
                'package_id' => $candidatePackage->id,
                'type' => InvoiceType::OFFICIAL->value,
                'status' => InvoiceStatus::PAID->value,
                'paid_at' => now(),
                'paid_amount' => 0,
                'payment_method' => 'free_grant',
                'sub_account_name' => $user?->name ?: $tenant->name,
                'sub_account_mobile' => $user?->mobile,
                'sub_account_email' => $user?->email ?? '',
                'meta' => [
                    'source' => 'subscription_free_selection',
                    'source_label' => __('resellerhub::messages.subscription.online_purchase'),
                    'package_id' => $candidatePackage->id,
                    'package_name' => $candidatePackage->name,
                ],
                'items' => [
                    [
                        'item_type' => 'package',
                        'item_id' => 'package_' . $candidatePackage->id,
                        'title' => __('resellerhub::messages.subscription.free_plan_title', ['name' => $candidatePackage->name]),
                        'duration_months' => 12,
                        'quantity' => 1,
                        'unit_price' => 0,
                        'tax_rate' => 0,
                    ]
                ],
            ], $user);

            $billingService->convertInvoiceToOrder($freeInvoice);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'free_activated' => true,
                    'message' => __('resellerhub::messages.subscription.free_activated', ['name' => $candidatePackage->name]),
                    'redirect' => route('profile.show', ['active_tab' => 'subscription']),
                ]);
            }
            return back()->with('success', __('resellerhub::messages.subscription.free_activated', ['name' => $candidatePackage->name]));
        }

        // Find latest active order or create one if none exists
        $order = ResellerOrder::where('tenant_id', $tenant->id)
            ->where('reseller_id', $tenant->reseller_id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$order) {
            $order = ResellerOrder::create([
                'reseller_id' => $tenant->reseller_id,
                'tenant_id' => $tenant->id,
                'order_number' => ResellerOrder::generateNextNumber($tenant->reseller_id),
                'sub_account_name' => $tenant->name,
                'sub_account_mobile' => $user?->mobile,
                'sub_account_email' => $user?->email,
                'status' => ResellerOrder::STATUS_ACTIVE,
                'billing_cycle' => 'yearly',
                'start_date' => $tenant->created_at ?? now(),
                'end_date' => $tenant->plan_expires_at ?? now()->addYear(),
                'renewal_date' => $tenant->plan_expires_at ?? now()->addYear(),
                'total_amount' => 0,
                'renewal_price' => 0,
                'modules_snapshot' => collect($currentPackage?->modules ?? [])->map(fn($m) => [
                    'type' => 'module',
                    'slug' => $m,
                    'title' => ResellerHelper::getModuleTitle($m) ?: $m,
                    'duration_months' => 12,
                ])->toArray(),
                'created_by' => $user?->id,
            ]);
        }

        // Check if an unpaid/pending upgrade invoice already exists for this candidate package to prevent duplicates
        $existingPendingInvoice = ResellerInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', [
                ResellerInvoice::STATUS_UNPAID,
                ResellerInvoice::STATUS_OVERDUE,
                ResellerInvoice::STATUS_DRAFT,
                'pending',
            ])
            ->where(function ($q) use ($candidatePackage) {
                $q->where('package_id', $candidatePackage->id)
                    ->orWhere('meta->to_package_id', $candidatePackage->id);
            })
            ->latest('id')
            ->first();

        if ($existingPendingInvoice) {
            $msg = __('resellerhub::messages.subscription.duplicate_pending', [
                'invoice' => $existingPendingInvoice->invoice_number,
                'name' => $candidatePackage->name,
            ]);
            Log::info('[ResellerHub] Blocked duplicate upgrade invoice request for Tenant #' . $tenant->id . ' (Target Package: ' . $candidatePackage->name . ', Existing Invoice #' . $existingPendingInvoice->id . ')');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'has_pending_invoice' => true,
                    'invoice_id' => $existingPendingInvoice->id,
                    'invoice_number' => $existingPendingInvoice->invoice_number,
                    'package_name' => $candidatePackage->name,
                    'invoice_due' => $existingPendingInvoice->total,
                    'invoice_due_formatted' => ResellerHelper::formatPrice($existingPendingInvoice->total, 'toman'),
                    'pay_url' => route('portal.invoices.pay', $existingPendingInvoice->id),
                    'total' => $existingPendingInvoice->total,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        /** @var ResellerBillingService $billingService */
        $billingService = app(ResellerBillingService::class);
        $durationMonths = !empty($validated['duration_months']) ? (int)$validated['duration_months'] : null;
        $billingCycle = !empty($validated['billing_cycle']) ? $validated['billing_cycle'] : ($durationMonths ? (string)$durationMonths : null);

        $invoice = $billingService->createUpgradeInvoiceFromOrder($order, $candidatePackage->id, array_filter([
            'notes' => $message ?: __('resellerhub::messages.subscription.upgrade_request_notes', ['name' => $candidatePackage->name]),
            'duration_months' => $durationMonths,
            'billing_cycle' => $billingCycle,
        ]));

        Log::info('[ResellerHub] Upgrade invoice #' . $invoice->id . ' created for Tenant #' . $tenant->id . ' (' . $tenant->name . ')', [
            'user' => $user?->name,
            'candidate_package' => $candidatePackage->name,
            'invoice_total' => $invoice->total,
            'invoice_status' => $invoice->status,
        ]);

        $payUrl = route('portal.invoices.pay', $invoice->id);

        if ($invoice->isPaid()) {
            $successMsg = __('resellerhub::messages.subscription.upgrade_success', ['name' => $candidatePackage->name]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_paid' => true,
                    'message' => $successMsg,
                    'redirect' => route('profile.show', ['active_tab' => 'subscription']),
                ]);
            }
            return redirect()->route('profile.show', ['active_tab' => 'subscription'])->with('success', $successMsg);
        }

        $isReused = (bool)$existingPendingInvoice;
        $successMsg = $isReused
            ? __('resellerhub::messages.subscription.pending_invoice_reused', ['invoice' => $invoice->invoice_number])
            : __('resellerhub::messages.subscription.upgrade_invoice_created');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_reused' => $isReused,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'package_name' => $candidatePackage->name,
                'invoice_due' => $invoice->total,
                'invoice_due_formatted' => ResellerHelper::formatPrice($invoice->total, 'toman'),
                'pay_url' => $payUrl,
                'total' => $invoice->total,
                'message' => $successMsg,
            ]);
        }

        return redirect()->to($payUrl);
    }

    /**
     * Check if tenant has remaining quota for a given key, returning JSON status and payload.
     */
    public function checkQuota(Request $request): JsonResponse
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->getTenant() ?: Tenant::find(auth()->user()?->tenant_id);

        if (!$tenant) {
            return response()->json([
                'has_remaining' => true,
                'payload' => null,
            ]);
        }

        $rawKey = (string)$request->query('key', QuotaKey::Invoices->value);
        $quotaKey = QuotaKey::resolve($rawKey) ?: $rawKey;

        /** @var TenantQuotaService $quotaService */
        $quotaService = app(TenantQuotaService::class);

        $hasRemaining = $quotaService->hasRemainingQuota($tenant, $quotaKey);
        $payload = $hasRemaining ? null : $quotaService->getExceededQuotaPayload($tenant, $quotaKey);

        return response()->json([
            'has_remaining' => $hasRemaining,
            'payload' => $payload,
        ]);
    }

    /**
     * Cancel an unpaid/pending invoice from the tenant portal.
     */
    public function cancelInvoice(Request $request, ResellerInvoice $invoice): JsonResponse|RedirectResponse
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->getTenant() ?: Tenant::find(auth()->user()?->tenant_id);

        if (!$tenant || (int)$invoice->tenant_id !== (int)$tenant->id) {
            $msg = __('resellerhub::messages.common.unauthorized');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        if ($invoice->isPaid()) {
            $msg = __('resellerhub::messages.invoices.cannot_cancel_paid');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($invoice->isCancelled()) {
            $msg = __('resellerhub::messages.invoices.payment_already_cancelled');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        try {
            $this->cancelInvoiceAction->execute($invoice);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        Log::info('[ResellerHub] Invoice #' . $invoice->id . ' cancelled by Tenant #' . $tenant->id);

        $successMsg = __('resellerhub::messages.invoices.cancelled_success');
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'invoice_id' => $invoice->id,
                'status' => InvoiceStatus::CANCELLED->value,
                'status_label' => InvoiceStatus::CANCELLED->label(),
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
