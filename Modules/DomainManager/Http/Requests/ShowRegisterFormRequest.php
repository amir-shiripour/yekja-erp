<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRegisterFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'domain' => 'nullable|string|max:191',
            'reseller_id' => 'nullable|exists:domain_resellers,id',
            'action' => 'nullable|string|in:register,transfer',
        ];
    }

    public function attributes(): array
    {
        return [
            'domain' => 'نام دامنه',
            'reseller_id' => 'شناسه کارگزاری',
            'action' => 'نوع عملیات',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'نام دامنه الزامی است.',
            'reseller_id.required' => 'انتخاب کارگزاری الزامی است.',
            'reseller_id.exists' => 'کارگزاری انتخاب‌شده معتبر نیست.',
            'action.required' => 'تعیین نوع عملیات ثبت یا انتقال الزامی است.',
            'action.in' => 'نوع عملیات فقط می‌تواند ثبت (register) یا انتقال (transfer) باشد.',
        ];
    }
}
