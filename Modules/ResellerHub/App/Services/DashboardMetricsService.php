<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\Dashboard\PeriodBucketBuilder;
use Morilog\Jalali\Jalalian;

class DashboardMetricsService
{
    public const DEFAULT_CACHE_TTL = 180; // 3 minutes (within 60-300s requirement)
    public const VALID_PERIODS = ['30d', '3m', '6m', '12m', 'ytd'];

    protected TenantQuotaService $quotaService;

    public function __construct(TenantQuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    /**
     * Get aggregated metrics for dashboard with caching.
     */
    public function getDashboardData(?int $scopedResellerId, string $period = '6m', bool $forceRefresh = false): array
    {
        if (!in_array($period, self::VALID_PERIODS, true)) {
            $period = '6m';
        }

        $resellerKey = $scopedResellerId !== null ? (string)$scopedResellerId : 'all';
        $cacheKey = "rh_dash_metrics_{$resellerKey}_{$period}";
        $ttl = (int)config('resellerhub.dashboard_cache_ttl', self::DEFAULT_CACHE_TTL);

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $data = $this->computeMetrics($scopedResellerId, $period);
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    /**
     * Flush cached dashboard metrics for a reseller or for all.
     */
    public function clearDashboardCache(?int $scopedResellerId = null): void
    {
        $keys = [];
        $targets = $scopedResellerId !== null ? [(string)$scopedResellerId, 'all'] : ['all'];

        foreach ($targets as $target) {
            foreach (self::VALID_PERIODS as $p) {
                Cache::forget("rh_dash_metrics_{$target}_{$p}");
            }
        }
    }

    /**
     * Compute all metrics using aggregate queries and optimized single-pass algorithms.
     */
    protected function computeMetrics(?int $scopedResellerId, string $selectedPeriod): array
    {
        $tenantQuery = Tenant::query()->when($scopedResellerId, function ($q) use ($scopedResellerId) {
            $q->where('reseller_id', $scopedResellerId);
        });

        $applyResellerScope = fn($query) => $scopedResellerId
            ? $query->where('reseller_id', $scopedResellerId)
            : $query;

        $invoiceQuery = $applyResellerScope(ResellerInvoice::query());
        $packageQuery = $applyResellerScope(ResellerPackage::query());
        $subAccountQuery = $applyResellerScope(ResellerSubAccount::query());
        $orderQuery = $applyResellerScope(ResellerOrder::query());

        // 1. Tenant Overview Statistics (Covered by composite index: [reseller_id, status], [reseller_id, plan_expires_at])
        $todayStr = now()->format('Y-m-d');
        $soonStr = now()->addDays(30)->format('Y-m-d');

        $tenantStats = (clone $tenantQuery)
            ->selectRaw(
                "COUNT(*) as total,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as suspended,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_payment,
                 SUM(CASE WHEN plan_expires_at IS NOT NULL AND plan_expires_at < ? THEN 1 ELSE 0 END) as expired,
                 SUM(CASE WHEN plan_expires_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as expiring_soon",
                [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_PENDING_PAYMENT, $todayStr, $todayStr, $soonStr]
            )
            ->first();

        $totalTenants = (int)($tenantStats->total ?? 0);
        $activeTenants = (int)($tenantStats->active ?? 0);
        $suspendedTenants = (int)($tenantStats->suspended ?? 0);
        $pendingPaymentTenants = (int)($tenantStats->pending_payment ?? 0);
        $expiredTenants = (int)($tenantStats->expired ?? 0);
        $expiringSoonTenants = (int)($tenantStats->expiring_soon ?? 0);

        // 2. Invoices Aggregate Statistics
        $invoiceStats = (clone $invoiceQuery)
            ->selectRaw(
                "COUNT(*) as total,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_count,
                 SUM(CASE WHEN status = ? THEN total ELSE 0 END) as paid_total,
                 SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as unpaid_count,
                 SUM(CASE WHEN status IN (?, ?) THEN CASE WHEN paid_amount IS NULL OR paid_amount >= total THEN 0 ELSE total - paid_amount END ELSE 0 END) as unpaid_total,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as overdue_count",
                [
                    ResellerInvoice::STATUS_PAID,
                    ResellerInvoice::STATUS_PAID,
                    ResellerInvoice::STATUS_UNPAID,
                    ResellerInvoice::STATUS_OVERDUE,
                    ResellerInvoice::STATUS_UNPAID,
                    ResellerInvoice::STATUS_OVERDUE,
                    ResellerInvoice::STATUS_OVERDUE,
                ]
            )
            ->first();

        $totalInvoices = (int)($invoiceStats->total ?? 0);
        $paidInvoicesCount = (int)($invoiceStats->paid_count ?? 0);
        $totalRevenue = (int)($invoiceStats->paid_total ?? 0);
        $unpaidInvoicesCount = (int)($invoiceStats->unpaid_count ?? 0);
        $unpaidInvoicesTotal = (int)($invoiceStats->unpaid_total ?? 0);
        $overdueInvoicesCount = (int)($invoiceStats->overdue_count ?? 0);
        $packagesCount = (clone $packageQuery)->count();
        $activePackagesCount = (clone $packageQuery)->where('is_active', true)->count();
        $subAccountsCount = (clone $subAccountQuery)->count();
        $activeOrdersCount = (clone $orderQuery)->where('status', ResellerOrder::STATUS_ACTIVE)->count();
        $totalOrdersCount = (clone $orderQuery)->count();
        $onlineOrdersCount = (clone $orderQuery)->online()->count();
        $manualOrdersCount = (clone $orderQuery)->manual()->count();
        $onlineOrdersRevenue = (int)(clone $orderQuery)->online()->sum('total_amount');
        $manualOrdersRevenue = (int)(clone $orderQuery)->manual()->sum('total_amount');
        $onlineOrdersRatio = $totalOrdersCount > 0 ? round(($onlineOrdersCount / $totalOrdersCount) * 100, 1) : 0;
        $manualOrdersRatio = $totalOrdersCount > 0 ? round(($manualOrdersCount / $totalOrdersCount) * 100, 1) : 0;

        // 5. Period labels & Time Series
        $periodLabels = [
            '30d' => __('resellerhub::resellerhub.dashboard.periods.30d') ?: '۳۰ روز اخیر',
            '3m' => __('resellerhub::resellerhub.dashboard.periods.3m') ?: '۳ ماه اخیر',
            '6m' => __('resellerhub::resellerhub.dashboard.periods.6m') ?: '۶ ماه اخیر',
            '12m' => __('resellerhub::resellerhub.dashboard.periods.12m') ?: '۱ سال اخیر',
            'ytd' => __('resellerhub::resellerhub.dashboard.periods.ytd') ?: 'سال جاری',
        ];
        $selectedPeriodLabel = $periodLabels[$selectedPeriod] ?? $periodLabels['6m'];

        $now = now();
        $currentJalali = JalaliDateHelper::toJalali($now) ?? Jalalian::now();
        $currentJalaliYear = $currentJalali->getYear();
        $currentJalaliMonth = $currentJalali->getMonth();

        if ($selectedPeriod === '30d') {
            $buckets = collect([
                ['start' => now()->subDays(28)->startOfDay(), 'end' => now()->subDays(21)->endOfDay(), 'label' => '۴ هفته پیش'],
                ['start' => now()->subDays(21)->startOfDay(), 'end' => now()->subDays(14)->endOfDay(), 'label' => '۳ هفته پیش'],
                ['start' => now()->subDays(14)->startOfDay(), 'end' => now()->subDays(7)->endOfDay(), 'label' => '۲ هفته پیش'],
                ['start' => now()->subDays(7)->startOfDay(), 'end' => now()->endOfDay(), 'label' => 'هفته جاری'],
            ]);

            $chartLabels = $buckets->pluck('label')->values();
            $tenantsCreatedSeries = collect();
            $revenueSeries = collect();

            foreach ($buckets as $b) {
                $tCount = (clone $tenantQuery)
                    ->whereBetween('created_at', [$b['start'], $b['end']])
                    ->count();
                $tenantsCreatedSeries->push($tCount);

                $rSum = (clone $invoiceQuery)
                    ->where('status', ResellerInvoice::STATUS_PAID)
                    ->whereBetween('issue_date', [$b['start']->toDateString(), $b['end']->toDateString()])
                    ->sum('total');
                $revenueSeries->push((int)$rSum);
            }

            $prevPeriodStart = now()->subDays(56)->startOfDay();
            $prevPeriodEnd = now()->subDays(28)->subSecond();
        } else {
            $buckets = collect();

            $bucketBuilder = app(PeriodBucketBuilder::class);

            if ($selectedPeriod === 'ytd') {
                // Current Jalali Financial Year: From 1 Farvardin up to the current Shamsi month
                $buckets = collect($bucketBuilder->jalaliYearToDate($currentJalali));

                $prevPeriodStart = (new Jalalian($currentJalaliYear - 1, 1, 1))->toCarbon()->startOfDay();
                $prevDays = (new Jalalian($currentJalaliYear - 1, $currentJalaliMonth, 1))->getMonthDays();
                $prevPeriodEnd = (new Jalalian($currentJalaliYear - 1, $currentJalaliMonth, $prevDays, 23, 59, 59))->toCarbon()->endOfDay();
            } else {
                // Rolling Jalali months (3m, 6m, 12m)
                $monthCount = match ($selectedPeriod) {
                    '3m' => 3,
                    '12m' => 12,
                    default => 6,
                };

                $buckets = collect($bucketBuilder->rollingJalaliMonths($currentJalali, $monthCount));

                $firstBucketStart = $buckets->first()['start'];
                $prevPeriodStart = Jalalian::fromCarbon($firstBucketStart)->subMonths($monthCount)->toCarbon()->startOfDay();
                $prevPeriodEnd = $firstBucketStart->copy()->subSecond();
            }

            $periodStart = $buckets->first()['start'];
            $periodEnd = $buckets->last()['end'];

            // Initialize series indexed by bucket keys
            $tenantsCreatedSeries = $buckets->pluck('key')->flip()->map(fn() => 0);
            $recentTenants = (clone $tenantQuery)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->get(['created_at']);

            foreach ($recentTenants as $recentTenant) {
                if ($recentTenant->created_at) {
                    $jDate = JalaliDateHelper::toJalali($recentTenant->created_at);
                    if ($jDate) {
                        $key = sprintf('%04d-%02d', $jDate->getYear(), $jDate->getMonth());
                        if ($tenantsCreatedSeries->has($key)) {
                            $tenantsCreatedSeries[$key] = $tenantsCreatedSeries[$key] + 1;
                        }
                    }
                }
            }

            $revenueSeries = $buckets->pluck('key')->flip()->map(fn() => 0);
            $recentPaidInvoices = (clone $invoiceQuery)
                ->where('status', ResellerInvoice::STATUS_PAID)
                ->whereBetween('issue_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->get(['issue_date', 'paid_at', 'total']);

            foreach ($recentPaidInvoices as $paidInvoice) {
                $referenceDate = $paidInvoice->paid_at ?: $paidInvoice->issue_date;
                if ($referenceDate) {
                    $jDate = JalaliDateHelper::toJalali($referenceDate);
                    if ($jDate) {
                        $key = sprintf('%04d-%02d', $jDate->getYear(), $jDate->getMonth());
                        if ($revenueSeries->has($key)) {
                            $revenueSeries[$key] = $revenueSeries[$key] + (int)$paidInvoice->total;
                        }
                    }
                }
            }

            $chartLabels = $buckets->pluck('label')->values();
        }

        $periodRevenue = (int)$revenueSeries->sum();
        $periodTenantsCreated = (int)$tenantsCreatedSeries->sum();

        $prevPeriodRevenue = (int)(clone $invoiceQuery)
            ->where('status', ResellerInvoice::STATUS_PAID)
            ->whereBetween('issue_date', [$prevPeriodStart->toDateString(), $prevPeriodEnd->toDateString()])
            ->sum('total');

        $prevPeriodTenants = (int)(clone $tenantQuery)
            ->whereBetween('created_at', [$prevPeriodStart, $prevPeriodEnd])
            ->count();

        $revenueGrowthPercent = $prevPeriodRevenue > 0
            ? round((($periodRevenue - $prevPeriodRevenue) / $prevPeriodRevenue) * 100, 1)
            : ($periodRevenue > 0 ? 100.0 : 0.0);

        $tenantsGrowthPercent = $prevPeriodTenants > 0
            ? round((($periodTenantsCreated - $prevPeriodTenants) / $prevPeriodTenants) * 100, 1)
            : ($periodTenantsCreated > 0 ? 100.0 : 0.0);

        $activeRatio = $totalTenants > 0 ? round(($activeTenants / $totalTenants) * 100, 1) : 0;
        $arpu = $totalTenants > 0 ? round($totalRevenue / $totalTenants) : 0;

        $calcExpectedTenantRevenue = function ($t) use ($arpu) {
            if ($t->package) {
                $p = $t->package;
                if ($p->yearly_price && $p->yearly_price > 0) {
                    return (int)$p->getFinalYearlyPriceAttribute();
                }
                if ($p->monthly_price && $p->monthly_price > 0) {
                    return (int)($p->getFinalMonthlyPriceAttribute() * 12);
                }
                if ($p->one_time_price && $p->one_time_price > 0) {
                    return (int)$p->getFinalOneTimePriceAttribute();
                }
            }
            return (int)$arpu;
        };

        $bucket1Tenants = (clone $tenantQuery)
            ->with('package')
            ->whereNotNull('plan_expires_at')
            ->whereBetween('plan_expires_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->get(['id', 'reseller_id', 'package_id', 'plan_expires_at']);

        $bucket2Tenants = (clone $tenantQuery)
            ->with('package')
            ->whereNotNull('plan_expires_at')
            ->whereBetween('plan_expires_at', [now()->addDays(30)->startOfDay(), now()->addDays(60)->endOfDay()])
            ->get(['id', 'reseller_id', 'package_id', 'plan_expires_at']);

        $bucket3Tenants = (clone $tenantQuery)
            ->with('package')
            ->whereNotNull('plan_expires_at')
            ->whereBetween('plan_expires_at', [now()->addDays(60)->startOfDay(), now()->addDays(90)->endOfDay()])
            ->get(['id', 'reseller_id', 'package_id', 'plan_expires_at']);

        $pipelineData = [
            '30d' => [
                'label' => __('resellerhub::resellerhub.dashboard.pipeline.30d_label') ?: 'تا ۳۰ روز آینده',
                'short_label' => __('resellerhub::resellerhub.dashboard.pipeline.30d_short') ?: '۳۰ روز',
                'count' => $bucket1Tenants->count(),
                'revenue' => (int)$bucket1Tenants->sum($calcExpectedTenantRevenue),
            ],
            '60d' => [
                'label' => __('resellerhub::resellerhub.dashboard.pipeline.60d_label') ?: '۳۱ تا ۶۰ روز',
                'short_label' => __('resellerhub::resellerhub.dashboard.pipeline.60d_short') ?: '۶۰ روز',
                'count' => $bucket2Tenants->count(),
                'revenue' => (int)$bucket2Tenants->sum($calcExpectedTenantRevenue),
            ],
            '90d' => [
                'label' => __('resellerhub::resellerhub.dashboard.pipeline.90d_label') ?: '۶۱ تا ۹۰ روز',
                'short_label' => __('resellerhub::resellerhub.dashboard.pipeline.90d_short') ?: '۹۰ روز',
                'count' => $bucket3Tenants->count(),
                'revenue' => (int)$bucket3Tenants->sum($calcExpectedTenantRevenue),
            ],
        ];

        $totalPipelineCount = $pipelineData['30d']['count'] + $pipelineData['60d']['count'] + $pipelineData['90d']['count'];
        $totalPipelineRevenue = $pipelineData['30d']['revenue'] + $pipelineData['60d']['revenue'] + $pipelineData['90d']['revenue'];

        $revenueComposition = [
            'collected' => [
                'label' => __('resellerhub::resellerhub.dashboard.revenue.collected') ?: 'درآمد وصول‌شده',
                'amount' => $totalRevenue,
                'count' => $paidInvoicesCount,
                'color' => '#10b981',
            ],
            'receivables' => [
                'label' => __('resellerhub::resellerhub.dashboard.revenue.receivables') ?: 'مطالبات معوق و جاری',
                'amount' => $unpaidInvoicesTotal,
                'count' => $unpaidInvoicesCount,
                'color' => '#f59e0b',
            ],
            'forecasted' => [
                'label' => __('resellerhub::resellerhub.dashboard.revenue.forecasted') ?: 'پیش‌بینی تمدید ۳ ماهه',
                'amount' => $totalPipelineRevenue,
                'count' => $totalPipelineCount,
                'color' => '#6366f1',
            ],
        ];

        $totalFinancialVolume = $totalRevenue + $unpaidInvoicesTotal + $totalPipelineRevenue;
        $collectionRate = ($totalRevenue + $unpaidInvoicesTotal) > 0
            ? round(($totalRevenue / ($totalRevenue + $unpaidInvoicesTotal)) * 100, 1)
            : 100.0;

        $expiringTenants = (clone $tenantQuery)
            ->whereNotNull('plan_expires_at')
            ->whereBetween('plan_expires_at', [now()->format('Y-m-d'), now()->addDays(30)->format('Y-m-d')])
            ->where(function ($q) {
                $q->whereNull('package_id')
                    ->orWhereHas('package', function ($pq) {
                        $pq->where('price_type', ResellerPackage::PRICE_TYPE_SUBSCRIPTION);
                    });
            })
            ->with('package')
            ->orderBy('plan_expires_at')
            ->limit(6)
            ->get(['id', 'name', 'subdomain', 'status', 'plan_expires_at', 'package_id']);

        $unpaidInvoices = (clone $invoiceQuery)
            ->whereIn('status', [ResellerInvoice::STATUS_UNPAID, ResellerInvoice::STATUS_OVERDUE])
            ->orderByDesc('issue_date')
            ->limit(6)
            ->get(['id', 'invoice_number', 'sub_account_name', 'total', 'paid_amount', 'status', 'due_date']);

        $allResellerTenantIds = (clone $tenantQuery)->pluck('id');
        $totalResellerTenants = $allResellerTenantIds->count();

        //Module Adoption Aggregate Query
        $rawModuleCounts = DB::table('tenant_modules')
            ->whereIn('tenant_id', $allResellerTenantIds)
            ->where('is_active', true)
            ->select('module_slug', DB::raw('count(distinct tenant_id) as tenant_count'))
            ->groupBy('module_slug')
            ->orderByDesc('tenant_count')
            ->get();

        $moduleColors = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e',
            '#06b6d4', '#10b981', '#f59e0b', '#3b82f6',
            '#14b8a6', '#84cc16', '#a855f7', '#0ea5e9'
        ];

        $topModules = $rawModuleCounts->take(8)->map(function ($m, $idx) use ($moduleColors, $totalResellerTenants) {
            $slug = strtolower(trim($m->module_slug));
            $name = ResellerHelper::getModuleTitle($slug);
            $count = (int)$m->tenant_count;
            $ratio = $totalResellerTenants > 0 ? round(($count / $totalResellerTenants) * 100, 1) : 0;
            return [
                'slug' => $slug,
                'name' => $name,
                'count' => $count,
                'ratio' => $ratio,
                'color' => $moduleColors[$idx % count($moduleColors)],
            ];
        })->values();

        $totalActiveModulesCount = (int)$rawModuleCounts->sum('tenant_count');
        $avgModulesPerTenant = $totalResellerTenants > 0 ? round($totalActiveModulesCount / $totalResellerTenants, 1) : 0;

        // OPTIMIZED Entitlements, Features and Quotas Statistics
        // Group tenants by package and status to calculate in O(Packages) instead of O(Tenants x Definitions)
        $allDefinitions = TenantQuotaService::getDefinitions();
        $entitlements = $this->computeEntitlementsStats($tenantQuery, $allDefinitions, $totalResellerTenants);
        $featuresStats = $entitlements['featuresStats'];
        $limitsStats = $entitlements['limitsStats'];
        $totalFeaturesActiveCount = (int)$featuresStats->sum('count');
        $avgFeaturesPerTenant = $totalResellerTenants > 0 ? round($totalFeaturesActiveCount / $totalResellerTenants, 1) : 0;
        $limitedTenantsRatio = $limitsStats->first() ? $limitsStats->first()['limitedRatio'] : 0;

        //Package Distribution
        $packageRaw = (clone $tenantQuery)
            ->with('package:id,name,monthly_price,yearly_price,color')
            ->get(['id', 'package_id'])
            ->groupBy(function ($t) {
                return $t->package ? $t->package->name : (__('resellerhub::resellerhub.dashboard.packages.no_plan_custom') ?: 'بدون پلن / سفارشی');
            });

        $packageDistribution = collect();
        foreach ($packageRaw as $pkgName => $tenantsList) {
            $tCount = $tenantsList->count();
            $ratio = $totalResellerTenants > 0 ? round(($tCount / $totalResellerTenants) * 100, 1) : 0;
            $firstPkg = $tenantsList->first()->package;
            $color = $firstPkg ? $firstPkg->color_hex : '#64748b';

            $packageDistribution->push([
                'name' => $pkgName,
                'count' => $tCount,
                'ratio' => $ratio,
                'color' => $color,
            ]);
        }
        $packageDistribution = $packageDistribution->sortByDesc('count')->values();
        $defaultNoPlan = __('resellerhub::resellerhub.dashboard.packages.no_plan_custom') ?: 'بدون پلن / سفارشی';
        $topCommercialPackage = $packageDistribution->first(fn($p) => $p['name'] !== $defaultNoPlan) ?? $packageDistribution->first();

        // 10. Longevity / Retention Cohorts
        $allTenantsCreated = (clone $tenantQuery)->get(['id', 'created_at', 'status', 'plan_expires_at']);

        $cohorts = [
            'new' => ['label' => __('resellerhub::resellerhub.dashboard.cohorts.new_label') ?: 'کمتر از ۱ ماه', 'sublabel' => __('resellerhub::resellerhub.dashboard.cohorts.new_sublabel') ?: 'مشترکین نوپا', 'count' => 0, 'color' => '#10b981'],
            'onboarding' => ['label' => __('resellerhub::resellerhub.dashboard.cohorts.onboarding_label') ?: '۱ تا ۳ ماه', 'sublabel' => __('resellerhub::resellerhub.dashboard.cohorts.onboarding_sublabel') ?: 'تثبیت و استقرار', 'count' => 0, 'color' => '#3b82f6'],
            'established' => ['label' => __('resellerhub::resellerhub.dashboard.cohorts.established_label') ?: '۳ تا ۶ ماه', 'sublabel' => __('resellerhub::resellerhub.dashboard.cohorts.established_sublabel') ?: 'مشتریان پایدار', 'count' => 0, 'color' => '#8b5cf6'],
            'mature' => ['label' => __('resellerhub::resellerhub.dashboard.cohorts.mature_label') ?: '۶ تا ۱۲ ماه', 'sublabel' => __('resellerhub::resellerhub.dashboard.cohorts.mature_sublabel') ?: 'مشترکین وفادار', 'count' => 0, 'color' => '#f59e0b'],
            'veteran' => ['label' => __('resellerhub::resellerhub.dashboard.cohorts.veteran_label') ?: 'بیش از ۱ سال', 'sublabel' => __('resellerhub::resellerhub.dashboard.cohorts.veteran_sublabel') ?: 'مشتریان طلایی', 'count' => 0, 'color' => '#06b6d4'],
        ];

        $totalAgeDays = 0;
        $nowDate = now();

        foreach ($allTenantsCreated as $t) {
            $ageDays = $t->created_at ? $t->created_at->diffInDays($nowDate) : 0;
            $totalAgeDays += $ageDays;

            if ($ageDays < 30) {
                $cohorts['new']['count']++;
            } elseif ($ageDays < 90) {
                $cohorts['onboarding']['count']++;
            } elseif ($ageDays < 180) {
                $cohorts['established']['count']++;
            } elseif ($ageDays < 365) {
                $cohorts['mature']['count']++;
            } else {
                $cohorts['veteran']['count']++;
            }
        }

        $retentionCohorts = collect($cohorts)->map(function ($c) use ($totalResellerTenants) {
            $c['ratio'] = $totalResellerTenants > 0 ? round(($c['count'] / $totalResellerTenants) * 100, 1) : 0;
            return $c;
        });

        $avgFleetAgeDays = $totalResellerTenants > 0 ? round($totalAgeDays / $totalResellerTenants) : 0;
        $avgFleetAgeMonths = round($avgFleetAgeDays / 30, 1);

        return compact(
            'totalTenants',
            'activeTenants',
            'suspendedTenants',
            'pendingPaymentTenants',
            'expiredTenants',
            'expiringSoonTenants',
            'totalInvoices',
            'paidInvoicesCount',
            'totalRevenue',
            'unpaidInvoicesCount',
            'unpaidInvoicesTotal',
            'overdueInvoicesCount',
            'packagesCount',
            'activePackagesCount',
            'subAccountsCount',
            'activeOrdersCount',
            'totalOrdersCount',
            'onlineOrdersCount',
            'manualOrdersCount',
            'onlineOrdersRevenue',
            'manualOrdersRevenue',
            'onlineOrdersRatio',
            'manualOrdersRatio',
            'selectedPeriod',
            'selectedPeriodLabel',
            'periodLabels',
            'chartLabels',
            'tenantsCreatedSeries',
            'revenueSeries',
            'periodRevenue',
            'periodTenantsCreated',
            'prevPeriodRevenue',
            'prevPeriodTenants',
            'revenueGrowthPercent',
            'tenantsGrowthPercent',
            'activeRatio',
            'arpu',
            'pipelineData',
            'totalPipelineCount',
            'totalPipelineRevenue',
            'revenueComposition',
            'totalFinancialVolume',
            'collectionRate',
            'expiringTenants',
            'unpaidInvoices',
            'totalResellerTenants',
            'topModules',
            'totalActiveModulesCount',
            'avgModulesPerTenant',
            'featuresStats',
            'limitsStats',
            'totalFeaturesActiveCount',
            'avgFeaturesPerTenant',
            'limitedTenantsRatio',
            'packageDistribution',
            'topCommercialPackage',
            'retentionCohorts',
            'avgFleetAgeDays',
            'avgFleetAgeMonths'
        );
    }

    /**
     * Compute features & limits stats by aggregating per package and evaluating custom tenants.
     * Eliminates the N x M nested loop bottleneck.
     */
    protected function computeEntitlementsStats($tenantQuery, array $allDefinitions, int $totalResellerTenants): array
    {
        $featurePalette = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#06b6d4',
            '#10b981', '#3b82f6', '#14b8a6', '#f43f5e', '#84cc16'
        ];
        $limitPalette = [
            '#10b981', '#6366f1', '#f59e0b', '#8b5cf6', '#06b6d4',
            '#14b8a6', '#f43f5e', '#3b82f6'
        ];

        $featureDefs = [];
        $limitDefs = [];
        foreach ($allDefinitions as $key => $def) {
            if (($def['type'] ?? '') === 'feature') {
                $featureDefs[$key] = $def;
            } elseif (($def['type'] ?? '') === 'quota') {
                $limitDefs[$key] = $def;
            }
        }

        $allTenants = (clone $tenantQuery)->with('package')->get(['id', 'reseller_id', 'package_id', 'settings', 'status', 'plan_expires_at']);

        // Group tenants by (has_active_plan ? package_id : 'unpaid')
        // Tenants with custom settings overrides are processed individually
        $featureCounts = array_fill_keys(array_keys($featureDefs), 0);
        $limitStats = [];
        foreach (array_keys($limitDefs) as $lKey) {
            $limitStats[$lKey] = ['limitedCount' => 0, 'unlimitedCount' => 0, 'totalCeiling' => 0];
        }

        // Cache package calculations so each package is evaluated exactly ONCE
        $packageFeatureCache = [];
        $packageLimitCache = [];

        foreach ($allTenants as $tenant) {
            $hasActivePlan = $tenant->hasActivePaidPlan();
            $hasCustomSettings = !empty($tenant->settings['features']) || !empty($tenant->settings['quotas']);

            if ($hasCustomSettings || !$tenant->package_id) {
                // Evaluate custom / no-package tenant individually
                foreach ($featureDefs as $fKey => $fDef) {
                    $qKey = QuotaKey::resolve($fKey);
                    if ($this->quotaService->hasFeature($tenant, $qKey ?: $fKey)) {
                        $featureCounts[$fKey]++;
                    }
                }
                foreach ($limitDefs as $lKey => $lDef) {
                    $qKey = QuotaKey::resolve($lKey);
                    $limit = $this->quotaService->getLimit($tenant, $qKey ?: $lKey);
                    if ($limit === null) {
                        $limitStats[$lKey]['unlimitedCount']++;
                    } else {
                        $limitStats[$lKey]['limitedCount']++;
                        $limitStats[$lKey]['totalCeiling'] += $limit;
                    }
                }
                continue;
            }

            if (!$hasActivePlan) {
                // Inactive/expired plan has no active features; limits default to 0
                foreach (array_keys($limitDefs) as $lKey) {
                    $limitStats[$lKey]['limitedCount']++;
                }
                continue;
            }

            $pkgId = (int)$tenant->package_id;
            if (!isset($packageFeatureCache[$pkgId])) {
                $packageFeatureCache[$pkgId] = [];
                foreach ($featureDefs as $fKey => $fDef) {
                    $qKey = QuotaKey::resolve($fKey);
                    $packageFeatureCache[$pkgId][$fKey] = $this->quotaService->hasFeature($tenant, $qKey ?: $fKey);
                }

                $packageLimitCache[$pkgId] = [];
                foreach ($limitDefs as $lKey => $lDef) {
                    $qKey = QuotaKey::resolve($lKey);
                    $packageLimitCache[$pkgId][$lKey] = $this->quotaService->getLimit($tenant, $qKey ?: $lKey);
                }
            }

            foreach ($featureDefs as $fKey => $fDef) {
                if ($packageFeatureCache[$pkgId][$fKey]) {
                    $featureCounts[$fKey]++;
                }
            }

            foreach ($limitDefs as $lKey => $lDef) {
                $limit = $packageLimitCache[$pkgId][$lKey];
                if ($limit === null) {
                    $limitStats[$lKey]['unlimitedCount']++;
                } else {
                    $limitStats[$lKey]['limitedCount']++;
                    $limitStats[$lKey]['totalCeiling'] += $limit;
                }
            }
        }

        // Build featuresStats collection
        $featuresStats = collect();
        $fIdx = 0;
        foreach ($featureDefs as $fKey => $fDef) {
            $activeCount = $featureCounts[$fKey] ?? 0;
            $featuresStats->push([
                'key' => $fKey,
                'name' => $fDef['title'],
                'module_title' => $fDef['module_title'] ?? ResellerHelper::getModuleTitle($fDef['module'] ?? ''),
                'icon' => $fDef['icon'] ?? 'star',
                'color' => $featurePalette[$fIdx % count($featurePalette)],
                'count' => $activeCount,
                'ratio' => $totalResellerTenants > 0 ? round(($activeCount / $totalResellerTenants) * 100, 1) : 0,
            ]);
            $fIdx++;
        }

        // Build limitsStats collection
        $limitsStatsColl = collect();
        $lIdx = 0;
        foreach ($limitDefs as $lKey => $lDef) {
            $ls = $limitStats[$lKey];
            $limitedCount = $ls['limitedCount'];
            $unlimitedCount = $ls['unlimitedCount'];
            $totalCeiling = $ls['totalCeiling'];

            $limitsStatsColl->push([
                'key' => $lKey,
                'name' => $lDef['title'],
                'unit' => $lDef['unit'] ?? (__('resellerhub::resellerhub.services.quota.item_unit') ?: 'مورد'),
                'module_title' => $lDef['module_title'] ?? ResellerHelper::getModuleTitle($lDef['module'] ?? ''),
                'icon' => $lDef['icon'] ?? 'layers',
                'color' => $limitPalette[$lIdx % count($limitPalette)],
                'limitedCount' => $limitedCount,
                'unlimitedCount' => $unlimitedCount,
                'limitedRatio' => $totalResellerTenants > 0 ? round(($limitedCount / $totalResellerTenants) * 100, 1) : 0,
                'unlimitedRatio' => $totalResellerTenants > 0 ? round(($unlimitedCount / $totalResellerTenants) * 100, 1) : 0,
                'avgCeiling' => $limitedCount > 0 ? round($totalCeiling / $limitedCount, 1) : 0,
            ]);
            $lIdx++;
        }

        return [
            'featuresStats' => $featuresStats,
            'limitsStats' => $limitsStatsColl,
        ];
    }
}
