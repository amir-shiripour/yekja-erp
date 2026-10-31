<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\SaveModulePricingsRequest;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Traits\HasReseller;

class ResellerPricingController extends Controller
{
    use HasReseller;

    protected ModuleCapabilityCatalogService $catalogService;

    public function __construct(ModuleCapabilityCatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index(): View
    {
        $reseller = $this->getReseller();
        $settings = ResellerSetting::allForReseller($reseller->id);
        $catalog = $this->catalogService->getCatalogForReseller($reseller);
        $currency = $settings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);
        $enableModuleItemizedPricing = (bool)($settings['enable_module_itemized_pricing'] ?? false);

        return view('resellerhub::user.pricings.index', compact(
            'reseller',
            'settings',
            'catalog',
            'currency',
            'currencyLabel',
            'enableModuleItemizedPricing'
        ));
    }

    public function store(SaveModulePricingsRequest $request): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();
        $validated = $request->validated();

        $savedCount = $this->catalogService->savePricings($reseller, $validated['pricings']);

        if ($request->has('enable_module_itemized_pricing')) {
            ResellerSetting::set($reseller->id, 'enable_module_itemized_pricing', $request->boolean('enable_module_itemized_pricing'));
        }

        $message = __('resellerhub::messages.pricing.saved_success', ['count' => $savedCount]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
            ]);
        }

        return redirect()->route('reseller.pricings.index')->with('success', $message);
    }

    public function toggleItemized(Request $request): JsonResponse
    {
        $reseller = $this->getReseller();
        $enabled = $request->boolean('enable_module_itemized_pricing');
        ResellerSetting::set($reseller->id, 'enable_module_itemized_pricing', $enabled);

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled
                ? __('resellerhub::messages.pricing.itemized_enabled')
                : __('resellerhub::messages.pricing.itemized_disabled'),
        ]);
    }
}