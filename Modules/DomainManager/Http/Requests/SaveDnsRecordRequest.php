<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDnsRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,SRV,NS,CAA',
            'value' => 'required|string',
            'ttl' => 'nullable|integer|min:60|max:86400',
            'priority' => 'nullable|integer|min:0|max:65535',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام رکورد / هاست',
            'type' => 'نوع رکورد DNS',
            'value' => 'مقدار / مقدار هدف رکورد',
            'ttl' => 'مدت زمان کش (TTL)',
            'priority' => 'اولویت (Priority)',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام رکورد الزامی است.',
            'type.required' => 'انتخاب نوع رکورد الزامی است.',
            'type.in' => 'نوع رکورد انتخاب‌شده نامعتبر است.',
            'value.required' => 'وارد کردن مقدار رکورد الزامی است.',
        ];
    }
}
