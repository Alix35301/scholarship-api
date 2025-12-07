<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ApplicationStatus::class)->only([ApplicationStatus::Approved, ApplicationStatus::Rejected])],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string'],
        ];
    }
}

