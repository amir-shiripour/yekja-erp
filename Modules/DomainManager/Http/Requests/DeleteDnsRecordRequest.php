<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDnsRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'record_id' => 'required|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'record_id' => 'شناسه رکورد DNS',
        ];
    }

    public function messages(): array
    {
        return [
            'record_id.required' => 'شناسه رکورد DNS جهت حذف الزامی است.',
        ];
    }
}
