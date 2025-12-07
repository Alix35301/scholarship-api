<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => 'required|string|max:255',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}

