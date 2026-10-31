<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('email') && $this->filled('domain')) {
            $cleanDomain = preg_replace('/^https?:\/\//i', '', trim((string)$this->input('domain')));
            $cleanDomain = preg_replace('/^www\./i', '', $cleanDomain);
            $this->merge(['email' => 'info@' . $cleanDomain]);
        }
    }

    public function rules(): array
    {
        return [
            'da_server_id' => 'required|exists:da_servers,id',
            'client_id' => 'nullable|exists:clients,id',
            'order_id' => 'nullable|integer',
            'username' => 'required|string|alpha_num|min:4|max:16|unique:da_accounts,username',
            'domain' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'package' => 'required|string',
            'password' => 'required|string|min:6',
        ];
    }

    public function attributes(): array
    {
        return [
            'da_server_id' => 'سرور هاستینگ',
            'client_id' => 'مشتری CRM',
            'username' => 'نام کاربری',
            'domain' => 'نام دامنه اصلی',
            'email' => 'ایمیل مدیر هاست',
            'package' => 'پلن / پکیج هاستینگ',
            'password' => 'رمز عبور',
        ];
    }

    public function messages(): array
    {
        return [
            'da_server_id.required' => 'انتخاب سرور دایرکت ادمین الزامی است.',
            'da_server_id.exists' => 'سرور انتخاب شده در سیستم نامعتبر است.',
            'username.required' => 'وارد کردن نام کاربری هاست الزامی است.',
            'username.alpha_num' => 'نام کاربری فقط می‌تواند شامل حروف انگلیسی و اعداد باشد.',
            'username.min' => 'نام کاربری باید حداقل ۴ کاراکتر باشد.',
            'username.max' => 'نام کاربری حداکثر می‌تواند ۱۶ کاراکتر باشد.',
            'username.unique' => 'این نام کاربری قبلاً در سیستم ثبت شده است و تکراری می‌باشد.',
            'domain.required' => 'وارد کردن دامنه اصلی هاست الزامی است.',
            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل وارد شده نامعتبر است.',
            'package.required' => 'انتخاب پکیج هاستینگ الزامی است.',
            'password.required' => 'وارد کردن رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
        ];
    }
}
