<?php

namespace Modules\DomainManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddDnsSecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tag' => 'required|string|max:191',
            'algorithm' => 'required|string|max:191',
            'digestType' => 'required|string|max:191',
            'digest' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'tag' => 'Key Tag',
            'algorithm' => 'الگوریتم رمزنگاری',
            'digestType' => 'نوع دایجست (Digest Type)',
            'digest' => 'مقدار دایجست (Digest)',
        ];
    }

    public function messages(): array
    {
        return [
            'tag.required' => 'فیلد Key Tag الزامی است.',
            'algorithm.required' => 'انتخاب الگوریتم الزامی است.',
            'digestType.required' => 'انتخاب نوع Digest الزامی است.',
            'digest.required' => 'وارد کردن مقدار Digest الزامی است.',
        ];
    }
}
