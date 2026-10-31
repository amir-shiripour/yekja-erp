<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'host' => 'required|string|max:191',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:191',
            'password' => 'nullable|string',
            'ssl' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام نمایشی سرور',
            'host' => 'آدرس هاست / دامنه / IP',
            'port' => 'پورت اتصال',
            'username' => 'نام کاربری نماینده',
            'password' => 'رمز عبور یا کلید لاگین',
            'ssl' => 'اتصال امن SSL',
            'is_active' => 'وضعیت فعال',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام نمایشی سرور الزامی است.',
            'host.required' => 'وارد کردن آدرس هاست یا آی‌پی سرور الزامی است.',
            'port.required' => 'پورت اتصال به سرور الزامی است.',
            'port.integer' => 'پورت باید یک عدد معتبر باشد.',
            'username.required' => 'نام کاربری حساب نماینده الزامی است.',
        ];
    }
}
