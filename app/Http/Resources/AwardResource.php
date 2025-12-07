<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'application_id' => $this->application_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => new UserResource($this->whenLoaded('student')),
            'application' => new ScholarshipApplicationResource($this->whenLoaded('application')),
            'planned_disbursements' => PlannedDisbursementResource::collection($this->whenLoaded('plannedDisbursements')),
        ];
    }
}

