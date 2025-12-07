<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipReportResource;
use App\Services\ScholarshipReportService;

class ScholarshipReportController extends Controller
{
    protected ScholarshipReportService $reportService;

    public function __construct(ScholarshipReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function show(int $id)
    {
        $report = $this->reportService->generateReport($id);
        
        return new ScholarshipReportResource($report);
    }
}

