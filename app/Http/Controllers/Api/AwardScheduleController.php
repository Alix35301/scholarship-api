<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentScheduleRequest;
use App\Http\Resources\PaymentScheduleResource;
use App\Models\Award;
use App\Models\PlannedDisbursement;
use App\Services\PaymentScheduleService;

class AwardScheduleController extends Controller
{
    protected PaymentScheduleService $scheduleService;

    public function __construct(PaymentScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function store(CreatePaymentScheduleRequest $request, int $awardId)
    {
        $award = Award::findOrFail($awardId);
        
        $plannedDisbursement = PlannedDisbursement::where('award_id', $award->id)
            ->where('id', $request->planned_disbursement_id)
            ->firstOrFail();

        $schedule = $this->scheduleService->createManualSchedule(
            $plannedDisbursement,
            $request->validated()
        );

        return new PaymentScheduleResource($schedule->load('plannedDisbursement.costCategory'));
    }
}

