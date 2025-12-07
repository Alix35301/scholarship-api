<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardReportResource;
use App\Services\AwardReportService;

class AwardReportController extends Controller
{
    protected AwardReportService $reportService;

    public function __construct(AwardReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function show(int $id)
    {
        $report = $this->reportService->generateReport($id);
        
        return new AwardReportResource($report);
    }
}

