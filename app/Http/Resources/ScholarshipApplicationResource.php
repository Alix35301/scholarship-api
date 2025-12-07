<?php

namespace App\Http\Resources;

use App\Models\CostCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categoryCosts = $this->category_costs ?? [];
        $formattedCategoryCosts = [];

        if (!empty($categoryCosts)) {
            $categoryIds = array_keys($categoryCosts);
            $categories = CostCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

            foreach ($categoryCosts as $categoryId => $cost) {
                $category = $categories->get((int) $categoryId);
                $formattedCategoryCosts[] = [
                    'category_id' => (int) $categoryId,
                    'category_name' => $category?->name ?? 'Unknown',
                    'cost' => (float) $cost,
                ];
            }
        }

        return [
            'id' => $this->id,
            'scholarship_id' => $this->scholarship_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'application_essay' => $this->application_essay,
            'category_costs' => $formattedCategoryCosts,
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'rejection_reason' => $this->rejection_reason,
            'applied_at' => $this->applied_at,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

