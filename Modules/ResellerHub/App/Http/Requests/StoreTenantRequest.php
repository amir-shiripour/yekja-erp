<?php

namespace Modules\ResellerHub\App\Http\Requests;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSubAccount;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Resolve the current reseller for validation context.
     */
    public function resolveReseller(): ?Reseller
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($this->filled('reseller_id') && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return Reseller::find($this->input('reseller_id'));
        }

        $reseller = Reseller::where('user_id', $user->id)->orderBy('id')->first();

        return $reseller;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('admin_mobile') && is_string($this->input('admin_mobile'))) {
            $this->merge(['admin_mobile' => ResellerHelper::normalizeMobile($this->input('admin_mobile'))]);
        }

        $normalizeDigits = fn($val) => ($val === null || $val === '') ? $val : ResellerHelper::cleanNumber($val);

        if ($this->has('invoice_discount_amount')) {
            $this->merge(['invoice_discount_amount' => $normalizeDigits($this->input('invoice_discount_amount'))]);
        }
        if ($this->has('invoice_tax_rate')) {
            $this->merge(['invoice_tax_rate' => $normalizeDigits($this->input('invoice_tax_rate'))]);
        }
        if ($this->has('invoice_items') && is_array($this->input('invoice_items'))) {
            $items = $this->input('invoice_items');
            foreach ($items as $idx => $item) {
                if (isset($item['unit_price'])) {
                    $items[$idx]['unit_price'] = $normalizeDigits($item['unit_price']);
                }
                if (isset($item['quantity'])) {
                    $items[$idx]['quantity'] = $normalizeDigits($item['quantity']);
                }
                if (isset($item['discount_amount'])) {
                    $items[$idx]['discount_amount'] = $normalizeDigits($item['discount_amount']);
                }
                if (isset($item['tax_rate'])) {
                    $items[$idx]['tax_rate'] = $normalizeDigits($item['tax_rate']);
                }
            }
            $this->merge(['invoice_items' => $items]);
        }

        if ($this->has('invoice_number')) {
            $this->merge(['invoice_number' => trim((string)$this->input('invoice_number'))]);
        }

        $packageId = $this->input('package_id');
        $isPermanentPackage = false;
        if ($packageId) {
            $pkg = ResellerPackage::find($packageId);
            if ($pkg && !$pkg->getPriceTypeEnum()->hasRenewal()) {
                $isPermanentPackage = true;
                $this->merge(['expires_in_months' => null]);
            }
            if ($pkg && $pkg->getPriceTypeEnum()->isFree() && !$this->has('issue_invoice')) {
                $this->merge(['issue_invoice' => 0]);
            }
        }

        $rawExpiry = $this->input('expires_in_months');
        if ($rawExpiry === 'lifetime' || $rawExpiry === '0' || (int)$rawExpiry === 0 || (int)$rawExpiry >= 1200) {
            $this->merge(['expires_in_months' => null]);
        }

        $domainType = $this->input('domain_type');
        if ($domainType === 'subdomain') {
            $this->merge(['custom_domain' => null]);
        } elseif ($domainType === 'custom' || ($this->filled('custom_domain') && !$this->filled('subdomain'))) {
            $cleaned = preg_replace('#^https?://#i', '', trim((string)$this->input('custom_domain')));
            $cleaned = rtrim(strtolower($cleaned), '/');
            $this->merge(['custom_domain' => $cleaned ?: null]);

            if (!$this->filled('subdomain') && !empty($cleaned)) {
                $baseSlug = Str::slug(explode('.', $cleaned)[0]);
                if (empty($baseSlug) || strlen($baseSlug) < 3) {
                    $baseSlug = Str::slug((string)$this->input('name'));
                }
                if (empty($baseSlug) || strlen($baseSlug) < 3) {
                    $baseSlug = 'crm-' . Str::lower(Str::random(5));
                }
                $slug = substr($baseSlug, 0, 50);
                while (Tenant::where('subdomain', $slug)->exists()) {
                    $slug = substr($baseSlug, 0, 45) . '-' . Str::lower(Str::random(4));
                }
                $this->merge(['subdomain' => $slug]);
            }
        }

        $currentReseller = $this->resolveReseller();
        if ($this->filled('sub_account_id')) {
            $sub = ResellerSubAccount::when($currentReseller, fn($q) => $q->where('reseller_id', $currentReseller->id))
                ->find($this->input('sub_account_id'));
            if ($sub) {
                if (!$this->filled('admin_name')) $this->merge(['admin_name' => $sub->name]);
                if (!$this->filled('admin_email')) $this->merge(['admin_email' => $sub->email]);
                if (!$this->filled('admin_mobile')) $this->merge(['admin_mobile' => $sub->mobile]);
            }
        }

        if (!$this->filled('name')) {
            $subName = null;
            if ($this->filled('sub_account_id')) {
                $sub = isset($sub) ? $sub : ResellerSubAccount::when($currentReseller, fn($q) => $q->where('reseller_id', $currentReseller->id))->find($this->input('sub_account_id'));
                $subName = $sub?->company_name ?: $sub?->name;
            }
            $fallbackName = $subName
                ?: ($this->input('branding.brand_title')
                    ?: ($this->input('subdomain') ?: 'سامانه اختصاصی'));
            $this->merge(['name' => $fallbackName]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $reseller = $this->resolveReseller();
        $reservedSubdomains = app(TenantManager::class)->getReservedSubdomains();
        $isExistingTenant = $this->input('tenant_mode') === 'existing';
        $skipTenantFields = $isExistingTenant;
        $hasSubAccount = $this->filled('sub_account_id');
        $subAccount = $hasSubAccount ? ResellerSubAccount::find($this->input('sub_account_id')) : null;
        $existingUserId = $subAccount?->user_id;
        $adminFieldsOptional = $skipTenantFields || $hasSubAccount;

        $adminEmailRules = array_values(array_filter([
            $adminFieldsOptional ? 'nullable' : 'required',
            'email',
            'max:150',
            $hasSubAccount ? null : Rule::unique('users', 'email')->ignore($existingUserId),
        ]));

        $adminMobileRules = array_values(array_filter([
            $adminFieldsOptional ? 'nullable' : 'required',
            'string',
            'regex:/^09[0-9]{9}$/',
            $hasSubAccount ? null : Rule::unique('users', 'mobile')->ignore($existingUserId),
        ]));

        return [
            'tenant_mode' => ['nullable', 'in:new,existing'],
            'existing_tenant_id' => [
                $isExistingTenant ? 'required' : 'nullable',
                'integer',
                Rule::exists('tenants', 'id')->where(fn($q) => $reseller ? $q->where('reseller_id', $reseller->id) : $q),
            ],
            'sub_account_id' => [
                $skipTenantFields ? 'nullable' : 'required',
                'integer',
                Rule::exists('reseller_sub_accounts', 'id')->where(fn($q) => $reseller ? $q->where('reseller_id', $reseller->id) : $q),
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'domain_type' => ['nullable', 'in:subdomain,custom'],
            'subdomain' => [
                $skipTenantFields ? 'nullable' : Rule::requiredIf(fn() => $this->input('domain_type') !== 'custom'),
                'nullable',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                'unique:tenants,subdomain',
                Rule::notIn($reservedSubdomains),
            ],
            'custom_domain' => [
                Rule::requiredIf(fn() => $this->input('domain_type') === 'custom'),
                'nullable',
                'string',
                'max:100',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                'unique:tenants,custom_domain',
                Rule::notIn($reservedSubdomains),
            ],
            'reseller_id' => ['nullable', 'exists:resellers,id'],
            'package_id' => [
                'nullable',
                'integer',
                Rule::exists('reseller_packages', 'id')->where(function ($q) use ($reseller) {
                    $q->where('is_active', true);
                    if ($reseller) {
                        $q->where('reseller_id', $reseller->id);
                    }
                }),
            ],
            'admin_name' => [($skipTenantFields || $hasSubAccount) ? 'nullable' : 'required', 'string', 'min:2', 'max:100'],
            'admin_email' => $adminEmailRules,
            'admin_mobile' => $adminMobileRules,
            'password' => [($skipTenantFields || $hasSubAccount) ? 'nullable' : 'required', 'string', 'min:6'],
            'expires_in_months' => ['nullable', 'integer', 'min:1', 'max:1200'],
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

            // Invoice integration fields
            'issue_invoice' => ['nullable'],
            'invoice_number' => ['nullable', 'string', 'max:64'],
            'invoice_auto' => ['nullable', 'boolean'],
            'tax_from_settings' => ['nullable', 'boolean'],
            'invoice_issue_date' => ['nullable', 'string', 'max:50'],
            'invoice_due_date' => ['nullable', 'string', 'max:50'],
            'invoice_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_tax_mode' => ['nullable', 'in:invoice,item'],
            'invoice_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_status' => ['nullable', 'in:draft,unpaid,paid'],
            'invoice_terms' => ['nullable', 'string', 'max:3000'],
            'invoice_notes' => ['nullable', 'string', 'max:3000'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.description' => ['nullable', 'string', 'max:255'],
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
            'sub_account_id.required' => 'لطفاً حساب خریدار (زیرمجموعه) را انتخاب کنید.',
            'sub_account_id.exists' => 'حساب زیرمجموعه انتخاب‌شده نامعتبر است یا متعلق به این نماینده نمی‌باشد.',
            'package_id.exists' => 'پکیج انتخاب شده معتبر نیست، غیرفعال است یا متعلق به این نماینده نمی‌باشد.',
            'name.required' => 'وارد کردن نام کسب‌وکار / کلینیک الزامی است.',
            'name.min' => 'نام کسب‌وکار باید حداقل ۲ کاراکتر باشد.',
            'subdomain.required' => 'انتخاب آدرس سابدامین اختصاصی الزامی است.',
            'subdomain.min' => 'سابدامین باید حداقل ۳ کاراکتر باشد.',
            'subdomain.regex' => 'سابدامین فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره (-) باشد.',
            'subdomain.unique' => 'این سابدامین قبلاً توسط سازمان دیگری ثبت شده است.',
            'subdomain.not_in' => 'این سابدامین سیستمی و رزرو شده است و امکان استفاده از آن وجود ندارد.',
            'admin_name.required' => 'وارد کردن نام و نام خانوادگی مدیر سامانه الزامی است.',
            'admin_email.required' => 'وارد کردن نشانی ایمیل مدیر سامانه الزامی است.',
            'admin_email.email' => 'فرمت ایمیل وارد شده معتبر نمی‌باشد.',
            'admin_email.unique' => 'این نشانی ایمیل قبلاً در سامانه ثبت شده است.',
            'admin_mobile.required' => 'وارد کردن شماره تماس همراه مدیر سامانه الزامی است.',
            'admin_mobile.regex' => 'فرمت شماره موبایل نامعتبر است. شماره باید ۱۱ رقمی بوده و با ۰۹ آغاز شود (مثال: ۰۹۱۲۳۴۵۶۷۸۹).',
            'admin_mobile.unique' => 'این شماره موبایل قبلاً در سامانه ثبت شده است.',
            'password.required' => 'وارد کردن کلمه عبور مدیر الزامی است.',
            'password.min' => 'کلمه عبور باید حداقل ۶ کاراکتر باشد.',
            'password.confirmed' => 'تکرار کلمه عبور با کلمه عبور اولیه مطابقت ندارد.',
            'invoice_number.max' => 'شماره فاکتور نمی‌تواند بیش از ۶۴ کاراکتر باشد.',
        ];
    }
}

