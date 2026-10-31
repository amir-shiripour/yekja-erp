<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'domain' => 'required|string|max:191',
            'reseller_id' => 'required|exists:domain_resellers,id',
            'registration_mode' => 'nullable|string|in:manual,api',
            'years' => 'required|integer|min:1|max:10',
            'ns1' => 'required|string|max:191',
            'ns2' => 'required|string|max:191',
            'ns3' => 'nullable|string|max:191',
            'ns4' => 'nullable|string|max:191',
            'holder_handle' => 'nullable|string|max:191',
        ];

        if ($this->input('registration_mode') === 'api') {
            $rules['holder_handle'] = 'required|string|max:191';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'domain' => 'نام دامنه',
            'reseller_id' => 'کارگزاری انتخابی',
            'registration_mode' => 'نوع ثبت دامنه',
            'years' => 'مدت زمان ثبت دامنه',
            'ns1' => 'Nameserver 1',
            'ns2' => 'Nameserver 2',
            'ns3' => 'Nameserver 3',
            'ns4' => 'Nameserver 4',
            'holder_handle' => 'شناسه کاربری مالک در کارگزار',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'وارد کردن نام دامنه الزامی است.',
            'reseller_id.required' => 'شناسه کارگزاری الزامی است.',
            'reseller_id.exists' => 'کارگزاری انتخابی معتبر نمی‌باشد.',
            'years.required' => 'انتخاب مدت زمان ثبت دامنه الزامی است.',
            'years.min' => 'حداقل مدت ثبت ۱ سال است.',
            'years.max' => 'حداکثر مدت ثبت ۱۰ سال است.',
            'ns1.required' => 'وارد کردن حداقل نیم‌سرور اول الزامی است.',
            'ns2.required' => 'وارد کردن حداقل نیم‌سرور دوم الزامی است.',
            'holder_handle.required' => 'وارد کردن شناسه مخاطب مالک دامنه الزامی است.',
        ];
    }
}
