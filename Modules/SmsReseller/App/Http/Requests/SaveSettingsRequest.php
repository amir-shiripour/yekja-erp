<?php

namespace Modules\SmsReseller\App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'api_key' => 'required|string|max:500',
            'base_url' => 'required|url|max:255',
            'default_income_category_id' => 'nullable|exists:accounting_categories,id',
            'default_expense_category_id' => 'nullable|exists:accounting_categories,id',
            'default_tax_category_id' => 'nullable|exists:accounting_categories,id',
            'default_fund_account_id' => 'nullable|exists:accounting_fund_accounts,id',
            'default_wallet_fund_account_id' => 'nullable|exists:accounting_fund_accounts,id',
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'api_key' => 'کلید API لیمو پیامک',
            'base_url' => 'آدرس Base URL وب‌سرویس',
            'default_income_category_id' => 'سرفصل پیش‌فرض درآمد پیامک',
            'default_expense_category_id' => 'سرفصل پیش‌فرض هزینه شارژ پنل',
            'default_tax_category_id' => 'سرفصل پیش‌فرض مالیات بر ارزش افزوده',
            'default_fund_account_id' => 'حساب بانکی پیش‌فرض',
            'default_wallet_fund_account_id' => 'حساب خزانه‌داری کیف پول پیامک',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'api_key.required' => 'وارد کردن کلید API وب‌سرویس الزامی است.',
            'base_url.required' => 'وارد کردن آدرس Base URL وب‌سرویس الزامی است.',
            'base_url.url' => 'فرمت آدرس Base URL نامعتبر است. باید یک URL معتبر باشد.',
            'default_income_category_id.exists' => 'سرفصل پیش‌فرض درآمد در سیستم حسابداری معتبر نیست.',
            'default_expense_category_id.exists' => 'سرفصل پیش‌فرض هزینه در سیستم حسابداری معتبر نیست.',
            'default_tax_category_id.exists' => 'سرفصل پیش‌فرض مالیات در سیستم حسابداری معتبر نیست.',
            'default_fund_account_id.exists' => 'حساب بانکی پیش‌فرض انتخاب شده معتبر نیست.',
            'default_wallet_fund_account_id.exists' => 'حساب خزانه‌داری کیف پول انتخاب شده معتبر نیست.',
        ];
    }
}
