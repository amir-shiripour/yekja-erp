<?php

namespace Modules\ResellerHub\App\Http\Requests;

use App\Tenancy\TenantManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ResellerHub\App\Services\DomainVerificationService;

class PortalCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules specifically for subdomain validation.
     */
    public static function subdomainRules(?int $ignoreTenantId = null): array
    {
        $reserved = app(TenantManager::class)->getReservedSubdomains();
        $uniqueRule = Rule::unique('tenants', 'subdomain');
        if ($ignoreTenantId) {
            $uniqueRule->ignore($ignoreTenantId);
        }

        return [
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                $uniqueRule,
                Rule::notIn($reserved),
            ],
        ];
    }

    /**
     * Messages specifically for subdomain validation.
     */
    public static function subdomainMessages(): array
    {
        return [
            'subdomain.required' => 'لطفاً یک زیردامنه وارد کنید.',
            'subdomain.min' => 'طول زیردامنه باید حداقل ۳ کاراکتر باشد.',
            'subdomain.max' => 'طول زیردامنه حداکثر می‌تواند ۶۳ کاراکتر باشد.',
            'subdomain.regex' => 'زیردامنه فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد.',
            'subdomain.unique' => 'این زیردامنه قبلاً ثبت شده است.',
            'subdomain.not_in' => 'این زیردامنه سیستمی و رزرو شده است.',
        ];
    }

    /**
     * Rules specifically for custom domain validation.
     */
    public static function customDomainRules(): array
    {
        $domainService = app(DomainVerificationService::class);
        $reserved = app(TenantManager::class)->getReservedSubdomains();

        return [
            'custom_domain' => [
                'required',
                'string',
                'max:100',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                'unique:tenants,custom_domain',
                Rule::notIn($reserved),
                function ($attribute, $value, $fail) use ($domainService) {
                    if (!$domainService->isValidDomain($value)) {
                        $fail('فرمت دامنه نامعتبر است (مثال: company.com یا crm.company.ir).');
                    } elseif ($domainService->isReservedDomain($value)) {
                        $fail('این دامنه رزرو شده یا متعلق به سامانه اصلی است.');
                    } elseif (!$domainService->isDomainAvailable($value)) {
                        $fail('این دامنه قبلاً در سامانه ثبت شده است.');
                    }
                },
            ],
        ];
    }

    /**
     * Messages specifically for custom domain validation.
     */
    public static function customDomainMessages(): array
    {
        return [
            'custom_domain.required' => 'لطفاً آدرس دامنه اختصاصی خود را وارد کنید.',
            'custom_domain.regex' => 'فرمت دامنه نامعتبر است (مثال: company.com یا crm.company.ir).',
            'custom_domain.unique' => 'این دامنه قبلاً در سامانه ثبت شده است.',
            'custom_domain.not_in' => 'این دامنه رزرو شده یا متعلق به سامانه اصلی است.',
            'custom_domain.max' => 'طول نام دامنه نمی‌تواند بیش از ۱۰۰ کاراکتر باشد.',
        ];
    }

    /**
     * Rules for white-label branding step.
     */
    public static function brandingRules(): array
    {
        return [
            'brandTitle' => ['nullable', 'string', 'max:150'],
            'brandPrimaryColor' => ['nullable', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'brandFooterText' => ['nullable', 'string', 'max:300'],
            'supportPhone' => ['nullable', 'string', 'max:30'],
            'supportEmail' => ['nullable', 'email', 'max:150'],
            'brandLogo' => ['nullable', 'image', 'max:2048'],
            'brandFavicon' => ['nullable', 'mimes:ico,png,svg,webp', 'max:1024'],
        ];
    }

    /**
     * Messages for white-label branding step.
     */
    public static function brandingMessages(): array
    {
        return [
            'brandPrimaryColor.regex' => 'فرمت کد رنگ سازمانی معتبر نیست (مثال: #4f46e5).',
            'brandLogo.image' => 'فایل لوگو باید از نوع تصویر باشد.',
            'brandLogo.max' => 'حجم فایل لوگو نباید بیشتر از ۲ مگابایت باشد.',
            'brandFavicon.mimes' => 'فاوآیکون باید یکی از فرمت‌های ico، png، svg یا webp باشد.',
            'brandFavicon.max' => 'حجم فایل فاوآیکون نباید بیشتر از ۱ مگابایت باشد.',
            'supportEmail.email' => 'فرمت آدرس ایمیل پشتیبانی نامعتبر است.',
            'brandTitle.max' => 'عنوان برند نمی‌تواند بیش از ۱۵۰ کاراکتر باشد.',
            'brandFooterText.max' => 'متن فوتر نمی‌تواند بیش از ۳۰۰ کاراکتر باشد.',
            'supportPhone.max' => 'شماره تماس پشتیبانی نمی‌تواند بیش از ۳۰ کاراکتر باشد.',
        ];
    }

    /**
     * Rules for package selection step.
     */
    public static function packageRules(?int $resellerId = null): array
    {
        $existsRule = Rule::exists('reseller_packages', 'id')->where('is_active', true);
        if ($resellerId) {
            $existsRule->where('reseller_id', $resellerId);
        }

        return [
            'selectedPackageId' => ['required', 'integer', $existsRule],
        ];
    }

    /**
     * Messages for package selection step.
     */
    public static function packageMessages(): array
    {
        return [
            'selectedPackageId.required' => 'لطفاً یک پکیج انتخاب کنید.',
            'selectedPackageId.exists' => 'پکیج انتخاب‌شده نامعتبر، غیرفعال یا حذف شده است.',
        ];
    }

    /**
     * Combined rules for the entire portal checkout request.
     */
    public function rules(): array
    {
        return array_merge(
            self::packageRules(),
            [
                'billingPeriod' => ['nullable', 'string'],
                'domainType' => ['required', 'in:subdomain,custom_domain'],
                'subdomain' => self::subdomainRules()['subdomain'],
                'custom_domain' => [
                    Rule::requiredIf(fn() => $this->input('domainType') === 'custom_domain'),
                    'nullable',
                    ...self::customDomainRules()['custom_domain'],
                ],
                'whiteLabelType' => ['required', 'in:default,custom'],
            ],
            self::brandingRules()
        );
    }

    /**
     * Combined messages for the entire request.
     */
    public function messages(): array
    {
        return array_merge(
            self::packageMessages(),
            self::subdomainMessages(),
            self::customDomainMessages(),
            self::brandingMessages(),
            [
                'domainType.required' => 'نوع دامنه الزامی است.',
                'whiteLabelType.required' => 'نوع برندسازی الزامی است.',
            ]
        );
    }
}
