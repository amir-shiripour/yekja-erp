<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTenantBrandingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Rules specifically for tenant branding validation.
     *
     * @return array<string, array>
     */
    public static function brandingRules(): array
    {
        return [
            'brandTitle' => ['required', 'string', 'max:100'],
            'brandPrimaryColor' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'supportPhone' => ['nullable', 'string', 'max:25'],
            'supportEmail' => ['nullable', 'email', 'max:100'],
        ];
    }

    /**
     * Messages specifically for tenant branding validation.
     *
     * @return array<string, string>
     */
    public static function brandingMessages(): array
    {
        return [
            'brandTitle.required' => 'عنوان برند سامانه الزامی است.',
            'brandTitle.max' => 'عنوان برند نمی‌تواند بیش از ۱۰۰ کاراکتر باشد.',
            'brandPrimaryColor.required' => 'رنگ اصلی برند الزامی است.',
            'brandPrimaryColor.regex' => 'فرمت رنگ انتخابی معتبر نیست (کد هگز معتبر مانند #4f46e5).',
            'supportPhone.max' => 'شماره تماس پشتیبانی نمی‌تواند بیش از ۲۵ کاراکتر باشد.',
            'supportEmail.email' => 'فرمت ایمیل پشتیبانی نامعتبر است.',
            'supportEmail.max' => 'ایمیل پشتیبانی نمی‌تواند بیش از ۱۰۰ کاراکتر باشد.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array>
     */
    public function rules(): array
    {
        return self::brandingRules();
    }

    /**
     * Custom Persian validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::brandingMessages();
    }
}
