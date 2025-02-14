<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change to true to allow all users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'event_id' => ['required', 'exists:events,id'],
            'owner_id' => ['required', 'exists:users,id'],
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
            'name.required' => 'The product name is required',
            'price.required' => 'The product price is required',
            'price.min' => 'The price must be greater than or equal to 0',
            'stock.min' => 'The stock must be greater than or equal to 0',
            'status.in' => 'The status must be either active or inactive',
            'event_id.required' => 'The event is required',
            'event_id.exists' => 'The selected event is invalid',
            'owner_id.required' => 'The seller is required',
            'owner_id.exists' => 'The selected seller is invalid',
        ];
    }
}
