<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveModulePricingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pricings' => ['required', 'array', 'min:1'],
            'pricings.*.module_slug' => ['required', 'string', 'max:100'],
            'pricings.*.feature_key' => ['nullable', 'string', 'max:150'],
            'pricings.*.feature_title' => ['nullable', 'string', 'max:200'],
            'pricings.*.monthly_price' => ['nullable', 'integer', 'min:0'],
            'pricings.*.quarterly_price' => ['nullable', 'integer', 'min:0'],
            'pricings.*.semi_annual_price' => ['nullable', 'integer', 'min:0'],
            'pricings.*.yearly_price' => ['nullable', 'integer', 'min:0'],
            'pricings.*.setup_fee' => ['nullable', 'integer', 'min:0'],
            'pricings.*.is_active' => ['nullable', 'boolean'],
            'pricings.*.description' => ['nullable', 'string', 'max:500'],
            'enable_module_itemized_pricing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'pricings.required' => 'حداقل یک تعرفه برای ذخیره ارسال فرمایید.',
            'pricings.array' => 'فرمت اطلاعات تعرفه‌ها نامعتبر است.',
            'pricings.*.module_slug.required' => 'شناسه ماژول الزامی است.',
            'pricings.*.monthly_price.min' => 'قیمت ماهانه نمی‌تواند منفی باشد.',
            'pricings.*.yearly_price.min' => 'قیمت سالانه نمی‌تواند منفی باشد.',
        ];
    }
}