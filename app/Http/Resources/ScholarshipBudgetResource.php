<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipBudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scholarship_id' => $this->scholarship_id,
            'cost_category_id' => $this->cost_category_id,
            'budget' => $this->budget,
            'cost_category' => new CostCategoryResource($this->whenLoaded('costCategory')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

