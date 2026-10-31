<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'brand_title' => ['nullable', 'string', 'max:150'],
            'brand_footer_text' => ['nullable', 'string', 'max:300'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'brand_logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,ico,avif,tiff,x-icon', 'max:2048'],
            'brand_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,gif,svg,webp,bmp,avif,tiff,x-icon', 'max:1024'],
            'remove_brand_logo' => ['nullable', 'boolean'],
            'remove_brand_favicon' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom Persian validation messages.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'وارد کردن نام شرکت / برند الزامی است.',
            'company_name.max' => 'نام شرکت نمی‌تواند بیش از ۱۵۰ کاراکتر باشد.',
            'support_email.email' => 'فرمت ایمیل پشتیبانی معتبر نمی‌باشد.',
            'brand_logo.file' => 'فایل لوگو باید یک تصویر معتبر باشد.',
            'brand_logo.mimes' => 'فرمت فایل لوگو معتبر نیست (فرمت‌های تصویری مانند PNG، JPG، SVG، WebP، GIF و... مجاز هستند).',
            'brand_logo.max' => 'حجم فایل لوگو نباید بیش از ۲ مگابایت باشد.',
            'brand_favicon.file' => 'فایل فاوآیکون باید یک تصویر یا آیکون معتبر باشد.',
            'brand_favicon.mimes' => 'فرمت فایل فاوآیکون معتبر نیست (فرمت‌های آیکون و تصویر مانند ICO، PNG، SVG، WebP، JPG و... مجاز هستند).',
            'brand_favicon.max' => 'حجم فایل فاوآیکون نباید بیش از ۱ مگابایت باشد.',
        ];
    }
}
