<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantDomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare inputs for validation (clean and normalize domain string).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('custom_domain') && is_string($this->input('custom_domain'))) {
            $cleaned = preg_replace('#^https?://#i', '', trim($this->input('custom_domain')));
            $cleaned = rtrim(strtolower($cleaned), '/');
            $this->merge(['custom_domain' => $cleaned ?: null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'custom_domain' => ['nullable', 'string', 'max:190'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'custom_domain.max' => __('resellerhub::messages.domain_branding.validation.custom_domain_max'),
        ];
    }
}
