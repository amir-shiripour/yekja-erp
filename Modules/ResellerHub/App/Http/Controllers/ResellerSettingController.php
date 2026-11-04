<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use App\Models\Tenant;
use App\Tenancy\TenancyModelRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\ResellerHub\App\Actions\Settings\UpdateResellerSettingsAction;
use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\UpdateResellerSettingsRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\ModuleCapabilityCatalogService;
use Modules\ResellerHub\App\Services\TenantDataExporterService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Throwable;

/**
 * @noinspection SqlResolve
 */
class ResellerSettingController extends Controller
{
    use HasReseller;

    protected ModuleCapabilityCatalogService $catalogService;

    public function __construct(ModuleCapabilityCatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index(Request $request, TenantDataExporterService $exporter): View
    {
        $reseller = $this->getReseller();
        $settings = ResellerSetting::allForReseller($reseller->id);
        $catalog = $this->catalogService->getCatalogForReseller($reseller);
        $currency = $settings['currency'] ?? 'toman';
        $currencyLabel = ResellerHelper::currencyLabel($currency);
        $resellerPackages = ResellerPackage::where('reseller_id', $reseller->id)->orderBy('sort_order')->get();
        $subAccountCustomFields = (Schema::hasTable('custom_user_fields') && Schema::hasColumn('custom_user_fields', 'entity_type'))
            ? CustomUserField::where('entity_type', CustomUserField::ENTITY_SUB_ACCOUNT)->get()
            : collect();
        $tenancyHealth = TenancyModelRegistry::getHealthReport();
        $discoveredModels = TenancyModelRegistry::discoverAvailableModels();

        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        $exportTenants = collect();
        $exportTenantsData = [];
        $exportResellersList = collect();
        $exportTotalTenants = 0;
        $exportActiveTenants = 0;
        $exportCustomDomainCount = 0;
        $exportSearch = trim((string)$request->query('search', ''));
        $exportStatus = $request->query('status');
        $exportResellerId = $request->query('reseller_id');
        $exportSortBy = $request->query('sort_by', 'latest');
        $exportSortOrder = strtolower((string)$request->query('sort_order', 'desc'));

        if ($isSuperAdmin) {
            $baseQuery = Tenant::query();
            $exportResellersList = Reseller::with('user')->orderBy('company_name')->get();

            if ($exportResellerId && $exportResellerId !== 'all') {
                $baseQuery->where('reseller_id', $exportResellerId);
            }

            // Summary Counters before search/status filter
            $exportTotalTenants = (clone $baseQuery)->count();
            $exportActiveTenants = (clone $baseQuery)->where('status', Tenant::STATUS_ACTIVE)->count();
            $exportCustomDomainCount = (clone $baseQuery)->whereNotNull('custom_domain')
                ->where('custom_domain', '!=', '')->count();

            // Search Filter
            if ($exportSearch !== '') {
                $term = '%' . $exportSearch . '%';
                $baseQuery->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('subdomain', 'like', $term)
                        ->orWhere('custom_domain', 'like', $term)
                        ->orWhereHas('subAccount', function ($sq) use ($term) {
                            $sq->where('name', 'like', $term)
                                ->orWhere('company_name', 'like', $term);
                        });
                });
            }

            // Status Filter
            if ($exportStatus && in_array($exportStatus, [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_EXPIRED], true)) {
                $baseQuery->where('status', $exportStatus);
            }

            // Sorting
            if ($exportSortBy === 'oldest') {
                $baseQuery->orderBy('id', 'asc');
            } elseif ($exportSortBy === 'name_asc') {
                $baseQuery->orderBy('name', 'asc');
            } elseif ($exportSortBy === 'name_desc') {
                $baseQuery->orderBy('name', 'desc');
            } elseif ($exportSortBy === 'created_at_asc') {
                $baseQuery->orderBy('created_at', 'asc');
            } else {
                $baseQuery->orderBy('id', 'desc');
            }

            $perPage = (int)$request->query('per_page', 10);
            if ($perPage < 5 || $perPage > 100) {
                $perPage = 10;
            }

            $exportTenants = $baseQuery->with(['subAccount.user', 'reseller.user', 'package'])
                ->paginate($perPage, ['*'], 'export_page')
                ->withQueryString();

            $exportTenantsData = $exporter->getStatisticsForMultipleTenants($exportTenants->items());
        }

        return view('resellerhub::user.settings.index', compact(
            'reseller',
            'settings',
            'catalog',
            'currency',
            'currencyLabel',
            'resellerPackages',
            'subAccountCustomFields',
            'tenancyHealth',
            'discoveredModels',
            'isSuperAdmin',
            'exportTenants',
            'exportTenantsData',
            'exportResellersList',
            'exportTotalTenants',
            'exportActiveTenants',
            'exportCustomDomainCount',
            'exportSearch',
            'exportStatus',
            'exportResellerId',
            'exportSortBy',
            'exportSortOrder'
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
                'onboarding_tour_optional_reminder_enabled' => (bool)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourOptionalReminderEnabled, true),
                'onboarding_tour_optional_reminder_interval_days' => (int)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourOptionalReminderIntervalDays, 3),
                'onboarding_tour_optional_reminder_mode' => (string)ResellerSetting::get($reseller->id, ResellerSettingKey::OnboardingTourOptionalReminderMode, 'pill'),
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
