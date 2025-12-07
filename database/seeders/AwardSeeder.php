<?php

namespace Database\Seeders;

use App\Models\Award;
use App\Models\Disbursement;
use App\Models\PlannedDisbursement;
use App\Models\ScholarshipApplication;
use App\Models\CostCategory;
use Illuminate\Database\Seeder;

class AwardSeeder extends Seeder
{
    public function run(): void
    {
        // Find an approved application (or create one if needed)
        $application = ScholarshipApplication::where('status', 'approved')->first();
        
        if (!$application) {
            // If no approved application exists, skip
            return;
        }

        // Get cost categories
        $tuitionCategory = CostCategory::where('category', 'tuition')->first();
        $stipendCategory = CostCategory::where('category', 'stipend')->first();
        $travelCategory = CostCategory::where('category', 'travel')->first();

        if (!$tuitionCategory || !$stipendCategory || !$travelCategory) {
            // If categories don't exist, skip
            return;
        }

        // Create award
        $award = Award::create([
            'student_id' => $application->student_id,
            'application_id' => $application->id,
            'total_amount' => 15000,
            'status' => 'awarded',
        ]);

        // Create planned disbursements (budgets)
        $tuitionBudget = PlannedDisbursement::create([
            'award_id' => $award->id,
            'cost_category_id' => $tuitionCategory->id,
            'allocated_amount' => 10000,
            'remaining_amount' => 5000, // 5000 already disbursed
        ]);

        $stipendBudget = PlannedDisbursement::create([
            'award_id' => $award->id,
            'cost_category_id' => $stipendCategory->id,
            'allocated_amount' => 4000,
            'remaining_amount' => 2000, // 2000 already disbursed
        ]);

        $travelBudget = PlannedDisbursement::create([
            'award_id' => $award->id,
            'cost_category_id' => $travelCategory->id,
            'allocated_amount' => 1000,
            'remaining_amount' => 500, // 500 already disbursed
        ]);

        // Create actual disbursements
        // Tuition disbursements
        Disbursement::create([
            'planned_disbursement_id' => $tuitionBudget->id,
            'amount' => 5000,
            'date' => '2024-09-01',
            'status' => 'completed',
            'notes' => 'Fall tuition',
        ]);

        Disbursement::create([
            'planned_disbursement_id' => $tuitionBudget->id,
            'amount' => 5000,
            'date' => '2025-01-15',
            'status' => 'pending',
            'notes' => 'Spring tuition',
        ]);

        // Stipend disbursements
        Disbursement::create([
            'planned_disbursement_id' => $stipendBudget->id,
            'amount' => 500,
            'date' => '2024-09-01',
            'status' => 'completed',
            'notes' => 'Sept stipend',
        ]);

        Disbursement::create([
            'planned_disbursement_id' => $stipendBudget->id,
            'amount' => 500,
            'date' => '2024-10-01',
            'status' => 'completed',
            'notes' => 'Oct stipend',
        ]);

        // Travel disbursement
        Disbursement::create([
            'planned_disbursement_id' => $travelBudget->id,
            'amount' => 500,
            'date' => '2024-09-15',
            'status' => 'completed',
            'notes' => 'Initial travel',
        ]);
    }
}

