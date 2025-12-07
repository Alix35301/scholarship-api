<?php

namespace App\Services;

use App\Models\Award;
use App\Models\PlannedDisbursement;
use App\Models\ScholarshipApplication;

class AwardService
{
    public function createAwardFromApplication(ScholarshipApplication $application): Award
    {
        // Check if application already has an award
        if ($application->award) {
            throw new \Exception('This application has already been awarded.');
        }

        // Calculate total amount from category costs
        $totalAmount = 0;
        if ($application->category_costs) {
            foreach ($application->category_costs as $categoryId => $amount) {
                $totalAmount += $amount;
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
            foreach ($application->category_costs as $categoryId => $amount) {
                PlannedDisbursement::create([
                    'award_id' => $award->id,
                    'cost_category_id' => $categoryId,
                    'allocated_amount' => $amount,
                    'remaining_amount' => $amount,
                ]);
            }
        }

        return $award->load(['plannedDisbursements.costCategory', 'student', 'application']);
    }
}

