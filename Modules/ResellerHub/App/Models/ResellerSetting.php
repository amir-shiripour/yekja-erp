<?php

namespace Modules\ResellerHub\App\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ResellerHub\App\Enums\OnboardingStep;
use Modules\ResellerHub\App\Enums\ResellerSettingKey;
use Modules\ResellerHub\App\Helpers\ResellerHelper;

/**
 * @property int $reseller_id
 * @property int $default_plan_duration_months
 * @property bool $auto_suspend_expired
 * @property int $grace_period_days
 * @property int $notify_days_before_expiry
 * @property string|null $notification_email
 * @property string|null $notification_mobile
 * @property bool $allow_custom_domains
 * @property string $currency
 * @property float $default_tax_rate
 * @property string $tax_mode
 * @property string|null $invoice_prefix
 * @property string|null $invoice_middle_prefix
 * @property string|null $invoice_suffix
 * @property int $invoice_padding
 * @property bool $invoice_auto
 * @property string|null $invoice_terms
 * @property string|null $invoice_footer_note
 * @property bool $reseller_use_global_payment_settings
 * @property string|null $bank_name
 * @property string|null $bank_card_number
 * @property string|null $bank_shaba_number
 * @property string|null $bank_account_holder
 * @property int|null $custom_domain_yearly_price
 * @property int|null $custom_domain_monthly_price
 * @property int|null $custom_domain_setup_fee
 *
 * @method static Builder|ResellerSetting query()
 * @method static ResellerSetting|null find(mixed $id, array $columns = ['*'])
 * @method static ResellerSetting findOrFail(mixed $id, array $columns = ['*'])
 * @method static ResellerSetting firstOrCreate(array $attributes = [], array $values = [])
 * @method static ResellerSetting updateOrCreate(array $attributes, array $values = [])
 * @method static Builder|ResellerSetting where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ResellerSetting extends Model
{
    protected $table = 'reseller_settings';

    protected $primaryKey = 'reseller_id';

    public $incrementing = false;

    protected $fillable = [
        'reseller_id',
        'default_plan_duration_months',
        'max_advance_renewal_cycles',
        'auto_suspend_expired',
        'grace_period_days',
        'notify_days_before_expiry',
        'notification_email',
        'notification_mobile',
        'allow_custom_domains',
        'subdomain_pattern',
        'enable_default_package_registration',
        'default_package_id',
        'currency',
        'default_tax_rate',
        'tax_mode',
        'reseller_rounding_mode',
        'reseller_rounding_factor',
        'invoice_prefix',
        'invoice_middle_prefix',
        'invoice_suffix',
        'invoice_padding',
        'invoice_auto',
        'invoice_terms',
        'invoice_footer_note',
        'reseller_use_global_payment_settings',
        'bank_name',
        'bank_card_number',
        'bank_shaba_number',
        'bank_account_holder',
        'custom_domain_yearly_price',
        'custom_domain_monthly_price',
        'custom_domain_setup_fee',
        'white_label_yearly_price',
        'white_label_monthly_price',
        'white_label_setup_fee',
        'enable_module_itemized_pricing',
        'brand_primary_color',
        'reseller_print_mode',
        'reseller_official_invoice_orientation',
        'reseller_stamp_standard_width',
        'reseller_stamp_standard_height',
        'reseller_stamp_official_width',
        'reseller_stamp_official_height',
        'brand_seal_signature',
        'reseller_economic_number',
        'reseller_national_id',
        'reseller_registration_number',
        'reseller_postal_code',
        'reseller_address',
        'onboarding_tour_enabled',
        'onboarding_tour_display_mode',
        'onboarding_tour_enforcement',
        'onboarding_tour_grace_period_days',
        'onboarding_tour_celebration_title',
        'onboarding_tour_celebration_message',
        'onboarding_tour_steps',
    ];

    protected $casts = [
        'auto_suspend_expired' => 'boolean',
        'allow_custom_domains' => 'boolean',
        'enable_default_package_registration' => 'boolean',
        'default_package_id' => 'integer',
        'enable_module_itemized_pricing' => 'boolean',
        'invoice_auto' => 'boolean',
        'reseller_use_global_payment_settings' => 'boolean',
        'default_plan_duration_months' => 'integer',
        'max_advance_renewal_cycles' => 'integer',
        'grace_period_days' => 'integer',
        'notify_days_before_expiry' => 'integer',
        'invoice_padding' => 'integer',
        'default_tax_rate' => 'float',
        'reseller_rounding_factor' => 'integer',
        'custom_domain_yearly_price' => 'integer',
        'custom_domain_monthly_price' => 'integer',
        'custom_domain_setup_fee' => 'integer',
        'white_label_yearly_price' => 'integer',
        'white_label_monthly_price' => 'integer',
        'white_label_setup_fee' => 'integer',
        'reseller_stamp_standard_width' => 'integer',
        'reseller_stamp_standard_height' => 'integer',
        'reseller_stamp_official_width' => 'integer',
        'reseller_stamp_official_height' => 'integer',
        'onboarding_tour_enabled' => 'boolean',
        'onboarding_tour_grace_period_days' => 'integer',
        'onboarding_tour_steps' => 'array',
    ];

    /**
     * @var array<int, self|null>
     */
    private static array $runtimeCache = [];

    /**
     * Clear runtime cache (useful in tests and background workers).
     */
    public static function clearRuntimeCache(): void
    {
        self::$runtimeCache = [];
    }

    /**
     * Keep the memo consistent with writes made through the model.
     */
    protected static function booted(): void
    {
        static::saved(static function (self $setting): void {
            self::$runtimeCache[(int)$setting->reseller_id] = $setting;
        });

        static::deleted(static function (self $setting): void {
            unset(self::$runtimeCache[(int)$setting->reseller_id]);
        });
    }

    /**
     * Find setting model for a reseller using request-level memoization.
     */
    public static function findForReseller(?int $resellerId): ?self
    {
        if (!$resellerId) {
            return null;
        }

        if (array_key_exists($resellerId, self::$runtimeCache)) {
            return self::$runtimeCache[$resellerId];
        }

        return self::$runtimeCache[$resellerId] = self::where('reseller_id', $resellerId)->first();
    }

    /**
     * Get a setting value for a specific reseller.
     */
    public static function get(int $resellerId, string|ResellerSettingKey $key, $default = null)
    {
        $setting = self::findForReseller($resellerId);
        $keyName = $key instanceof ResellerSettingKey ? $key->value : $key;

        if (!$setting || !isset($setting->$keyName)) {
            return $default;
        }

        return $setting->$keyName;
    }

    /**
     * Set a single setting value for a specific reseller.
     */
    public static function set(int $resellerId, string|ResellerSettingKey $key, $value): void
    {
        $keyName = $key instanceof ResellerSettingKey ? $key->value : $key;
        self::setMany($resellerId, [$keyName => $value]);
    }

    /**
     * Set multiple setting values for a specific reseller in a single query.
     */
    public static function setMany(int $resellerId, array $attributes): self
    {
        $setting = self::firstOrCreate(
            ['reseller_id' => $resellerId],
            []
        );

        $setting->fill($attributes);
        $setting->save();

        self::$runtimeCache[$resellerId] = $setting;

        return $setting;
    }

    /**
     * Retrieve all settings for a specific reseller as an associative array.
     */
    public static function allForReseller(int $resellerId): array
    {
        $setting = self::findForReseller($resellerId);

        if (!$setting) {
            return [];
        }

        return $setting->toArray();
    }

    /**
     * Alias for allForReseller to prevent BadMethodCallException.
     */
    public static function getSettings(?int $resellerId): array
    {
        if (!$resellerId) {
            return [];
        }

        return self::allForReseller($resellerId);
    }

    /**
     * Get the active currency code for a specific reseller (or current logged-in reseller).
     */
    public static function getCurrencyForReseller(?int $resellerId = null): string
    {
        if (!$resellerId && auth()->check()) {
            $resellerId = auth()->user()?->reseller?->id ?? null;
        }

        return $resellerId ? (string)self::get($resellerId, 'currency', 'toman') : 'toman';
    }

    /**
     * Get the Persian label for a specific reseller.
     */
    public static function getCurrencyLabelForReseller(?int $resellerId = null): string
    {
        $currency = self::getCurrencyForReseller($resellerId);

        return ResellerHelper::currencyLabel($currency);
    }

    /**
     * Relationship to the configured default package for registration.
     */
    public function defaultPackage(): BelongsTo
    {
        return $this->belongsTo(ResellerPackage::class, 'default_package_id');
    }

    /**
     * Check if default package registration is enabled for this reseller.
     */
    public function isDefaultPackageRegistrationActive(): bool
    {
        if (!(bool)$this->enable_default_package_registration || empty($this->default_package_id)) {
            return false;
        }

        return ResellerPackage::where('is_active', true)
            ->where('reseller_id', $this->reseller_id)
            ->where('id', $this->default_package_id)
            ->exists();
    }

    /**
     * Get default registration package model for a given reseller id if active.
     */
    public static function getDefaultRegistrationPackage(?int $resellerId): ?ResellerPackage
    {
        if (!$resellerId) {
            return null;
        }

        $setting = self::findForReseller($resellerId);
        if (!$setting || !$setting->enable_default_package_registration || !$setting->default_package_id) {
            return null;
        }

        return ResellerPackage::where('is_active', true)
            ->where('reseller_id', $resellerId)
            ->find($setting->default_package_id);
    }

    /**
     * Get default predefined onboarding steps.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDefaultOnboardingSteps(): array
    {
        return OnboardingStep::defaultStepsArray();
    }

    /**
     * Get sorted onboarding tour steps for a reseller.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getOnboardingTourSteps(?int $resellerId): array
    {
        $setting = self::findForReseller($resellerId);
        $steps = $setting?->onboarding_tour_steps;
        $defaultSteps = self::getDefaultOnboardingSteps();

        if (empty($steps) || !is_array($steps)) {
            $steps = $defaultSteps;
        } else {
            // Ensure all essential core steps (KYC, Domain, Branding/White-label, Package) are always present
            $existingSlugs = array_column($steps, 'slug');
            foreach ($defaultSteps as $defaultCoreStep) {
                if (!in_array($defaultCoreStep['slug'], $existingSlugs, true)) {
                    $defaultCoreStep['sort_order'] = count($steps) + 1;
                    $steps[] = $defaultCoreStep;
                }
            }
        }

        // Guarantee core steps are permanently marked as core and mandatory
        $coreSlugs = array_column($defaultSteps, 'slug');
        foreach ($steps as &$step) {
            if (in_array($step['slug'] ?? '', $coreSlugs, true)) {
                $step['is_core'] = true;
                $step['is_required'] = true;
            }
        }
        unset($step);

        usort($steps, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return array_values($steps);
    }

    /**
     * Get full onboarding tour config for a reseller with defaults.
     *
     * @return array<string, mixed>
     */
    public static function getOnboardingTourConfig(?int $resellerId): array
    {
        $setting = self::findForReseller($resellerId);

        return [
            'enabled' => $setting ? (bool)$setting->onboarding_tour_enabled : true,
            'display_mode' => $setting?->onboarding_tour_display_mode ?: 'bento',
            'enforcement' => $setting?->onboarding_tour_enforcement ?: 'soft',
            'grace_period_days' => (int)($setting?->onboarding_tour_grace_period_days ?? 3),
            'celebration_title' => $setting?->onboarding_tour_celebration_title ?: 'تبریک! سامانه CRM شما ۱۰۰٪ پیکربندی و فعال شد',
            'celebration_message' => $setting?->onboarding_tour_celebration_message ?: 'احراز هویت، دامنه اختصاصی، برند وایت‌لیبل و پکیج شما فعال هستند. تمامی ماژول‌ها در سایدبار در دسترس شما قرار دارند.',
            'steps' => self::getOnboardingTourSteps($resellerId),
        ];
    }
}
