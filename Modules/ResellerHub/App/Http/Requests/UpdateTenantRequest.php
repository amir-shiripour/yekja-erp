<?php

namespace Modules\ResellerHub\App\Http\Requests;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ResellerHub\App\Helpers\ResellerHelper;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare inputs for validation (normalize digits and format).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('admin_mobile') && is_string($this->input('admin_mobile'))) {
            $this->merge(['admin_mobile' => ResellerHelper::normalizeMobile($this->input('admin_mobile'))]);
        }

        $domainType = $this->input('domain_type');
        if ($domainType === 'subdomain') {
            $this->merge(['custom_domain' => null]);
        } elseif ($this->has('custom_domain') && is_string($this->input('custom_domain'))) {
            $cleaned = preg_replace('#^https?://#i', '', trim($this->input('custom_domain')));
            $cleaned = rtrim(strtolower($cleaned), '/');
            $this->merge(['custom_domain' => $cleaned ?: null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $reservedSubdomains = app(TenantManager::class)->getReservedSubdomains();
        $tenant = $this->route('tenant');
        $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        $tenantModel = is_object($tenant) ? $tenant : ($tenantId ? Tenant::find($tenantId) : null);
        $resellerId = (int) ($tenantModel?->reseller_id ?? 0);

        // The admin user is the sub-account's user, or the tenant's first user.
        $adminUserId = (int) ($tenantModel?->subAccount?->user_id
            ?: $tenantModel?->users()->first()?->id
            ?: 0);

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'domain_type' => ['nullable', 'in:subdomain,custom'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('tenants', 'subdomain')->ignore($tenantId),
                Rule::notIn($reservedSubdomains),
            ],
            'custom_domain' => [
                Rule::requiredIf(fn() => $this->input('domain_type') === 'custom'),
                'nullable',
                'string',
                'max:100',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                Rule::unique('tenants', 'custom_domain')->ignore($tenantId),
                Rule::notIn($reservedSubdomains),
            ],
            'status' => ['nullable', 'in:active,suspended,expired'],
            'sub_account_id' => [
                'nullable',
                Rule::exists('reseller_sub_accounts', 'id')
                    ->where(fn ($query) => $query->where('reseller_id', $resellerId)),
            ],
            'admin_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'admin_email' => [
                'nullable', 'email', 'max:150',
                Rule::unique('users', 'email')->where(fn($query) => $query->where('tenant_id', $tenantId))->ignore($adminUserId),
            ],
            'admin_mobile' => [
                'nullable', 'string', 'regex:/^09[0-9]{9}$/',
                Rule::unique('users', 'mobile')->where(fn($query) => $query->where('tenant_id', $tenantId))->ignore($adminUserId),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable'],
            'limits' => ['nullable', 'array'],
            'limits.*' => ['nullable'],

            // Dedicated White-label branding fields
            'white_label_type' => ['nullable', 'in:default,custom'],
            'branding' => ['nullable', 'array'],
            'branding.is_custom' => ['nullable'],
            'branding.brand_title' => ['nullable', 'string', 'max:150'],
            'branding.brand_primary_color' => ['nullable', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'branding.brand_footer_text' => ['nullable', 'string', 'max:300'],
            'branding.support_phone' => ['nullable', 'string', 'max:30'],
            'branding.support_email' => ['nullable', 'email', 'max:150'],
            'tenant_brand_logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,ico,avif,tiff,x-icon', 'max:2048'],
            'tenant_brand_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,gif,svg,webp,bmp,avif,tiff,x-icon', 'max:1024'],
            'remove_tenant_brand_logo' => ['nullable', 'boolean'],
            'remove_tenant_brand_favicon' => ['nullable', 'boolean'],
            'issue_invoice' => ['nullable'],
            'invoice_number' => ['nullable', 'string', 'max:64'],
            'invoice_auto' => ['nullable', 'boolean'],
            'tax_from_settings' => ['nullable', 'boolean'],
            'invoice_issue_date' => ['nullable', 'string', 'max:50'],
            'invoice_due_date' => ['nullable', 'string', 'max:50'],
            'invoice_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_tax_mode' => ['nullable', 'in:invoice,item'],
            'invoice_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_terms' => ['nullable', 'string', 'max:3000'],
            'invoice_notes' => ['nullable', 'string', 'max:3000'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.description' => ['nullable', 'string', 'max:500'],
            'invoice_items.*.title' => ['nullable', 'string', 'max:191'],
            'invoice_items.*.item_type' => ['nullable', 'string', 'max:50'],
            'invoice_items.*.item_id' => ['nullable', 'string', 'max:100'],
            'invoice_items.*.duration_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'invoice_items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'invoice_items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'invoice_items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Custom Persian validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام کسب‌وکار / کلینیک الزامی است.',
            'name.min' => 'نام کسب‌وکار باید حداقل ۲ کاراکتر باشد.',
            'subdomain.required' => 'انتخاب آدرس سابدامین اختصاصی الزامی است.',
            'subdomain.min' => 'سابدامین باید حداقل ۳ کاراکتر باشد.',
            'subdomain.regex' => 'سابدامین فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره (-) باشد.',
            'subdomain.unique' => 'این سابدامین قبلاً توسط سازمان دیگری ثبت شده است.',
            'subdomain.not_in' => 'این سابدامین سیستمی و رزرو شده است و امکان استفاده از آن وجود ندارد.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نمی‌باشد.',
            'admin_name.min' => 'نام و نام خانوادگی مدیر باید حداقل ۲ کاراکتر باشد.',
            'admin_email.email' => 'فرمت ایمیل مدیر صحیح نمی‌باشد.',
            'admin_email.unique' => 'این نشانی ایمیل قبلاً برای یک مدیر دیگر در همین سامانه ثبت شده است.',
            'admin_mobile.required' => 'وارد کردن شماره تماس همراه مدیر سامانه الزامی است.',
            'admin_mobile.regex' => 'فرمت شماره موبایل نامعتبر است. شماره باید ۱۱ رقمی بوده و با ۰۹ آغاز شود (مثال: ۰۹۱۲۳۴۵۶۷۸۹).',
            'admin_mobile.unique' => 'این شماره موبایل قبلاً برای یک مدیر دیگر در همین سامانه ثبت شده است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور جدید مطابقت ندارد.',
        ];
    }
}
