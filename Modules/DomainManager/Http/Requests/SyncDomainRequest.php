<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'domain_id' => 'required|exists:domain_records,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'domain_id' => 'شناسه دامنه',
        ];
    }

    public function messages(): array
    {
        return [
            'domain_id.required' => 'شناسه رکورد دامنه الزامی است.',
            'domain_id.exists' => 'رکورد دامنه مورد نظر یافت نشد.',
        ];
    }
}
