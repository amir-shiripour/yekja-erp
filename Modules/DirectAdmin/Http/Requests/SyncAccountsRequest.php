<?php

namespace Modules\DirectAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncAccountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'server_id' => 'required|exists:da_servers,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'server_id' => 'سرور دایرکت ادمین',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required' => 'شناسه سرور جهت همگام‌سازی الزامی است.',
            'server_id.exists' => 'سرور انتخاب شده در سیستم نامعتبر است.',
        ];
    }
}
