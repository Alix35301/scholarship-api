<?php

namespace App\Services;

use App\Models\Award;
use App\Models\PlannedDisbursement;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipBudget;
use Illuminate\Support\Facades\DB;

class AwardService
{
    protected PaymentScheduleService $scheduleService;

    public function __construct(PaymentScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function createAwardFromApplication(ScholarshipApplication $application): Award
    {
        // Check if application already has an award
        if ($application->award) {
            throw new \Exception('This application has already been awarded.');
        }

        // Validate no over-commitment: Check cumulative committed amounts per category
        if ($application->category_costs) {
            $scholarshipId = $application->scholarship_id;
            
            foreach ($application->category_costs as $categoryId => $amount) {
                // Get budget for this category
                $budget = ScholarshipBudget::where('scholarship_id', $scholarshipId)
                    ->where('cost_category_id', $categoryId)
                    ->first();
                
                if ($budget) {
                    // Calculate total already committed for this category in this scholarship
                    $totalCommitted = PlannedDisbursement::whereHas('award.application', function ($query) use ($scholarshipId) {
                        $query->where('scholarship_id', $scholarshipId);
                    })
                    ->where('cost_category_id', $categoryId)
                    ->sum('allocated_amount');
                    
                    // Check if new amount would exceed budget
                    if ($totalCommitted + $amount > $budget->budget) {
                        $categoryName = $budget->costCategory?->name ?? "Category {$categoryId}";
                        throw new \Exception(
                            "Cannot commit {$categoryName} amount: " . number_format($amount, 2) . 
                            ". Total committed would be " . number_format($totalCommitted + $amount, 2) . 
                            ", exceeding budget limit of " . number_format($budget->budget, 2) . "."
                        );
                    }
                }
            }
        }

        // Calculate total amount from category costs
        $totalAmount = 0;
        if ($application->category_costs) {
            foreach ($application->category_costs as $categoryId => $amount) {
                $totalAmount += $amount;
            }
        }

        return DB::transaction(function () use ($application, $totalAmount) {
            // Create the award
            $award = Award::create([
                'student_id' => $application->student_id,
                'application_id' => $application->id,
                'total_amount' => $totalAmount,
                'status' => 'awarded',
            ]);

            // Create planned disbursements from category costs
            if ($application->category_costs) {
                foreach ($application->category_costs as $categoryId => $amount) {
                    $plannedDisbursement = PlannedDisbursement::create([
                        'award_id' => $award->id,
                        'cost_category_id' => $categoryId,
                        'allocated_amount' => $amount,
                        'remaining_amount' => $amount,
                    ]);

                    // Auto-generate payment schedules based on cost category disbursement rules
                    $this->scheduleService->generateSchedulesForPlannedDisbursement($plannedDisbursement);
                }
            }

            return $award->load(['plannedDisbursements.costCategory', 'student', 'application']);
        });
    }
}

