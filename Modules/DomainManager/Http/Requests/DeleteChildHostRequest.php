<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteChildHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ns' => 'required|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'ns' => 'نام کارگزار فرزند',
        ];
    }

    public function messages(): array
    {
        return [
            'ns.required' => 'نام کارگزار فرزند جهت حذف الزامی است.',
        ];
    }
}
