<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scholarship_id' => $this->scholarship_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'application_essay' => $this->application_essay,
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'rejection_reason' => $this->rejection_reason,
            'applied_at' => $this->applied_at,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

