<?php

namespace Modules\ResellerHub\App\Enums;

/**
 * SSOT for keys, types, and categories.
 */
enum ResellerSettingKey: string
{
    // General & Expiry Settings
    case DefaultPlanDurationMonths = 'default_plan_duration_months';
    case MaxAdvanceRenewalCycles = 'max_advance_renewal_cycles';
    case AutoSuspendExpired = 'auto_suspend_expired';
    case GracePeriodDays = 'grace_period_days';
    case NotifyDaysBeforeExpiry = 'notify_days_before_expiry';
    case NotificationEmail = 'notification_email';
    case NotificationMobile = 'notification_mobile';
    case AllowCustomDomains = 'allow_custom_domains';
    case SubdomainPattern = 'subdomain_pattern';
    case EnableDefaultPackageRegistration = 'enable_default_package_registration';
    case DefaultPackageId = 'default_package_id';

    // Pricing & White-label
    case CustomDomainYearlyPrice = 'custom_domain_yearly_price';
    case CustomDomainMonthlyPrice = 'custom_domain_monthly_price';
    case CustomDomainSetupFee = 'custom_domain_setup_fee';
    case WhiteLabelYearlyPrice = 'white_label_yearly_price';
    case WhiteLabelMonthlyPrice = 'white_label_monthly_price';
    case WhiteLabelSetupFee = 'white_label_setup_fee';
    case BrandPrimaryColor = 'brand_primary_color';

    // Financial & Invoicing
    case Currency = 'currency';
    case DefaultTaxRate = 'default_tax_rate';
    case TaxMode = 'tax_mode';
    case ResellerRoundingMode = 'reseller_rounding_mode';
    case ResellerRoundingFactor = 'reseller_rounding_factor';
    case InvoicePrefix = 'invoice_prefix';
    case InvoiceMiddlePrefix = 'invoice_middle_prefix';
    case InvoiceSuffix = 'invoice_suffix';
    case InvoicePadding = 'invoice_padding';
    case InvoiceAuto = 'invoice_auto';
    case InvoiceTerms = 'invoice_terms';
    case InvoiceFooterNote = 'invoice_footer_note';
    case ResellerUseGlobalPaymentSettings = 'reseller_use_global_payment_settings';
    case BankName = 'bank_name';
    case BankCardNumber = 'bank_card_number';
    case BankShabaNumber = 'bank_shaba_number';
    case BankAccountHolder = 'bank_account_holder';

    // Print & Official Invoice
    case ResellerPrintMode = 'reseller_print_mode';
    case ResellerOfficialInvoiceOrientation = 'reseller_official_invoice_orientation';
    case ResellerStampStandardWidth = 'reseller_stamp_standard_width';
    case ResellerStampStandardHeight = 'reseller_stamp_standard_height';
    case ResellerStampOfficialWidth = 'reseller_stamp_official_width';
    case ResellerStampOfficialHeight = 'reseller_stamp_official_height';
    case BrandSealSignature = 'brand_seal_signature';
    case ResellerEconomicNumber = 'reseller_economic_number';
    case ResellerNationalId = 'reseller_national_id';
    case ResellerRegistrationNumber = 'reseller_registration_number';
    case ResellerAddress = 'reseller_address';
    case ResellerPostalCode = 'reseller_postal_code';

    // Onboarding & Tour Settings
    case OnboardingTourEnabled = 'onboarding_tour_enabled';
    case OnboardingTourDisplayMode = 'onboarding_tour_display_mode';
    case OnboardingTourEnforcement = 'onboarding_tour_enforcement';
    case OnboardingTourGracePeriodDays = 'onboarding_tour_grace_period_days';
    case OnboardingTourCelebrationTitle = 'onboarding_tour_celebration_title';
    case OnboardingTourCelebrationMessage = 'onboarding_tour_celebration_message';
    case OnboardingTourSteps = 'onboarding_tour_steps';

    /**
     * Resolve ResellerSettingKey instance safely from string or self.
     */
    public static function resolve(string|self|null $key): ?self
    {
        if ($key instanceof self) {
            return $key;
        }

        if (empty($key) || !is_string($key)) {
            return null;
        }

        return self::tryFrom(trim($key));
    }
}
