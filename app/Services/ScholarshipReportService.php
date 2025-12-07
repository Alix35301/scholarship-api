<?php

namespace App\Services;

use App\Models\Scholarship;
use Illuminate\Support\Facades\DB;

class ScholarshipReportService
{
    public function generateReport(int $scholarshipId): array
    {
        $scholarship = Scholarship::with([
            'applications',
            'applications.student',
            'applications.receipts',
            'budgets'
        ])->findOrFail($scholarshipId);

        $totalApplications = $scholarship->applications()->count();
        $approvedApplications = $scholarship->applications()
            ->where('status', 'approved')
            ->count();
        $rejectedApplications = $scholarship->applications()
            ->where('status', 'rejected')
            ->count();
        $pendingApplications = $scholarship->applications()
            ->where('status', 'pending')
            ->count();

        // Get total awards amount
        $totalAwardedAmount = DB::table('awards')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->sum('awards.total_amount');

        // Get disbursements data
        $totalDisbursed = DB::table('disbursements')
            ->join('planned_disbursements', 'disbursements.planned_disbursement_id', '=', 'planned_disbursements.id')
            ->join('awards', 'planned_disbursements.award_id', '=', 'awards.id')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->where('disbursements.status', 'completed')
            ->sum('disbursements.amount');

        $pendingDisbursements = DB::table('disbursements')
            ->join('planned_disbursements', 'disbursements.planned_disbursement_id', '=', 'planned_disbursements.id')
            ->join('awards', 'planned_disbursements.award_id', '=', 'awards.id')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->where('disbursements.status', 'pending')
            ->sum('disbursements.amount');

        // Get receipts verification stats
        $receiptsStats = DB::table('disbursement_receipts')
            ->join('disbursements', 'disbursement_receipts.disbursement_id', '=', 'disbursements.id')
            ->join('planned_disbursements', 'disbursements.planned_disbursement_id', '=', 'planned_disbursements.id')
            ->join('awards', 'planned_disbursements.award_id', '=', 'awards.id')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->selectRaw('
                COUNT(*) as total_receipts,
                SUM(CASE WHEN disbursement_receipts.status = "verified" THEN 1 ELSE 0 END) as verified_receipts,
                SUM(CASE WHEN disbursement_receipts.status = "pending" THEN 1 ELSE 0 END) as pending_receipts,
                SUM(CASE WHEN disbursement_receipts.status = "rejected" THEN 1 ELSE 0 END) as rejected_receipts
            ')
            ->first();

        // Get awards by status
        $awardsByStatus = DB::table('awards')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->select('awards.status', DB::raw('COUNT(*) as count'), DB::raw('SUM(awards.total_amount) as total'))
            ->groupBy('awards.status')
            ->get()
            ->keyBy('status');

        // Get disbursements by cost category
        $disbursementsByCategory = DB::table('planned_disbursements')
            ->join('cost_categories', 'planned_disbursements.cost_category_id', '=', 'cost_categories.id')
            ->join('awards', 'planned_disbursements.award_id', '=', 'awards.id')
            ->join('scholarship_applications', 'awards.application_id', '=', 'scholarship_applications.id')
            ->where('scholarship_applications.scholarship_id', $scholarshipId)
            ->select(
                'cost_categories.name as category_name',
                DB::raw('SUM(planned_disbursements.allocated_amount) as allocated'),
                DB::raw('SUM(planned_disbursements.remaining_amount) as remaining')
            )
            ->groupBy('cost_categories.id', 'cost_categories.name')
            ->get();

        return [
            'scholarship' => $scholarship,
            'summary' => [
                'total_budget' => $scholarship->budget,
                'total_applications' => $totalApplications,
                'approved_applications' => $approvedApplications,
                'rejected_applications' => $rejectedApplications,
                'pending_applications' => $pendingApplications,
                'total_awarded_amount' => $totalAwardedAmount ?? 0,
                'total_disbursed' => $totalDisbursed ?? 0,
                'pending_disbursements' => $pendingDisbursements ?? 0,
                'remaining_budget' => $scholarship->budget - ($totalAwardedAmount ?? 0),
            ],
            'awards_by_status' => $awardsByStatus,
            'receipts_stats' => [
                'total' => $receiptsStats->total_receipts ?? 0,
                'verified' => $receiptsStats->verified_receipts ?? 0,
                'pending' => $receiptsStats->pending_receipts ?? 0,
                'rejected' => $receiptsStats->rejected_receipts ?? 0,
            ],
            'disbursements_by_category' => $disbursementsByCategory,
        ];
    }
}

