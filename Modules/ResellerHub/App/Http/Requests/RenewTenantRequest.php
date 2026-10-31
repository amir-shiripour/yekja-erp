<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'months' => ['required', 'integer', 'min:1', 'max:120'],
            'issue_invoice' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom Persian validation messages.
     */
    public function messages(): array
    {
        return [
            'months.required' => 'مدت زمان تمدید الزامی است.',
            'months.integer' => 'مدت زمان تمدید باید عدد باشد.',
            'months.min' => 'حداقل مدت تمدید ۱ ماه است.',
            'months.max' => 'حداکثر مدت تمدید ۱۲۰ ماه است.',
        ];
    }
}
