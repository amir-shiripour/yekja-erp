<?php

namespace Modules\SmsReseller\App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Accounting\App\Models\FundAccount;

class RecordCumulativeReconciliationRequest extends FormRequest
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
            'income_category_id' => 'required|exists:accounting_categories,id',
            'expense_category_id' => 'required|exists:accounting_categories,id',
            'tax_category_id' => 'nullable|exists:accounting_categories,id',
            'fund_account_id' => 'required|exists:accounting_fund_accounts,id',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'document_date' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'income_category_id' => 'سرفصل درآمد فروش پیامک',
            'expense_category_id' => 'سرفصل هزینه شارژ پنل',
            'tax_category_id' => 'سرفصل مالیات بر ارزش افزوده',
            'fund_account_id' => 'حساب بانکی / خزانه‌داری طرف حساب',
            'wallet_id' => 'کیف پول کاربر / مشتری',
            'document_date' => 'تاریخ سند تجمیعی',
            'description' => 'شرح سند',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'income_category_id.required' => 'انتخاب سرفصل درآمد فروش پیامک الزامی است.',
            'income_category_id.exists' => 'سرفصل درآمد انتخاب شده در سیستم حسابداری وجود ندارد.',
            'expense_category_id.required' => 'انتخاب سرفصل هزینه شارژ پنل الزامی است.',
            'expense_category_id.exists' => 'سرفصل هزینه انتخاب شده در سیستم حسابداری وجود ندارد.',
            'tax_category_id.exists' => 'سرفصل مالیات بر ارزش افزوده انتخاب شده معتبر نیست.',
            'fund_account_id.required' => 'انتخاب حساب بانکی یا خزانه‌داری طرف حساب الزامی است.',
            'fund_account_id.exists' => 'حساب بانکی یا خزانه‌داری انتخاب شده در سیستم وجود ندارد.',
            'wallet_id.exists' => 'کیف پول کاربر انتخاب شده معتبر نمی‌باشد.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fundAccountId = $this->input('fund_account_id');
            if ($fundAccountId) {
                $fa = FundAccount::find($fundAccountId);
                if ($fa && ((method_exists($fa, 'isWalletAccount') && $fa->isWalletAccount()) || str_contains($fa->name, 'کیف پول'))) {
                    if (empty($this->input('wallet_id'))) {
                        $validator->errors()->add('wallet_id', 'برای حساب خزانه‌داری «کیف پول کاربران»، انتخاب کاربر / مشتری متناظر الزامی است.');
                    }
                }
            }
        });
    }
}
