<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddOrderItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:module,feature'],
            'items.*.slug' => ['required', 'string', 'max:100'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'issue_invoice' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'حداقل باید یک ماژول یا قابلیت جهت افزودن به سفارش انتخاب فرمایید.',
            'items.min' => 'حداقل باید یک ماژول یا قابلیت جهت افزودن به سفارش انتخاب فرمایید.',
            'items.array' => 'فرمت اقلام انتخابی نامعتبر است.',
            'items.*.type.in' => 'نوع قلم انتخابی باید ماژول یا قابلیت باشد.',
            'items.*.price.integer' => 'مبلغ هر قلم باید یک عدد صحیح باشد.',
            'notes.max' => 'توضیحات نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
        ];
    }
}
