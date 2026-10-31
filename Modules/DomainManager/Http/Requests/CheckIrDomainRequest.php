<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckIrDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'domain' => 'required|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'domain' => 'نام دامنه',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'وارد کردن نام دامنه الزامی است.',
        ];
    }
}
