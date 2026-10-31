<?php

namespace Modules\SmsReseller\App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Accounting\App\Models\FundAccount;

class RecordPanelExpenseDocumentRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:accounting_categories,id',
            'tax_category_id' => 'nullable|exists:accounting_categories,id',
            'fund_account_id' => 'required|exists:accounting_fund_accounts,id',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'payment_id' => 'nullable|exists:sms_reseller_panel_payments,id',
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
            'amount' => 'مبلغ شارژ بدون مالیات',
            'tax' => 'مالیات بر ارزش افزوده',
            'total_amount' => 'مبلغ کل شارژ',
            'expense_category_id' => 'سرفصل هزینه شارژ پنل',
            'tax_category_id' => 'سرفصل مالیات بر ارزش افزوده',
            'fund_account_id' => 'حساب بانکی / خزانه‌داری',
            'wallet_id' => 'کیف پول کاربر / مشتری',
            'payment_id' => 'شناسه پرداخت پنل',
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
            'amount.required' => 'وارد کردن مبلغ شارژ بدون مالیات الزامی است.',
            'amount.numeric' => 'مبلغ شارژ باید عددی معتبر باشد.',
            'amount.min' => 'مبلغ شارژ باید حداقل ۰.۰۱ ریال باشد.',
            'total_amount.required' => 'وارد کردن مبلغ کل شارژ الزامی است.',
            'total_amount.numeric' => 'مبلغ کل شارژ باید عددی معتبر باشد.',
            'total_amount.min' => 'مبلغ کل شارژ باید حداقل ۰.۰۱ ریال باشد.',
            'expense_category_id.required' => 'انتخاب سرفصل هزینه شارژ پنل الزامی است.',
            'expense_category_id.exists' => 'سرفصل هزینه انتخاب شده در سیستم حسابداری معتبر نیست.',
            'tax_category_id.exists' => 'سرفصل مالیات انتخاب شده در سیستم حسابداری معتبر نیست.',
            'fund_account_id.required' => 'انتخاب حساب بانکی یا خزانه‌داری پرداختی الزامی است.',
            'fund_account_id.exists' => 'حساب بانکی یا خزانه‌داری انتخاب شده معتبر نیست.',
            'wallet_id.exists' => 'کیف پول کاربر انتخاب شده معتبر نمی‌باشد.',
            'payment_id.exists' => 'شناسه شارژ پنل انتخاب شده در سیستم یافت نشد.',
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
