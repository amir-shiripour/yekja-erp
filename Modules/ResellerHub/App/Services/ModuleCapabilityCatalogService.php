<?php

namespace Modules\ResellerHub\App\Services;

use App\Models\Module;
use App\Services\Modules\ModuleMenuService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerModulePricing;

class ModuleCapabilityCatalogService
{
    protected ModuleMenuService $menuService;

    public function __construct(ModuleMenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Get catalog by Reseller model or Reseller ID.
     */
    public function getResellerCatalog($reseller): Collection
    {
        if (is_numeric($reseller)) {
            $reseller = Reseller::find((int)$reseller);
        }

        if (!$reseller instanceof Reseller) {
            return collect([]);
        }

        return $this->getCatalogForReseller($reseller);
    }

    /**
     * Commercial features & quotas for target modules.
     * Only booking, clients, market & services have sub-features.
     * All other modules return an empty array.
     */
    public function getModuleCommercialFeatures(string $slug): array
    {
        $keys = QuotaKey::forModule($slug);
        if (empty($keys)) {
            return [];
        }

        return array_map(fn(QuotaKey $key) => [
            'key' => $key->value,
            'title' => $key->title(),
            'type' => $key->isFeature() ? 'feature' : 'quota',
            'unit' => $key->unit(),
            'description' => $key->description(),
        ], $keys);
    }

    /**
     * Get the full catalog of modules and capabilities across the system,
     * optionally populated with reseller-specific pricing if a reseller is provided.
     */
    public function getFullCatalog(?Reseller $reseller = null): Collection
    {
        if ($reseller instanceof Reseller) {
            return $this->getCatalogForReseller($reseller);
        }

        $systemModules = Module::where('installed', true)
            ->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'admin', 'core', 'settings'])
            ->get();

        return $systemModules->map(function ($mod) {
            $slug = strtolower(trim($mod->slug));
            $title = $this->menuService->resolveModuleGroupTitle($slug, $mod->name) ?: $mod->name;
            $commercialFeatures = $this->getModuleCommercialFeatures($slug);

            $features = collect($commercialFeatures)->map(function ($f) {
                return [
                    'key' => $f['key'],
                    'title' => $f['title'],
                    'type' => $f['type'] ?? 'feature',
                    'unit' => $f['unit'] ?? null,
                    'description' => $f['description'] ?? '',
                    'monthly_price' => 0,
                    'quarterly_price' => 0,
                    'semi_annual_price' => 0,
                    'yearly_price' => 0,
                    'setup_fee' => 0,
                    'is_active' => true,
                    'pricing_id' => null,
                ];
            })->values();

            return [
                'name' => $mod->name,
                'slug' => $slug,
                'title' => $title,
                'description' => $mod->description ?? '',
                'is_allowed' => true,
                'base_monthly_price' => 0,
                'base_quarterly_price' => 0,
                'base_semi_annual_price' => 0,
                'base_yearly_price' => 0,
                'base_setup_fee' => 0,
                'is_active' => true,
                'base_pricing_id' => null,
                'features' => $features,
                'features_count' => $features->count(),
            ];
        })->values();
    }

    /**
     * Get the full catalog of modules and their commercial capabilities with reseller pricing.
     */
    public function getCatalogForReseller(Reseller $reseller): Collection
    {
        $systemModules = Module::where('installed', true)
            ->where('active', true)
            ->whereNotIn('slug', ['resellerhub', 'admin', 'core', 'settings'])
            ->get();

        $existingPricings = ResellerModulePricing::where('reseller_id', $reseller->id)
            ->get()
            ->keyBy(function ($p) {
                return $p->module_slug . ':' . ($p->feature_key ?? '__base__');
            });

        return $systemModules->map(function ($mod) use ($reseller, $existingPricings) {
            $slug = strtolower(trim($mod->slug));
            $title = $this->menuService->resolveModuleGroupTitle($slug, $mod->name) ?: $mod->name;
            $isAllowed = $reseller->canSellModule($slug);
            $commercialFeatures = $this->getModuleCommercialFeatures($slug);

            // Base module pricing
            $basePricing = $existingPricings->get($slug . ':__base__');

            // Features list
            $features = collect($commercialFeatures)->map(function ($f) use ($slug, $existingPricings) {
                $featurePricing = $existingPricings->get($slug . ':' . $f['key']);

                return [
                    'key' => $f['key'],
                    'title' => $f['title'],
                    'type' => $f['type'] ?? 'feature',
                    'unit' => $f['unit'] ?? null,
                    'description' => $f['description'] ?? '',
                    'monthly_price' => $featurePricing ? (int)$featurePricing->monthly_price : 0,
                    'quarterly_price' => $featurePricing ? (int)$featurePricing->quarterly_price : 0,
                    'semi_annual_price' => $featurePricing ? (int)$featurePricing->semi_annual_price : 0,
                    'yearly_price' => $featurePricing ? (int)$featurePricing->yearly_price : 0,
                    'setup_fee' => $featurePricing ? (int)$featurePricing->setup_fee : 0,
                    'is_active' => $featurePricing ? (bool)$featurePricing->is_active : true,
                    'pricing_id' => $featurePricing ? $featurePricing->id : null,
                ];
            })->values();

            return [
                'name' => $mod->name,
                'slug' => $slug,
                'title' => $title,
                'description' => $mod->description ?? '',
                'is_allowed' => $isAllowed,
                'base_monthly_price' => $basePricing ? (int)$basePricing->monthly_price : 0,
                'base_quarterly_price' => $basePricing ? (int)$basePricing->quarterly_price : 0,
                'base_semi_annual_price' => $basePricing ? (int)$basePricing->semi_annual_price : 0,
                'base_yearly_price' => $basePricing ? (int)$basePricing->yearly_price : 0,
                'base_setup_fee' => $basePricing ? (int)$basePricing->setup_fee : 0,
                'is_active' => $basePricing ? (bool)$basePricing->is_active : true,
                'base_pricing_id' => $basePricing ? $basePricing->id : null,
                'features' => $features,
                'features_count' => $features->count(),
            ];
        })->values();
    }

    /**
     * Save/Update pricing matrix for a reseller.
     */
    public function savePricings(Reseller $reseller, array $pricings): int
    {
        $savedCount = 0;

        foreach ($pricings as $item) {
            $moduleSlug = strtolower(trim($item['module_slug']));
            $featureKey = !empty($item['feature_key']) ? trim($item['feature_key']) : null;
            $featureTitle = !empty($item['feature_title']) ? trim($item['feature_title']) : null;

            ResellerModulePricing::updateOrCreate(
                [
                    'reseller_id' => $reseller->id,
                    'module_slug' => $moduleSlug,
                    'feature_key' => $featureKey,
                ],
                [
                    'feature_title' => $featureTitle,
                    'monthly_price' => max(0, (int)($item['monthly_price'] ?? 0)),
                    'quarterly_price' => max(0, (int)($item['quarterly_price'] ?? 0)),
                    'semi_annual_price' => max(0, (int)($item['semi_annual_price'] ?? 0)),
                    'yearly_price' => max(0, (int)($item['yearly_price'] ?? 0)),
                    'setup_fee' => max(0, (int)($item['setup_fee'] ?? 0)),
                    'is_active' => isset($item['is_active']) ? (bool)$item['is_active'] : true,
                    'description' => $item['description'] ?? null,
                ]
            );

            $savedCount++;
        }

        return $savedCount;
    }
}
