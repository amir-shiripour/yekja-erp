<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNameserversRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ns1' => 'required|string|max:191',
            'ns2' => 'required|string|max:191',
            'ns3' => 'nullable|string|max:191',
            'ns4' => 'nullable|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'ns1' => 'Nameserver 1',
            'ns2' => 'Nameserver 2',
            'ns3' => 'Nameserver 3',
            'ns4' => 'Nameserver 4',
        ];
    }

    public function messages(): array
    {
        return [
            'ns1.required' => 'وارد کردن حداقل Nameserver 1 الزامی است.',
            'ns2.required' => 'وارد کردن حداقل Nameserver 2 الزامی است.',
        ];
    }
}
