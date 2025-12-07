<?php

namespace App\Http\Requests;

use App\Enums\ReceiptStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ReceiptStatus::class)->only([ReceiptStatus::Approved, ReceiptStatus::Rejected])],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string'],
        ];
    }
}
