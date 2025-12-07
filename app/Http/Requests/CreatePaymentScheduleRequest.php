<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'planned_disbursement_id' => 'required|exists:planned_disbursements,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'status' => 'nullable|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ];
    }
}

