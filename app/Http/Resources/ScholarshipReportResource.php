<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'scholarship' => [
                'id' => $this->resource['scholarship']->id,
                'name' => $this->resource['scholarship']->name,
                'description' => $this->resource['scholarship']->description,
                'amount' => $this->resource['scholarship']->amount,
                'budget' => $this->resource['scholarship']->budget,
                'status' => $this->resource['scholarship']->status,
                'application_deadline' => $this->resource['scholarship']->application_deadline,
                'start_date' => $this->resource['scholarship']->start_date,
                'end_date' => $this->resource['scholarship']->end_date,
            ],
            'summary' => $this->resource['summary'],
            'awards_by_status' => $this->resource['awards_by_status'],
            'receipts_stats' => $this->resource['receipts_stats'],
            'disbursements_by_category' => $this->resource['disbursements_by_category'],
        ];
    }
}

