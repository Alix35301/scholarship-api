<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'budget' => $this->budget,
            'status' => $this->status,
            'application_deadline' => $this->application_deadline,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'requirements' => $this->requirements,
            'total_approved_amount' => $this->when(isset($this->total_approved_amount), $this->total_approved_amount),
            'total_receipts_amount' => $this->when(isset($this->total_receipts_amount), $this->total_receipts_amount),
            'remaining_budget' => $this->when(isset($this->remaining_budget), $this->remaining_budget),
            'applications' => ScholarshipApplicationResource::collection($this->whenLoaded('applications')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

