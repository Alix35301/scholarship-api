<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            // 'buyer_id' => ['required', 'exists:users,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product',
            'product_id.exists' => 'The selected product is invalid',
            // 'buyer_id.required' => 'Please select a buyer',
            // 'buyer_id.exists' => 'The selected buyer is invalid',
            'quantity.required' => 'Please enter the quantity',
            'quantity.min' => 'Quantity must be at least 1',
            'unit_price.required' => 'Please enter the unit price',
            'unit_price.min' => 'Unit price must be greater than or equal to 0',
            'status.required' => 'Please select a status',
            'status.in' => 'The selected status is invalid',
        ];
    }
}
