<?php

namespace App\Http\Requests;

use App\Models\ScholarshipBudget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'category_costs' => ['required', 'array'],
            'category_costs.*' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $scholarshipId = $this->input('scholarship_id');
            $categoryCosts = $this->input('category_costs', []);

            if (!$scholarshipId || empty($categoryCosts)) {
                return;
            }

            try {
                $budgets = ScholarshipBudget::where('scholarship_id', $scholarshipId)
                    ->with('costCategory')
                    ->get()
                    ->keyBy('cost_category_id');

                if ($budgets->isEmpty()) {
                    $validator->errors()->add(
                        'scholarship_id',
                        'No budget categories found for this scholarship.'
                    );
                    return;
                }

                foreach ($categoryCosts as $categoryId => $cost) {
                    // Validate category ID is integer (can be string or int)
                    if (!is_numeric($categoryId) || (is_string($categoryId) && !ctype_digit($categoryId))) {
                        $validator->errors()->add(
                            "category_costs.{$categoryId}",
                            "Invalid cost category ID."
                        );
                        continue;
                    }

                    $categoryId = (int) $categoryId;

                    if (!$budgets->has($categoryId)) {
                        $validator->errors()->add(
                            "category_costs.{$categoryId}",
                            "Invalid cost category for this scholarship."
                        );
                        continue;
                    }

                    $budget = $budgets->get($categoryId);
                    if ($cost > $budget->budget) {
                        $categoryName = $budget->costCategory?->name ?? "Category {$categoryId}";
                        $validator->errors()->add(
                            "category_costs.{$categoryId}",
                            "The cost for {$categoryName} exceeds the budget limit of " . number_format($budget->budget, 2) . "."
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Scholarship application validation error: ' . $e->getMessage());
                $validator->errors()->add(
                    'category_costs',
                    'Error validating category costs. Please try again.'
                );
            }
        });
    }
}

