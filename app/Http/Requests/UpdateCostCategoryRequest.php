<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $costCategoryId = $this->route('costCategory')?->id ?? $this->route('costCategory');
        
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:cost_categories,name,' . $costCategoryId],
            'category' => ['sometimes', 'required', 'in:tuition,stipend,travel'],
            'disbursement_type' => ['nullable', 'in:semester,monthly,reimbursement'],
            'disbursement_config' => ['nullable', 'array'],
            'disbursement_config.frequency' => ['nullable', 'string'],
            'disbursement_config.periods' => ['nullable', 'integer', 'min:1'],
            'disbursement_config.months' => ['nullable', 'array'],
            'disbursement_config.months.*' => ['integer', 'between:1,12'],
            'disbursement_config.duration_months' => ['nullable', 'integer', 'min:1'],
            'disbursement_config.start_month' => ['nullable', 'integer', 'between:1,12'],
            'disbursement_config.max_claims' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
