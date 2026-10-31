<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'modify_type' => 'nullable|string|in:package,custom',
            'package' => 'required_if:modify_type,package|nullable|string',
            'quota' => 'nullable|string',
            'bandwidth' => 'nullable|string',
            'vdomains' => 'nullable|string',
            'nemailf' => 'nullable|string',
            'mysql' => 'nullable|string',
            'ftps' => 'nullable|string',
            'php' => 'nullable',
            'cgi' => 'nullable',
            'ssl' => 'nullable',
            'ssh' => 'nullable',
            'dnscontrol' => 'nullable',
        ];
    }

    public function attributes(): array
    {
        return [
            'modify_type' => 'نوع ویرایش',
            'package' => 'پکیج هاستینگ',
            'quota' => 'سهمیه فضا',
            'bandwidth' => 'پهنای باند ماهانه',
            'vdomains' => 'تعداد دامنه‌ها',
            'nemailf' => 'تعداد حساب‌های ایمیل',
            'mysql' => 'تعداد پایگاه داده‌ها',
            'ftps' => 'تعداد حساب‌های FTP',
        ];
    }

    public function messages(): array
    {
        return [
            'package.required_if' => 'انتخاب پکیج هاستینگ الزامی است.',
        ];
    }
}
