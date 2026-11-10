<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update_sale_list');
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'seller_id' => 'nullable|exists:employees,id',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'sale_date' => 'nullable|date',
            'sub_total' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'grand_total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variant_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one product is required.',
            'items.*.id.required' => 'Product ID is required for each item.',
            'items.*.id.exists' => 'One or more selected products do not exist.',
            'items.*.qty.min' => 'Quantity must be at least 1.',
            'items.*.price.min' => 'Price cannot be negative.',
            'grand_total.min' => 'Grand total cannot be negative.',
        ];
    }
}
