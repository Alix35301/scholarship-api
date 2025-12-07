<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisbursementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planned_disbursement_id' => $this->planned_disbursement_id,
            'amount' => $this->amount,
            'date' => $this->date,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'planned_disbursement' => new PlannedDisbursementResource($this->whenLoaded('plannedDisbursement')),
        ];
    }
}

