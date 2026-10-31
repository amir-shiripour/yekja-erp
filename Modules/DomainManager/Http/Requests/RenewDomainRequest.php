<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'years' => 'required|integer|min:1|max:10',
        ];
    }

    public function attributes(): array
    {
        return [
            'years' => 'مدت زمان تمدید',
        ];
    }

    public function messages(): array
    {
        return [
            'years.required' => 'تعیین مدت زمان تمدید الزامی است.',
            'years.min' => 'حداقل مدت تمدید ۱ سال است.',
            'years.max' => 'حداکثر مدت تمدید ۱۰ سال است.',
        ];
    }
}
