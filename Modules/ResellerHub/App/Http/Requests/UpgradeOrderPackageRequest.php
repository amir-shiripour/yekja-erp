<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeOrderPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'new_package_id' => ['required', 'integer', 'exists:reseller_packages,id'],
            'calculation_mode' => ['nullable', 'string', 'in:prorated,full,custom'],
            'custom_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'issue_invoice' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_package_id.required' => 'انتخاب پکیج جدید برای ارتقا الزامی است.',
            'new_package_id.exists' => 'پکیج جدید انتخاب‌شده در سیستم یافت نشد.',
            'calculation_mode.in' => 'نحوه محاسبه مبلغ مابه‌التفاوت نامعتبر است.',
            'custom_price.numeric' => 'مبلغ سفارشی باید یک مقدار عددی باشد.',
            'custom_price.min' => 'مبلغ سفارشی نمی‌تواند منفی باشد.',
            'notes.max' => 'یادداشت ارتقا نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
        ];
    }
}
