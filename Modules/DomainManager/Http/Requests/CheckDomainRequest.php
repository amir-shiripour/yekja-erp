<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'domain' => 'required|string|max:191',
            'reseller_id' => 'required|exists:domain_resellers,id',
            'tlds' => 'nullable|array',
            'tlds.*' => 'string|max:30',
        ];
    }

    public function attributes(): array
    {
        return [
            'domain' => 'نام دامنه',
            'reseller_id' => 'کارگزاری انتخابی',
            'tlds' => 'پسوندها',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'وارد کردن نام دامنه الزامی است.',
            'reseller_id.required' => 'شناسه کارگزاری الزامی است.',
            'reseller_id.exists' => 'کارگزاری انتخاب‌شده معتبر نمی‌باشد.',
        ];
    }
}
