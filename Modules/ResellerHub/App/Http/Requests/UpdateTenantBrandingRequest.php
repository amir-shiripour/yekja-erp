<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantBrandingRequest extends FormRequest
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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'brand_title' => ['required', 'string', 'max:100'],
            'brand_primary_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'brand_footer_text' => ['nullable', 'string', 'max:250'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:100'],
            'brand_logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'brand_favicon' => ['nullable', 'file', 'mimes:png,ico,svg,jpg,jpeg', 'max:1024'],
            'remove_brand_logo' => ['nullable', 'boolean'],
            'remove_brand_favicon' => ['nullable', 'boolean'],
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
            'brand_title.required' => __('resellerhub::messages.domain_branding.validation.brand_title_required'),
            'brand_title.max' => __('resellerhub::messages.domain_branding.validation.brand_title_max'),
            'brand_primary_color.required' => __('resellerhub::messages.domain_branding.validation.brand_color_required'),
            'brand_primary_color.regex' => __('resellerhub::messages.domain_branding.validation.brand_color_regex'),
            'support_email.email' => __('resellerhub::messages.domain_branding.validation.support_email_email'),
            'brand_logo.file' => __('resellerhub::messages.domain_branding.validation.logo_file'),
            'brand_logo.image' => __('resellerhub::messages.domain_branding.validation.logo_file'),
            'brand_logo.max' => __('resellerhub::messages.domain_branding.validation.logo_max'),
            'brand_logo.mimes' => __('resellerhub::messages.domain_branding.validation.logo_mimes'),
            'brand_favicon.file' => __('resellerhub::messages.domain_branding.validation.favicon_file'),
            'brand_favicon.max' => __('resellerhub::messages.domain_branding.validation.favicon_max'),
            'brand_favicon.mimes' => __('resellerhub::messages.domain_branding.validation.favicon_mimes'),
        ];
    }
}
