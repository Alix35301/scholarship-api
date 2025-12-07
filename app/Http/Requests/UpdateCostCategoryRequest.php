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
            'slug' => ['nullable', 'string', 'max:255', 'unique:cost_categories,slug,' . $costCategoryId],
            'description' => ['nullable', 'string'],
        ];
    }
}
