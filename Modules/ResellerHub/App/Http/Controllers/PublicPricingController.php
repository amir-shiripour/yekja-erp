<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerPackage;

class PublicPricingController extends Controller
{
    /**
     * Display public package pricing page.
     */
    public function index(): View
    {
        $packages = ResellerPackage::active()->orderBy('sort_order')->orderBy('monthly_price')->get();

        $availablePeriods = ResellerHelper::getAllAvailablePeriods();
        $defaultPeriod = 'monthly_price';

        // Fetch all active packages for the comparison matrix
        $allPackages = $packages;

        // Pre-compute structured periods per package (for monthly rates, discounts, etc.)
        $packagePeriods = [];
        foreach ($allPackages as $pkg) {
            $packagePeriods[$pkg->id] = $pkg->getPackagePeriods();
        }

        $comparisonData = $this->buildComparisonData($allPackages, $availablePeriods);

        return view('resellerhub::public.pricing', compact(
            'packages',
            'availablePeriods',
            'defaultPeriod',
            'allPackages',
            'comparisonData',
            'packagePeriods',
        ));
    }

    /**
     * Build the structured comparison rows grouped by category.
     * @param Collection<int, ResellerPackage> $packages
     */
    private function buildComparisonData(Collection $packages, array $availablePeriods = []): array
    {
        if ($packages->isEmpty()) {
            return [];
        }

        // Config-driven buyer-oriented priorities for modules (CRM & Finance first, then Scheduling, etc.)
        $modulePriorities = config('resellerhub.comparison_module_priorities', []);

        $allModuleSlugs = $packages
            ->flatMap(static fn(ResellerPackage $p) => is_array($p->modules) ? $p->modules : [])
            ->unique()
            ->sortBy(static fn(string $slug) => $modulePriorities[$slug] ?? 999)
            ->values();

        $allQuotaKeys = collect(QuotaKey::cases())->filter(static fn(QuotaKey $q) => $q->isNumeric());
        $allFeatureKeys = collect(QuotaKey::cases())->filter(static fn(QuotaKey $q) => $q->isFeature());
        $usedQuotaSlugs = $packages
            ->flatMap(static fn(ResellerPackage $p) => array_keys(is_array($p->limits) ? $p->limits : []))
            ->unique()->values();

        $usedFeatureSlugs = $packages
            ->flatMap(static fn(ResellerPackage $p) => is_array($p->features) ? $p->features : [])
            ->unique()->values();

        $quotaRows = $allQuotaKeys
            ->filter(static fn(QuotaKey $q) => $usedQuotaSlugs->contains($q->value))
            ->sortBy(static fn(QuotaKey $q) => $q->priority())
            ->values();

        $featureRows = $allFeatureKeys
            ->filter(static fn(QuotaKey $q) => $usedFeatureSlugs->contains($q->value))
            ->sortBy(static fn(QuotaKey $q) => $q->priority())
            ->values();

        $periodRows = collect($availablePeriods ?? ResellerHelper::getAllAvailablePeriods())
            ->map(static fn($pData, $pKey) => [
                'key' => $pKey,
                'label' => $pData['label'],
                'months' => $pData['months'] ?? ResellerPackage::resolveMonthFromPeriodKey($pKey),
            ])
            ->values()
            ->toArray();

        // 4. Build Quotas & Capabilities group rows
        $packageIds = $packages->pluck('id')->values()->all();
        $sortRowsByAdoption = static function (array &$rows) use ($packageIds, $modulePriorities) {
            foreach ($rows as &$r) {
                $type = $r['type'] ?? 'module';
                $slug = $r['slug'] ?? '';
                $values = $r['values'] ?? [];

                $activeCount = 0;
                $patternScore = 0;
                foreach ($packageIds as $idx => $pkgId) {
                    $val = $values[$pkgId] ?? null;
                    $isActive = ($type === 'quota') ? ($val !== null) : ($val === true);
                    if ($isActive) {
                        $activeCount++;
                        $patternScore += (1 << $idx);
                    }
                }

                $priority = match ($type) {
                    'quota', 'feature' => QuotaKey::tryFrom($slug)?->priority() ?? 999,
                    'module' => $modulePriorities[$slug] ?? 999,
                    default => 999,
                };

                $r['active_count'] = $activeCount;
                $r['pattern_score'] = $patternScore;
                $r['priority'] = $priority;
            }
            unset($r);

            usort($rows, static function ($a, $b) {
                if ($a['active_count'] !== $b['active_count']) {
                    return $b['active_count'] <=> $a['active_count']; // DESC
                }
                if ($a['pattern_score'] !== $b['pattern_score']) {
                    return $b['pattern_score'] <=> $a['pattern_score']; // DESC
                }
                if ($a['priority'] !== $b['priority']) {
                    return $a['priority'] <=> $b['priority']; // ASC
                }
                return strcmp($a['label'] ?? '', $b['label'] ?? '');
            });
        };

        $quotaRowsList = [];
        if ($quotaRows->isNotEmpty()) {
            foreach ($quotaRows as $quota) {
                $values = [];
                $numericVals = [];
                foreach ($packages as $pkg) {
                    $val = $pkg->getLimit($quota);
                    $values[$pkg->id] = $val;
                    if ($val !== null && $val > 0) {
                        $numericVals[] = $val;
                    }
                }

                $maxQuota = !empty($numericVals) ? max($numericVals) : 100;
                $hasDiff = count(array_unique(array_values($values))) > 1;

                $quotaRowsList[] = [
                    'slug' => $quota->value,
                    'label' => $quota->shortTitle(),
                    'description' => trim(str_replace(['(خالی = نامحدود)', '(خالی=نامحدود)'], '', $quota->description())),
                    'type' => 'quota',
                    'unit' => $quota->unit(),
                    'max_quota' => $maxQuota,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $sortRowsByAdoption($quotaRowsList);
        }

        $featureRowsList = [];
        if ($featureRows->isNotEmpty()) {
            foreach ($featureRows as $feat) {
                $values = [];
                foreach ($packages as $pkg) {
                    $values[$pkg->id] = in_array($feat->value, is_array($pkg->features) ? $pkg->features : [], true);
                }

                $hasDiff = count(array_unique(array_values($values))) > 1;

                $featureRowsList[] = [
                    'slug' => $feat->value,
                    'label' => $feat->shortTitle(),
                    'description' => $feat->description(),
                    'type' => 'feature',
                    'unit' => null,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $sortRowsByAdoption($featureRowsList);
        }

        $quotaAndFeatureRows = array_merge($quotaRowsList, $featureRowsList);

        // 5. Build Modules group rows (sorted strictly by package adoption DESC)
        $moduleRows = [];
        if ($allModuleSlugs->isNotEmpty()) {
            foreach ($allModuleSlugs as $slug) {
                $values = [];
                foreach ($packages as $pkg) {
                    $values[$pkg->id] = in_array($slug, is_array($pkg->modules) ? $pkg->modules : [], true);
                }
                $quotaEnum = QuotaKey::tryFrom($slug);
                $label = $quotaEnum?->shortTitle()
                    ?? match ($slug) {
                        'contracts' => 'قراردادها',
                        'invoices' => 'فاکتورها و صورتحساب‌ها',
                        default => ResellerHelper::getModuleTitle($slug),
                    };

                $description = $quotaEnum?->description() ?? '';
                $hasDiff = count(array_unique(array_values($values))) > 1;

                $moduleRows[] = [
                    'slug' => $slug,
                    'label' => $label,
                    'description' => $description,
                    'type' => 'module',
                    'unit' => null,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $sortRowsByAdoption($moduleRows);
        }

        // 6. Group ordering: Quotas & Capabilities first (core decision drivers), then Active Modules
        $groups = [];

        if (!empty($quotaAndFeatureRows)) {
            $groups[] = [
                'category' => 'quotas_and_features',
                'label' => __('resellerhub::messages.comparison.group_quotas_and_features'),
                'rows' => $quotaAndFeatureRows,
            ];
        }

        if (!empty($moduleRows)) {
            $groups[] = [
                'category' => 'modules',
                'label' => __('resellerhub::messages.comparison.group_modules'),
                'rows' => $moduleRows,
            ];
        }

        return [
            'groups' => $groups,
            'periodRows' => $periodRows,
        ];
    }
}
