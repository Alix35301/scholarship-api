<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scholarship_id' => ['required', 'exists:scholarships,id'],
            'application_essay' => ['nullable', 'string'],
            'additional_documents' => ['nullable', 'array'],
        ];
    }
}

