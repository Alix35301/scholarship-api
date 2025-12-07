<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:cost_categories,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:cost_categories,slug'],
            'description' => ['nullable', 'string'],
        ];
    }
}
