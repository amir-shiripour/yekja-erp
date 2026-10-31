<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Modules\ResellerHub\App\Helpers\ResellerHelper;

class UpdateResellerSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $normalizeDigits = function ($val) {
            if ($val === null || $val === '') return null;
            return (int) ResellerHelper::cleanNumber($val);
        };

        if ($this->has('notification_mobile') && $this->input('notification_mobile') !== null) {
            $this->merge(['notification_mobile' => ResellerHelper::normalizeMobile($this->input('notification_mobile'))]);
        }

        if ($this->has('custom_domain_yearly_price')) {
            $this->merge(['custom_domain_yearly_price' => $normalizeDigits($this->input('custom_domain_yearly_price'))]);
        }
        if ($this->has('custom_domain_monthly_price')) {
            $this->merge(['custom_domain_monthly_price' => $normalizeDigits($this->input('custom_domain_monthly_price'))]);
        }
        if ($this->has('custom_domain_setup_fee')) {
            $this->merge(['custom_domain_setup_fee' => $normalizeDigits($this->input('custom_domain_setup_fee'))]);
        }
        if ($this->has('white_label_yearly_price')) {
            $this->merge(['white_label_yearly_price' => $normalizeDigits($this->input('white_label_yearly_price'))]);
        }
        if ($this->has('white_label_monthly_price')) {
            $this->merge(['white_label_monthly_price' => $normalizeDigits($this->input('white_label_monthly_price'))]);
        }
        if ($this->has('white_label_setup_fee')) {
            $this->merge(['white_label_setup_fee' => $normalizeDigits($this->input('white_label_setup_fee'))]);
        }
        if ($this->has('max_advance_renewal_cycles')) {
            $this->merge(['max_advance_renewal_cycles' => $normalizeDigits($this->input('max_advance_renewal_cycles'))]);
        }
        if ($this->has('reseller_rounding_factor')) {
            $this->merge(['reseller_rounding_factor' => $normalizeDigits($this->input('reseller_rounding_factor'))]);
        }
        if ($this->has('default_package_id') && ($this->input('default_package_id') === '' || $this->input('default_package_id') === '0')) {
            $this->merge(['default_package_id' => null]);
        }

        if ($this->has('onboarding_tour_steps') && is_string($this->input('onboarding_tour_steps'))) {
            $decoded = json_decode($this->input('onboarding_tour_steps'), true);
            if (is_array($decoded)) {
                $this->merge(['onboarding_tour_steps' => $decoded]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'default_plan_duration_months' => ['required', 'in:6,12,24,36'],
            'max_advance_renewal_cycles' => ['nullable', 'integer', 'min:0', 'max:120'],
            'auto_suspend_expired' => ['nullable', 'boolean'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'notify_days_before_expiry' => ['nullable', 'integer', 'min:1', 'max:60'],
            'notification_email' => ['nullable', 'email', 'max:191'],
            'notification_mobile' => ['nullable', 'string', 'max:20'],
            'allow_custom_domains' => ['nullable', 'boolean'],
            'subdomain_pattern' => ['nullable', 'string', 'max:100'],
            'enable_default_package_registration' => ['nullable', 'boolean'],
            'default_package_id' => ['nullable', 'integer', 'exists:reseller_packages,id'],
            'custom_domain_yearly_price' => ['nullable', 'integer', 'min:0'],
            'custom_domain_monthly_price' => ['nullable', 'integer', 'min:0'],
            'custom_domain_setup_fee' => ['nullable', 'integer', 'min:0'],
            'white_label_yearly_price' => ['nullable', 'integer', 'min:0'],
            'white_label_monthly_price' => ['nullable', 'integer', 'min:0'],
            'white_label_setup_fee' => ['nullable', 'integer', 'min:0'],

            // Financial & Invoicing Settings
            'currency' => ['nullable', 'in:toman,rial'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_mode' => ['nullable', 'string'],
            'reseller_rounding_mode' => ['nullable', 'string', 'in:none,up,down,nearest'],
            'reseller_rounding_factor' => ['nullable', 'integer', 'min:1'],
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'invoice_middle_prefix' => ['nullable', 'string', 'max:20'],
            'invoice_suffix' => ['nullable', 'string', 'max:20'],
            'invoice_padding' => ['nullable', 'integer', 'min:1', 'max:10'],
            'invoice_auto' => ['nullable', 'boolean'],
            'invoice_terms' => ['nullable', 'string', 'max:2000'],
            'invoice_footer_note' => ['nullable', 'string', 'max:2000'],
            'reseller_use_global_payment_settings' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_card_number' => ['nullable', 'string', 'max:30'],
            'bank_shaba_number' => ['nullable', 'string', 'max:35'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],

            // Print & Official Invoice Settings
            'reseller_print_mode' => ['nullable', 'in:standard,official'],
            'reseller_official_invoice_orientation' => ['nullable', 'in:portrait,landscape'],
            'reseller_stamp_standard_width' => ['nullable', 'integer', 'min:20', 'max:800'],
            'reseller_stamp_standard_height' => ['nullable', 'integer', 'min:20', 'max:600'],
            'reseller_stamp_official_width' => ['nullable', 'integer', 'min:20', 'max:800'],
            'reseller_stamp_official_height' => ['nullable', 'integer', 'min:20', 'max:600'],
            'reseller_economic_number' => ['nullable', 'string', 'max:50'],
            'reseller_national_id' => ['nullable', 'string', 'max:50'],
            'reseller_registration_number' => ['nullable', 'string', 'max:50'],
            'reseller_address' => ['nullable', 'string', 'max:500'],
            'reseller_postal_code' => ['nullable', 'string', 'max:20'],

            // Onboarding & Tour Settings
            'onboarding_tour_enabled' => ['nullable', 'boolean'],
            'onboarding_tour_display_mode' => ['nullable', 'string', 'in:bento,modal_wizard,sticky_banner'],
            'onboarding_tour_enforcement' => ['nullable', 'string', 'in:soft,strict_blocking,grace_period'],
            'onboarding_tour_grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'onboarding_tour_celebration_title' => ['nullable', 'string', 'max:150'],
            'onboarding_tour_celebration_message' => ['nullable', 'string', 'max:1000'],
            'onboarding_tour_steps' => ['nullable', 'array'],

            // Branding fields
            'company_name' => ['nullable', 'string', 'max:150'],
            'brand_title' => ['nullable', 'string', 'max:150'],
            'brand_primary_color' => ['nullable', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'brand_footer_text' => ['nullable', 'string', 'max:300'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'brand_logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,ico,avif,tiff,x-icon', 'max:2048'],
            'brand_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,gif,svg,webp,bmp,avif,tiff,x-icon', 'max:1024'],
            'brand_seal_signature' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp', 'max:2048'],
            'remove_brand_logo' => ['nullable', 'boolean'],
            'remove_brand_favicon' => ['nullable', 'boolean'],
            'remove_brand_seal_signature' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Persian validation messages.
     */
    public function messages(): array
    {
        return [
            'default_plan_duration_months.required' => 'انتخاب مدت‌زمان پیش‌فرض لایسنس اولیه الزامی است.',
            'default_plan_duration_months.in' => 'مدت‌زمان لایسنس باید یکی از مقادیر ۶، ۱۲، ۲۴ یا ۳۶ ماه باشد.',
            'grace_period_days.integer' => 'مهلت ارفاقی باید یک عدد صحیح باشد.',
            'grace_period_days.min' => 'مهلت ارفاقی نمی‌تواند منفی باشد.',
            'grace_period_days.max' => 'حداکثر مهلت ارفاقی ۹۰ روز است.',
            'notify_days_before_expiry.integer' => 'روزهای هشدار انقضا باید عدد صحیح باشد.',
            'notify_days_before_expiry.min' => 'روزهای هشدار انقضا حداقل باید ۱ روز باشد.',
            'notify_days_before_expiry.max' => 'روزهای هشدار انقضا حداکثر می‌تواند ۶۰ روز باشد.',
            'notification_email.email' => 'فرمت ایمیل اطلاعیه‌ها معتبر نیست.',
            'notification_mobile.max' => 'شماره موبایل نمی‌تواند بیش از ۲۰ کاراکتر باشد.',
            'company_name.max' => 'نام شرکت نمی‌تواند بیش از ۱۵۰ کاراکتر باشد.',
            'support_email.email' => 'فرمت ایمیل پشتیبانی معتبر نمی‌باشد.',
            'brand_logo.file' => 'فایل لوگو باید یک تصویر معتبر باشد.',
            'brand_logo.mimes' => 'فرمت فایل لوگو معتبر نیست (فرمت‌های تصویری مانند PNG، JPG، SVG، WebP، GIF و... مجاز هستند).',
            'brand_logo.max' => 'حجم فایل لوگو نباید بیش از ۲ مگابایت باشد.',
            'brand_favicon.file' => 'فایل فاوآیکون باید یک تصویر یا آیکون معتبر باشد.',
            'brand_favicon.mimes' => 'فرمت فایل فاوآیکون معتبر نیست (فرمت‌های آیکون و تصویر مانند ICO، PNG، SVG، WebP، JPG و... مجاز هستند).',
            'brand_favicon.max' => 'حجم فایل فاوآیکون نباید بیش از ۱ مگابایت باشد.',
            'brand_seal_signature.file' => 'فایل مهر و امضا باید یک تصویر معتبر باشد.',
            'brand_seal_signature.mimes' => 'فرمت فایل مهر و امضا معتبر نیست (فرمت‌های مجاز: PNG, JPG, WebP, SVG).',
            'brand_seal_signature.max' => 'حجم فایل مهر و امضا نباید بیش از ۲ مگابایت باشد.',
        ];
    }
}
