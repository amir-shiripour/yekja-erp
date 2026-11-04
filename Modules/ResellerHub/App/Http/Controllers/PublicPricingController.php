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
        $packages = ResellerPackage::active()->orderBy('monthly_price')->paginate(9);

        $availablePeriods = ResellerHelper::getAllAvailablePeriods();
        $defaultPeriod = 'monthly_price';

        // Fetch all active packages (un-paginated) for the comparison matrix
        $allPackages = ResellerPackage::active()->orderBy('sort_order')->orderBy('monthly_price')->get();

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
    private function buildComparisonData($packages, array $availablePeriods = []): array
    {
        if ($packages->isEmpty()) {
            return [];
        }

        $allModuleSlugs = $packages
            ->flatMap(fn(ResellerPackage $p) => is_array($p->modules) ? $p->modules : [])
            ->unique()
            ->sort()
            ->values();

        $allQuotaKeys = collect(QuotaKey::cases())->filter(fn(QuotaKey $q) => !$q->isFeature());
        $allFeatureKeys = collect(QuotaKey::cases())->filter(fn(QuotaKey $q) => $q->isFeature());
        $usedQuotaSlugs = $packages
            ->flatMap(fn(ResellerPackage $p) => array_keys(is_array($p->limits) ? $p->limits : []))
            ->unique()->values();

        $usedFeatureSlugs = $packages
            ->flatMap(fn(ResellerPackage $p) => is_array($p->features) ? $p->features : [])
            ->unique()->values();

        $quotaRows = $allQuotaKeys->filter(
            fn(QuotaKey $q) => $usedQuotaSlugs->contains($q->value)
        )->values();

        $featureRows = $allFeatureKeys->filter(
            fn(QuotaKey $q) => $usedFeatureSlugs->contains($q->value)
        )->values();

        $periodRows = collect($availablePeriods ?? ResellerHelper::getAllAvailablePeriods())
            ->map(fn($pData, $pKey) => [
                'key' => $pKey,
                'label' => $pData['label'],
                'months' => $pData['months'] ?? ResellerPackage::resolveMonthFromPeriodKey($pKey),
            ])
            ->values()
            ->toArray();

        $groups = [];

        if ($allModuleSlugs->isNotEmpty()) {
            $rows = [];
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

                $rows[] = [
                    'slug' => $slug,
                    'label' => $label,
                    'description' => $description,
                    'type' => 'module',
                    'unit' => null,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $groups[] = [
                'category' => 'modules',
                'label' => __('resellerhub::messages.comparison.group_modules'),
                'rows' => $rows,
            ];
        }

        if ($quotaRows->isNotEmpty()) {
            $rows = [];
            foreach ($quotaRows as $quota) {
                $values = [];
                $numericVals = [];
                foreach ($packages as $pkg) {
                    $val = $pkg->getLimit($quota);
                    $values[$pkg->id] = $val;
                    if ($val !== null && $val > 0) {
                        $numericVals[] = (int)$val;
                    }
                }

                $maxQuota = !empty($numericVals) ? max($numericVals) : 100;
                $hasDiff = count(array_unique(array_values($values))) > 1;

                $rows[] = [
                    'slug' => $quota->value,
                    'label' => $quota->shortTitle(),
                    'description' => $quota->description(),
                    'type' => 'quota',
                    'unit' => $quota->unit(),
                    'max_quota' => $maxQuota,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $groups[] = [
                'category' => 'quotas',
                'label' => __('resellerhub::messages.comparison.group_quotas'),
                'rows' => $rows,
            ];
        }

        if ($featureRows->isNotEmpty()) {
            $rows = [];
            foreach ($featureRows as $feat) {
                $values = [];
                foreach ($packages as $pkg) {
                    $values[$pkg->id] = in_array($feat->value, is_array($pkg->features) ? $pkg->features : [], true);
                }

                $hasDiff = count(array_unique(array_values($values))) > 1;

                $rows[] = [
                    'slug' => $feat->value,
                    'label' => $feat->shortTitle(),
                    'description' => $feat->description(),
                    'type' => 'feature',
                    'unit' => null,
                    'has_difference' => $hasDiff,
                    'values' => $values,
                ];
            }
            $groups[] = [
                'category' => 'features',
                'label' => __('resellerhub::messages.comparison.group_features'),
                'rows' => $rows,
            ];
        }

        return [
            'groups' => $groups,
            'periodRows' => $periodRows,
        ];
    }
}
