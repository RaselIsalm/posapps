<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update_sale_list');
    }

    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|exists:accounts,id',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.exists' => 'The selected sale does not exist.',
            'amount.min' => 'Payment amount must be at least 0.01.',
            'payment_method.exists' => 'The selected payment method/account does not exist.',
        ];
    }
}
