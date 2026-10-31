<?php

namespace Modules\SmsReseller\App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordAccountingDocumentRequest extends FormRequest
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
            'type' => 'nullable|string|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0.01',
            'income_category_id' => 'required_without:expense_category_id|nullable|exists:accounting_categories,id',
            'expense_category_id' => 'required_without:income_category_id|nullable|exists:accounting_categories,id',
            'tax_category_id' => 'nullable|exists:accounting_categories,id',
            'fund_account_id' => 'required|exists:accounting_fund_accounts,id',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'payment_id' => 'nullable',
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
            'type' => 'نوع سند',
            'amount' => 'مبلغ بدون مالیات',
            'tax' => 'مالیات بر ارزش افزوده',
            'total_amount' => 'مبلغ کل',
            'income_category_id' => 'سرفصل درآمد',
            'expense_category_id' => 'سرفصل هزینه',
            'tax_category_id' => 'سرفصل مالیات',
            'fund_account_id' => 'حساب بانکی / خزانه‌داری',
            'wallet_id' => 'کیف پول کاربر / مشتری',
            'payment_id' => 'شناسه پرداخت',
            'document_date' => 'تاریخ سند',
            'description' => 'شرح سند',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'وارد کردن مبلغ بدون مالیات الزامی است.',
            'amount.numeric' => 'مبلغ باید عددی معتبر باشد.',
            'amount.min' => 'مبلغ باید حداقل ۰.۰۱ ریال باشد.',
            'total_amount.required' => 'وارد کردن مبلغ کل الزامی است.',
            'total_amount.numeric' => 'مبلغ کل باید عددی معتبر باشد.',
            'total_amount.min' => 'مبلغ کل باید حداقل ۰.۰۱ ریال باشد.',
            'income_category_id.required_without' => 'انتخاب سرفصل درآمد یا سرفصل هزینه الزامی است.',
            'expense_category_id.required_without' => 'انتخاب سرفصل هزینه یا سرفصل درآمد الزامی است.',
            'income_category_id.exists' => 'سرفصل درآمد انتخاب شده در سیستم حسابداری معتبر نیست.',
            'expense_category_id.exists' => 'سرفصل هزینه انتخاب شده در سیستم حسابداری معتبر نیست.',
            'tax_category_id.exists' => 'سرفصل مالیات انتخاب شده در سیستم حسابداری معتبر نیست.',
            'fund_account_id.required' => 'انتخاب حساب بانکی یا خزانه‌داری الزامی است.',
            'fund_account_id.exists' => 'حساب بانکی یا خزانه‌داری انتخاب شده معتبر نیست.',
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
                $fa = \Modules\Accounting\App\Models\FundAccount::find($fundAccountId);
                if ($fa && ((method_exists($fa, 'isWalletAccount') && $fa->isWalletAccount()) || str_contains($fa->name, 'کیف پول'))) {
                    if (empty($this->input('wallet_id'))) {
                        $validator->errors()->add('wallet_id', 'برای حساب خزانه‌داری «کیف پول کاربران»، انتخاب کاربر / مشتری متناظر الزامی است.');
                    }
                }
            }
        });
    }
}
