<?php

namespace App\Services;

use App\Models\Award;
use App\Models\PlannedDisbursement;
use App\Models\ScholarshipApplication;

class AwardService
{
    public function createAwardFromApplication(ScholarshipApplication $application): Award
    {
        // Load the application with cost categories if not already loaded
        $application->load('costCategories');

        // Calculate total amount from category costs
        $totalAmount = 0;
        if ($application->category_costs) {
            foreach ($application->category_costs as $cost) {
                $totalAmount += $cost['amount'] ?? 0;
            }
        }

        // Create the award
        $award = Award::create([
            'student_id' => $application->student_id,
            'application_id' => $application->id,
            'total_amount' => $totalAmount,
            'status' => 'awarded',
        ]);

        // Create planned disbursements from category costs
        if ($application->category_costs) {
            foreach ($application->category_costs as $cost) {
                PlannedDisbursement::create([
                    'award_id' => $award->id,
                    'cost_category_id' => $cost['cost_category_id'],
                    'allocated_amount' => $cost['amount'],
                    'remaining_amount' => $cost['amount'],
                ]);
            }
        }

        return $award->load(['plannedDisbursements.costCategory', 'student', 'application']);
    }
}

