<?php

namespace Modules\ResellerHub\App\Actions\Settings;

use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use App\Models\Tenant;

class UpdateResellerSettingsAction
{
    use FileUploadTrait;

    /**
     * Execute the update of reseller settings and branding details.
     */
    public function execute(Reseller $reseller, array $validated, Request $request): void
    {
        $settingsData = $this->extractSettingsData($request, $validated, $reseller);
        ResellerSetting::setMany($reseller->id, $settingsData);

        $brandingData = $this->extractBrandingData($request, $validated, $reseller);
        if (!empty($brandingData)) {
            $reseller->update($brandingData);
        }

        // Flush onboarding cache for tenants of this reseller if tour settings changed
        if (
            array_key_exists(ResellerSettingKey::OnboardingTourEnabled->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourSteps->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourEnforcement->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourDisplayMode->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourCelebrationTitle->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourCelebrationMessage->value, $settingsData) ||
            array_key_exists(ResellerSettingKey::OnboardingTourGracePeriodDays->value, $settingsData)
        ) {
            ResellerSetting::clearRuntimeCache();
            $tenantIds = Tenant::where('reseller_id', $reseller->id)->pluck('id');
            $stateService = app(OnboardingStateService::class);
            foreach ($tenantIds as $tId) {
                $stateService->flushCache($tId);
            }
        }
    }

    /**
     * Map and normalize validated input into ResellerSetting key-value pairs.
     */
    protected function extractSettingsData(Request $request, array $validated, Reseller $reseller): array
    {
        $data = [];

        $trimmedStrings = [
            ResellerSettingKey::NotificationEmail->value,
            ResellerSettingKey::NotificationMobile->value,
            ResellerSettingKey::InvoicePrefix->value,
            ResellerSettingKey::InvoiceMiddlePrefix->value,
            ResellerSettingKey::InvoiceSuffix->value,
            ResellerSettingKey::InvoiceTerms->value,
            ResellerSettingKey::BankName->value,
            ResellerSettingKey::BankCardNumber->value,
            ResellerSettingKey::BankShabaNumber->value,
            ResellerSettingKey::BankAccountHolder->value,
            ResellerSettingKey::ResellerEconomicNumber->value,
            ResellerSettingKey::ResellerNationalId->value,
            ResellerSettingKey::ResellerRegistrationNumber->value,
            ResellerSettingKey::ResellerAddress->value,
            ResellerSettingKey::ResellerPostalCode->value,
        ];
        foreach ($trimmedStrings as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = trim((string)($validated[$key] ?? ''));
            }
        }

        if (array_key_exists(ResellerSettingKey::InvoiceTerms->value, $validated)) {
            $data[ResellerSettingKey::InvoiceFooterNote->value] = '';
        }

        if (array_key_exists(ResellerSettingKey::SubdomainPattern->value, $validated)) {
            $data[ResellerSettingKey::SubdomainPattern->value] = !empty($validated[ResellerSettingKey::SubdomainPattern->value])
                ? trim($validated[ResellerSettingKey::SubdomainPattern->value])
                : 'crm-{rand}';
        }
        if (array_key_exists(ResellerSettingKey::BrandPrimaryColor->value, $validated)) {
            $data[ResellerSettingKey::BrandPrimaryColor->value] = trim($validated[ResellerSettingKey::BrandPrimaryColor->value] ?? '#4f46e5');
        }
        foreach ([
                     ResellerSettingKey::Currency->value,
                     ResellerSettingKey::ResellerPrintMode->value,
                     ResellerSettingKey::ResellerOfficialInvoiceOrientation->value,
                     ResellerSettingKey::ResellerRoundingMode->value,
                 ] as $key) {
            if (isset($validated[$key])) {
                $data[$key] = $validated[$key];
            }
        }
        if (isset($validated[ResellerSettingKey::DefaultTaxRate->value])) {
            $data[ResellerSettingKey::DefaultTaxRate->value] = (float)$validated[ResellerSettingKey::DefaultTaxRate->value];
        }
        $data[ResellerSettingKey::TaxMode->value] = 'invoice';

        $integerKeys = [
            ResellerSettingKey::DefaultPlanDurationMonths->value,
            ResellerSettingKey::GracePeriodDays->value,
            ResellerSettingKey::NotifyDaysBeforeExpiry->value,
            ResellerSettingKey::CustomDomainYearlyPrice->value,
            ResellerSettingKey::CustomDomainMonthlyPrice->value,
            ResellerSettingKey::CustomDomainSetupFee->value,
            ResellerSettingKey::WhiteLabelYearlyPrice->value,
            ResellerSettingKey::WhiteLabelMonthlyPrice->value,
            ResellerSettingKey::WhiteLabelSetupFee->value,
            ResellerSettingKey::InvoicePadding->value,
            ResellerSettingKey::ResellerRoundingFactor->value,
        ];
        foreach ($integerKeys as $key) {
            if (isset($validated[$key])) {
                $data[$key] = (int)$validated[$key];
            }
        }

        $nullableIntKeys = [
            ResellerSettingKey::MaxAdvanceRenewalCycles->value,
            ResellerSettingKey::DefaultPackageId->value,
            ResellerSettingKey::ResellerStampStandardWidth->value,
            ResellerSettingKey::ResellerStampStandardHeight->value,
            ResellerSettingKey::ResellerStampOfficialWidth->value,
            ResellerSettingKey::ResellerStampOfficialHeight->value,
        ];
        foreach ($nullableIntKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = (!is_null($validated[$key]) && $validated[$key] !== '')
                    ? (int)$validated[$key]
                    : null;
            }
        }

        foreach ([
                     ResellerSettingKey::AutoSuspendExpired->value,
                     ResellerSettingKey::AllowCustomDomains->value,
                 ] as $key) {
            if ($request->exists($key) || array_key_exists($key, $validated)) {
                $data[$key] = $request->boolean($key) ? 1 : 0;
            }
        }
        $data[ResellerSettingKey::EnableDefaultPackageRegistration->value] = $request->boolean(ResellerSettingKey::EnableDefaultPackageRegistration->value) ? 1 : 0;
        $data[ResellerSettingKey::InvoiceAuto->value] = $request->boolean(ResellerSettingKey::InvoiceAuto->value) ? 1 : 0;
        $data[ResellerSettingKey::ResellerUseGlobalPaymentSettings->value] = $request->boolean(ResellerSettingKey::ResellerUseGlobalPaymentSettings->value) ? 1 : 0;

        // 6. Seal / Signature file upload
        $currentSeal = ResellerSetting::get($reseller->id, ResellerSettingKey::BrandSealSignature);
        $fileUpdate = $this->handleFileUpdate($request, ResellerSettingKey::BrandSealSignature->value, $currentSeal, 'branding/stamps');
        if (!empty($fileUpdate)) {
            $data[ResellerSettingKey::BrandSealSignature->value] = $fileUpdate[ResellerSettingKey::BrandSealSignature->value];
        }

        // 7. Onboarding Tour Settings
        if (array_key_exists(ResellerSettingKey::OnboardingTourDisplayMode->value, $validated)) {
            $data[ResellerSettingKey::OnboardingTourDisplayMode->value] = $validated[ResellerSettingKey::OnboardingTourDisplayMode->value];
        }
        if (array_key_exists(ResellerSettingKey::OnboardingTourEnforcement->value, $validated)) {
            $data[ResellerSettingKey::OnboardingTourEnforcement->value] = $validated[ResellerSettingKey::OnboardingTourEnforcement->value];
        }
        if (array_key_exists(ResellerSettingKey::OnboardingTourCelebrationTitle->value, $validated)) {
            $data[ResellerSettingKey::OnboardingTourCelebrationTitle->value] = trim((string)$validated[ResellerSettingKey::OnboardingTourCelebrationTitle->value]);
        }
        if (array_key_exists(ResellerSettingKey::OnboardingTourCelebrationMessage->value, $validated)) {
            $data[ResellerSettingKey::OnboardingTourCelebrationMessage->value] = trim((string)$validated[ResellerSettingKey::OnboardingTourCelebrationMessage->value]);
        }
        if (array_key_exists(ResellerSettingKey::OnboardingTourGracePeriodDays->value, $validated)) {
            $data[ResellerSettingKey::OnboardingTourGracePeriodDays->value] = (int)$validated[ResellerSettingKey::OnboardingTourGracePeriodDays->value];
        }
        if ($request->has(ResellerSettingKey::OnboardingTourEnabled->value)) {
            $data[ResellerSettingKey::OnboardingTourEnabled->value] = $request->boolean(ResellerSettingKey::OnboardingTourEnabled->value) ? 1 : 0;
        }
        if (array_key_exists(ResellerSettingKey::OnboardingTourSteps->value, $validated)) {
            $rawSteps = is_array($validated[ResellerSettingKey::OnboardingTourSteps->value])
                ? $validated[ResellerSettingKey::OnboardingTourSteps->value]
                : (json_decode((string)$validated[ResellerSettingKey::OnboardingTourSteps->value], true) ?: []);

            $data[ResellerSettingKey::OnboardingTourSteps->value] = $this->normalizeTourSteps($rawSteps);
        }

        return $data;
    }

    /**
     * Normalize and validate tour steps array ensuring correct types and sort order.
     */
    protected function normalizeTourSteps(array $rawSteps): array
    {
        $normalized = [];
        $coreSteps = collect(ResellerSetting::getDefaultOnboardingSteps())->keyBy('slug');
        $index = 1;

        foreach ($rawSteps as $step) {
            if (!is_array($step)) {
                continue;
            }
            $slug = trim((string)($step['slug'] ?? ''));
            if (empty($slug)) {
                continue;
            }

            $isCore = isset($coreSteps[$slug]) || !empty($step['is_core']);
            $coreDefault = $coreSteps->get($slug, []);

            $normalized[] = [
                'slug' => $slug,
                'is_core' => $isCore,
                'enabled' => !empty($step['enabled']),
                'is_required' => $isCore ? true : !empty($step['is_required']),
                'sort_order' => isset($step['sort_order']) ? (int)$step['sort_order'] : $index,
                'title' => trim((string)($step['title'] ?? ($coreDefault['title'] ?? $slug))),
                'description' => trim((string)($step['description'] ?? ($coreDefault['description'] ?? ''))),
                'badge' => trim((string)($step['badge'] ?? ($coreDefault['badge'] ?? 'گام ' . $index))),
                'icon' => trim((string)($step['icon'] ?? ($coreDefault['icon'] ?? 'check-circle'))),
                'depends_on' => !empty($step['depends_on']) ? trim((string)$step['depends_on']) : null,
                'action_type' => trim((string)($step['action_type'] ?? ($coreDefault['action_type'] ?? 'modal'))),
                'action_target' => trim((string)($step['action_target'] ?? ($coreDefault['action_target'] ?? ''))),
                'action_url' => !empty($step['action_url']) ? trim((string)$step['action_url']) : null,
                'can_manual_complete' => !empty($step['can_manual_complete']),
            ];
            $index++;
        }

        usort($normalized, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        foreach ($normalized as $idx => &$item) {
            $item['sort_order'] = $idx + 1;
        }

        return $normalized;
    }

    /**
     * Map and normalize validated input into Reseller model branding attributes.
     */
    protected function extractBrandingData(Request $request, array $validated, Reseller $reseller): array
    {
        $data = [];

        if (!empty($validated['company_name'])) {
            $data['company_name'] = $validated['company_name'];
        }

        foreach (['brand_title', 'brand_footer_text', 'support_phone', 'support_email'] as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }

        $logoUpdate = $this->handleFileUpdate($request, 'brand_logo', $reseller->brand_logo, 'branding/logos');
        if (!empty($logoUpdate)) {
            $data['brand_logo'] = $logoUpdate['brand_logo'];
        }

        $faviconUpdate = $this->handleFileUpdate($request, 'brand_favicon', $reseller->brand_favicon, 'branding/favicons');
        if (!empty($faviconUpdate)) {
            $data['brand_favicon'] = $faviconUpdate['brand_favicon'];
        }

        return $data;
    }

    /**
     * Handle upload or deletion of a branding file field.
     */
    protected function handleFileUpdate(Request $request, string $field, ?string $currentPath, string $directory): array
    {
        if ($request->boolean("remove_{$field}")) {
            $this->deleteStoredFile($currentPath);
            return [$field => null];
        }

        if ($request->hasFile($field)) {
            $this->deleteStoredFile($currentPath);
            $newPath = $this->uploadFile($request->file($field), $directory, 'public');
            return [$field => $newPath];
        }

        return [];
    }

    /**
     * Delete stored file from public disk if present.
     */
    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
