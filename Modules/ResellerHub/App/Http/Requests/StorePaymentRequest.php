<?php

namespace Modules\ResellerHub\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'use_wallet' => ['nullable', 'boolean'],
            'wallet_amount' => ['nullable'],
            'payment_items' => ['nullable', 'array'],
            'payment_items.*.method' => ['nullable', 'string'],
            'payment_items.*.amount' => ['nullable'],
            'payment_items.*.transaction_id' => ['nullable', 'string', 'max:191'],
            'payment_items.*.gateway' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'amount' => ['nullable'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
            'transaction_ref' => ['nullable', 'string', 'max:191'],
            'gateway' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['nullable', 'string'],
        ];
    }
}
