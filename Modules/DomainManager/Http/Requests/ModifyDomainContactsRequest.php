<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifyDomainContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'holder' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'country' => 'required|string|max:2',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:255',
            'postCode' => 'required|string|max:50',
            'phone' => 'required|string|max:50',
            'organization' => 'nullable|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'holder' => 'شناسه مالک',
            'name' => 'نام و نام خانوادگی',
            'email' => 'پست الکترونیک',
            'country' => 'کشور',
            'state' => 'استان',
            'city' => 'شهر',
            'address' => 'آدرس',
            'postCode' => 'کد پستی',
            'phone' => 'شماره تماس',
            'organization' => 'سازمان / شرکت',
        ];
    }

    public function messages(): array
    {
        return [
            'holder.required' => 'شناسه مالک WHOIS الزامی است.',
            'name.required' => 'نام و نام خانوادگی مخاطب الزامی است.',
            'email.required' => 'ایمیل معتبر الزامی است.',
            'email.email' => 'فرمت ایمیل وارد شده صحیح نمی‌باشد.',
            'country.required' => 'کد دوحرفی کشور الزامی است.',
            'state.required' => 'نام استان الزامی است.',
            'city.required' => 'نام شهر الزامی است.',
            'address.required' => 'آدرس کامل الزامی است.',
            'postCode.required' => 'کد پستی الزامی است.',
            'phone.required' => 'شماره تماس الزامی است.',
        ];
    }
}
