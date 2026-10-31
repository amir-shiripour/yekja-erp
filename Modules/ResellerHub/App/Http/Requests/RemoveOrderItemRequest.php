<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        if ($this->has('items') && is_array($this->input('items'))) {
            return [
                'items' => ['required', 'array', 'min:1'],
                'items.*.type' => ['required', 'string', 'in:module,feature'],
                'items.*.slug' => ['required', 'string', 'max:100'],
                'items.*.title' => ['nullable', 'string', 'max:150'],
            ];
        }

        return [
            'type' => ['required', 'string', 'in:module,feature'],
            'slug' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'اقلام مورد نظر جهت حذف انتخاب نشده‌اند.',
            'items.min' => 'حداقل یک قلم برای حذف باید مشخص شود.',
            'type.required' => 'نوع قلم (ماژول یا قابلیت) الزامی است.',
            'type.in' => 'نوع قلم باید ماژول یا قابلیت باشد.',
            'slug.required' => 'شناسه ماژول یا قابلیت الزامی است.',
        ];
    }
}
