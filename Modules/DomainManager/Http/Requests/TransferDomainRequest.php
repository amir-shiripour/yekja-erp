<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'domain' => 'required|string|max:191',
            'reseller_id' => 'required|exists:domain_resellers,id',
            'auth_code' => 'required|string|max:191',
            'ns1' => 'required|string|max:191',
            'ns2' => 'required|string|max:191',
            'ns3' => 'nullable|string|max:191',
            'ns4' => 'nullable|string|max:191',
            'holder_handle' => 'required|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'domain' => 'نام دامنه',
            'reseller_id' => 'کارگزاری انتخابی',
            'auth_code' => 'کد محرمانه انتقال (EPP / Auth Code)',
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
            'auth_code.required' => 'وارد کردن کد محرمانه انتقال (EPP Code) الزامی است.',
            'ns1.required' => 'وارد کردن حداقل نیم‌سرور اول الزامی است.',
            'ns2.required' => 'وارد کردن حداقل نیم‌سرور دوم الزامی است.',
            'holder_handle.required' => 'وارد کردن شناسه مخاطب مالک دامنه الزامی است.',
        ];
    }
}
