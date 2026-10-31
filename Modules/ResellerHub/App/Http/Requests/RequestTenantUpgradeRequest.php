<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestTenantUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'package_id' => ['nullable', 'integer', 'exists:reseller_packages,id'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:1200'],
            'billing_cycle' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
            'requested_quota' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.exists' => 'پکیج انتخابی نامعتبر است.',
            'message.max' => 'متن پیام نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
            'requested_quota.max' => 'مشخصات درخواست نمی‌تواند بیش از ۱۰۰ کاراکتر باشد.',
        ];
    }
}
