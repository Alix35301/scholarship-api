<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cost_category_id' => ['required', 'exists:cost_categories,id'],
            'budget' => ['required', 'numeric', 'min:0'],
        ];
    }
}

