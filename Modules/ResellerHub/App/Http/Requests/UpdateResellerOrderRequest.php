<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResellerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('renewal_price') && is_string($this->renewal_price)) {
            $this->merge([
                'renewal_price' => str_replace([',', ' '], '', $this->renewal_price)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:pending,active,inactive,cancelled,suspended,completed'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,quarterly,semi_annual,yearly,custom'],
            'start_date' => ['nullable'],
            'end_date' => ['nullable'],
            'renewal_date' => ['nullable', 'string'],
            'renewal_price_type' => ['nullable', 'string', 'in:auto,manual'],
            'renewal_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'وضعیت انتخابی سفارش معتبر نیست.',
        ];
    }
}