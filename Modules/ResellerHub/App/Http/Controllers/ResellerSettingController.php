<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use App\Tenancy\TenancyModelRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Modules\ResellerHub\App\Actions\Settings\UpdateResellerSettingsAction;
use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerSettingsRequest;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Throwable;

class ResellerSettingController extends Controller
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
        $resellerPackages = ResellerPackage::where('reseller_id', $reseller->id)->orderBy('sort_order')->get();
        $subAccountCustomFields = CustomUserField::where('entity_type', CustomUserField::ENTITY_SUB_ACCOUNT)->get();
        $tenancyHealth = TenancyModelRegistry::getHealthReport();
        $discoveredModels = TenancyModelRegistry::discoverAvailableModels();

        return view('resellerhub::user.settings.index', compact(
            'reseller',
            'settings',
            'catalog',
            'currency',
            'currencyLabel',
            'resellerPackages',
            'subAccountCustomFields',
            'tenancyHealth',
            'discoveredModels'
        ));
    }

    public function update(
        UpdateResellerSettingsRequest $request,
        UpdateResellerSettingsAction  $action
    ): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        $action->execute($reseller, $request->validated(), $request);

        $activeTab = (string)$request->input('active_tab', 'general');

        if ($request->expectsJson() || $request->ajax()) {
            $sealPath = ResellerSetting::get($reseller->id, ResellerSettingKey::BrandSealSignature);
            return response()->json([
                'success' => true,
                'message' => __('resellerhub::messages.settings.saved_success'),
                'brand_logo_url' => $reseller->brand_logo ? asset('storage/' . $reseller->brand_logo) : null,
                'brand_favicon_url' => $reseller->brand_favicon ? asset('storage/' . $reseller->brand_favicon) : null,
                'brand_seal_signature_url' => $sealPath ? asset('storage/' . $sealPath) : null,
                'allow_custom_domains' => (bool)ResellerSetting::get($reseller->id, ResellerSettingKey::AllowCustomDomains, true),
                'onboarding_tour_enabled' => (bool)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourEnabled, true),
                'onboarding_tour_steps' => ResellerSetting::getOnboardingTourSteps($reseller->id),
                'onboarding_tour_display_mode' => ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourDisplayMode, 'bento'),
                'onboarding_tour_enforcement' => ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourEnforcement, 'soft'),
                'onboarding_tour_grace_period_days' => (int)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourGracePeriodDays, 3),
                'onboarding_tour_celebration_title' => (string)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourCelebrationTitle, ''),
                'onboarding_tour_celebration_message' => (string)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourCelebrationMessage, ''),
            ]);
        }

        return redirect()->route('reseller.settings.index', ['tab' => $activeTab])
            ->with('success', __('resellerhub::messages.settings.saved_success'))
            ->with('active_tab', $activeTab);
    }

    public function seedWorkflows(): RedirectResponse
    {
        try {
            Artisan::call('reseller:seed-workflows');
            return back()->with('success', __('resellerhub::messages.workflows.seed_success'))
                ->with('active_tab', 'automation');
        } catch (Throwable $e) {
            return back()->with('error', __('resellerhub::messages.workflows.seed_error', ['error' => $e->getMessage()]))
                ->with('active_tab', 'automation');
        }
    }

    public function syncTenancy(Request $request): JsonResponse|RedirectResponse
    {
        try {
            TenancyModelRegistry::flushBound();
            TenancyModelRegistry::bootAll();
            $health = TenancyModelRegistry::getHealthReport();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'همگام‌سازی و تحلیل سلامت مدل‌های چندمستاجری با موفقیت انجام شد.',
                    'health' => $health,
                ]);
            }

            return back()->with('success', 'همگام‌سازی و تحلیل سلامت مدل‌های چندمستاجری با موفقیت انجام شد.')
                ->with('active_tab', 'tenancy');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در همگام‌سازی: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'خطا در همگام‌سازی: ' . $e->getMessage())
                ->with('active_tab', 'tenancy');
        }
    }

    public function addTenancyModel(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'model_class' => ['required', 'string', 'max:255'],
        ]);

        $modelClass = trim($validated['model_class'], "\\ \t\n\r\0\x0B");

        if (!class_exists($modelClass)) {
            $msg = "کلاس {$modelClass} یافت نشد. لطفاً نام کامل کلاس با Namespace را بررسی کنید.";
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg)->with('active_tab', 'tenancy');
        }

        TenancyModelRegistry::addModel($modelClass);

        $msg = "مدل {$modelClass} با موفقیت به رجیستری چندمستاجری اضافه شد و تحت پوشش اسکوپ قرار گرفت.";
        return $request->expectsJson() || $request->ajax()
            ? response()->json(['success' => true, 'message' => $msg, 'health' => TenancyModelRegistry::getHealthReport()])
            : back()->with('success', $msg)->with('active_tab', 'tenancy');
    }

    public function removeTenancyModel(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'model_class' => ['required', 'string', 'max:255'],
        ]);

        $modelClass = trim($validated['model_class'], "\\ \t\n\r\0\x0B");

        if (TenancyModelRegistry::isSystemModel($modelClass)) {
            $msg = "موجودیت‌های پیش‌فرض هسته سامانه به دلیل وابستگی منطق پایه CRM قابل حذف نیستند.";
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg)->with('active_tab', 'tenancy');
        }

        TenancyModelRegistry::removeModel($modelClass);

        $msg = "مدل {$modelClass} با موفقیت از رجیستری پویا حذف شد.";
        return $request->expectsJson() || $request->ajax()
            ? response()->json(['success' => true, 'message' => $msg, 'health' => TenancyModelRegistry::getHealthReport()])
            : back()->with('success', $msg)->with('active_tab', 'tenancy');
    }
}
