<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $isManual = ($this->input('connection_type') === 'manual') || ($this->input('auth_userid') === 'manual');

        return [
            'name' => 'required|string|max:191',
            'connection_type' => 'nullable|string|in:api,manual',
            'auth_userid' => 'required|string|max:191',
            'api_key' => $isManual ? 'nullable|string' : 'required|string',
            'currency' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
            'balance' => 'nullable|numeric|min:0',
            'locked_balance' => 'nullable|numeric|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام کارگزاری',
            'auth_userid' => 'شناسه نمایندگی (Reseller ID)',
            'api_key' => 'کلید امنیتی API Key',
            'is_active' => 'وضعیت فعال',
            'balance' => 'موجودی',
            'locked_balance' => 'موجودی مسدود شده',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام کارگزاری الزامی است.',
            'auth_userid.required' => 'وارد کردن شناسه نمایندگی (Reseller ID) الزامی است.',
            'api_key.required' => 'وارد کردن کلید امنیتی API Key الزامی است.',
        ];
    }
}
