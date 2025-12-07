<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'amount' => $this->amount,
            'receipt_file' => $this->receipt_file,
            'description' => $this->description,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'verified_at' => $this->verified_at,
            'application' => new ScholarshipApplicationResource($this->whenLoaded('application')),
            'verifier' => new UserResource($this->whenLoaded('verifier')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

