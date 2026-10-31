<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveChildHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'hostname' => 'required|string|max:191',
            'ip' => 'required|ip',
            'old_hostname' => 'nullable|string|max:191',
            'old_ip' => 'nullable|ip',
        ];
    }

    public function attributes(): array
    {
        return [
            'hostname' => 'نام هاست اختصاصی',
            'ip' => 'آدرس IP هاست',
            'old_hostname' => 'نام هاست قبلی',
            'old_ip' => 'آدرس IP قبلی',
        ];
    }

    public function messages(): array
    {
        return [
            'hostname.required' => 'نام هاست چایلد نیم‌سرور الزامی است.',
            'ip.required' => 'آدرس IP معتبر برای چایلد هاست الزامی است.',
            'ip.ip' => 'آدرس وارد شده باید یک IPv4 یا IPv6 معتبر باشد.',
        ];
    }
}
