<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResellerInvoiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:unpaid,paid,overdue,cancelled,draft'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'انتخاب وضعیت فاکتور الزامی است.',
            'status.in' => 'وضعیت فاکتور انتخاب‌شده معتبر نمی‌باشد.',
        ];
    }
}
