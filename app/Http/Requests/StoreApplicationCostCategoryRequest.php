<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationCostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cost_category_id' => ['required', 'exists:cost_categories,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
