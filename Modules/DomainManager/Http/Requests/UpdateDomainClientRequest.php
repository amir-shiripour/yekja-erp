<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainClientRequest extends FormRequest
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
            'client_id' => 'شناسه مشتری',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'مشتری انتخاب‌شده در سیستم معتبر نمی‌باشد.',
        ];
    }
}
