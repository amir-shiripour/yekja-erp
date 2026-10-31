<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeAccountPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string|min:6',
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => 'رمز عبور جدید',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'وارد کردن رمز عبور جدید الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
        ];
    }
}
