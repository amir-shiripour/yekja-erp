<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'مشتری متصل در CRM',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'مشتری انتخاب شده در CRM نامعتبر است.',
        ];
    }
}
