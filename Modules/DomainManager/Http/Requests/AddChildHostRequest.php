<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddChildHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ns' => 'required|string|max:191',
            'ip' => 'required|ip',
        ];
    }

    public function attributes(): array
    {
        return [
            'ns' => 'نام چایلد هاست',
            'ip' => 'آدرس IP هاست',
        ];
    }

    public function messages(): array
    {
        return [
            'ns.required' => 'وارد کردن نام کارگزار فرزند (Child Host) الزامی است.',
            'ip.required' => 'وارد کردن آدرس IP معتبر الزامی است.',
            'ip.ip' => 'آدرس IP وارد شده معتبر نیست.',
        ];
    }
}
