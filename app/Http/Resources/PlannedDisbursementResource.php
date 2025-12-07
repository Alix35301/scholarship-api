<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlannedDisbursementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'award_id' => $this->award_id,
            'cost_category_id' => $this->cost_category_id,
            'allocated_amount' => $this->allocated_amount,
            'remaining_amount' => $this->remaining_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cost_category' => new CostCategoryResource($this->whenLoaded('costCategory')),
            'disbursements' => DisbursementResource::collection($this->whenLoaded('disbursements')),
            'payment_schedules' => PaymentScheduleResource::collection($this->whenLoaded('paymentSchedules')),
        ];
    }
}

