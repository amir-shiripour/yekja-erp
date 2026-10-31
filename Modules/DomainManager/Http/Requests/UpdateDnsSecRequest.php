<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDnsSecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'key_tag' => 'required|integer',
            'algorithm' => 'required|integer',
            'digest_type' => 'required|integer',
            'digest' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'key_tag' => 'Key Tag',
            'algorithm' => 'الگوریتم رمزنگاری',
            'digest_type' => 'نوع دایجست (Digest Type)',
            'digest' => 'مقدار دایجست (Digest)',
        ];
    }

    public function messages(): array
    {
        return [
            'key_tag.required' => 'فیلد Key Tag الزامی است.',
            'algorithm.required' => 'انتخاب الگوریتم الزامی است.',
            'digest_type.required' => 'انتخاب نوع Digest الزامی است.',
            'digest.required' => 'وارد کردن مقدار Digest الزامی است.',
        ];
    }
}
