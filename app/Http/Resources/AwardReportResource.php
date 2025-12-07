<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'award' => [
                'id' => $this->resource['award']->id,
                'student_id' => $this->resource['award']->student_id,
                'application_id' => $this->resource['award']->application_id,
                'total_amount' => $this->resource['award']->total_amount,
                'status' => $this->resource['award']->status,
                'created_at' => $this->resource['award']->created_at,
                'student' => new UserResource($this->resource['award']->student),
                'scholarship' => [
                    'id' => $this->resource['award']->application->scholarship->id,
                    'name' => $this->resource['award']->application->scholarship->name,
                ],
            ],
            'summary' => $this->resource['summary'],
            'payment_schedules' => $this->resource['payment_schedules'],
            'receipts_stats' => $this->resource['receipts_stats'],
            'category_breakdown' => $this->resource['category_breakdown'],
            'disbursement_timeline' => $this->resource['disbursement_timeline'],
        ];
    }
}

