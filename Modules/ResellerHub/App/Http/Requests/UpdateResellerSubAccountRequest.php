<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;

class UpdateResellerSubAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => ResellerHelper::normalizeMobile($this->input('mobile'))]);
        }
    }

    public function rules(): array
    {
        $resellerId = Reseller::where('user_id', auth()->id())->value('id')
            ?: session('active_reseller_id');

        $current = $this->route('subAccount');
        $currentId = is_object($current) ? $current->getKey() : $current;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
                // Ignore the record being edited so saving without changes never fails.
                Rule::unique('reseller_sub_accounts', 'mobile')
                    ->where(fn($q) => $resellerId ? $q->where('reseller_id', $resellerId) : $q)
                    ->whereNull('deleted_at')
                    ->ignore($currentId),
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'max_invoices' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'limits' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام و نام خانوادگی الزامی است.',
            'name.min' => 'نام و نام خانوادگی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام و نام خانوادگی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل باید ۱۱ رقمی معتبر باشد (مثال: ۰۹۱۲۳۴۵۶۷۸۹).',
            'mobile.unique' => 'این شماره موبایل قبلاً برای یک حساب زیرمجموعه دیگر ثبت شده است.',
            'company_name.max' => 'نام شرکت یا مجموعه نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'max_invoices.integer' => 'سقف فاکتور باید یک عدد صحیح باشد.',
        ];
    }
}
