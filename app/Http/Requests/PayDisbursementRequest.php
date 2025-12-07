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
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}

