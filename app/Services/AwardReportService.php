<?php

namespace App\Services;

use App\Models\Award;
use Illuminate\Support\Facades\DB;

class AwardReportService
{
    public function generateReport(int $awardId): array
    {
        $award = Award::with([
            'student',
            'application.scholarship',
            'plannedDisbursements.costCategory',
            'plannedDisbursements.disbursements',
            'plannedDisbursements.disbursements.receipts',
            'plannedDisbursements.paymentSchedules'
        ])->findOrFail($awardId);

        // Calculate disbursement statistics
        $totalDisbursed = $award->plannedDisbursements->flatMap->disbursements
            ->where('status', 'completed')
            ->sum('amount');

        $pendingDisbursements = $award->plannedDisbursements->flatMap->disbursements
            ->where('status', 'pending')
            ->sum('amount');

        $totalAllocated = $award->plannedDisbursements->sum('allocated_amount');
        $totalRemaining = $award->plannedDisbursements->sum('remaining_amount');

        // Calculate payment schedule statistics
        $scheduledPayments = $award->plannedDisbursements->flatMap->paymentSchedules;
        $completedSchedules = $scheduledPayments->where('status', 'completed')->count();
        $pendingSchedules = $scheduledPayments->where('status', 'pending')->count();
        $cancelledSchedules = $scheduledPayments->where('status', 'cancelled')->count();

        // Calculate receipts statistics
        $allReceipts = $award->plannedDisbursements->flatMap->disbursements->flatMap->receipts;
        $verifiedReceipts = $allReceipts->where('status', 'verified')->count();
        $pendingReceipts = $allReceipts->where('status', 'pending')->count();
        $rejectedReceipts = $allReceipts->where('status', 'rejected')->count();

        // Breakdown by cost category
        $categoryBreakdown = $award->plannedDisbursements->map(function ($pd) {
            $disbursedAmount = $pd->disbursements->where('status', 'completed')->sum('amount');
            $pendingAmount = $pd->disbursements->where('status', 'pending')->sum('amount');
            $receiptsCount = $pd->disbursements->flatMap->receipts->count();
            $verifiedReceiptsCount = $pd->disbursements->flatMap->receipts
                ->where('status', 'verified')->count();

            return [
                'category_id' => $pd->cost_category_id,
                'category_name' => $pd->costCategory->name ?? 'N/A',
                'allocated_amount' => $pd->allocated_amount,
                'remaining_amount' => $pd->remaining_amount,
                'disbursed_amount' => $disbursedAmount,
                'pending_amount' => $pendingAmount,
                'receipts_count' => $receiptsCount,
                'verified_receipts_count' => $verifiedReceiptsCount,
            ];
        });

        // Disbursement timeline
        $disbursementTimeline = $award->plannedDisbursements->flatMap->disbursements
            ->sortBy('date')
            ->map(function ($disbursement) {
                return [
                    'id' => $disbursement->id,
                    'date' => $disbursement->date,
                    'amount' => $disbursement->amount,
                    'status' => $disbursement->status,
                    'category' => $disbursement->plannedDisbursement->costCategory->name ?? 'N/A',
                    'receipts_count' => $disbursement->receipts->count(),
                ];
            })
            ->values();

        return [
            'award' => $award,
            'summary' => [
                'total_amount' => $award->total_amount,
                'total_allocated' => $totalAllocated,
                'total_disbursed' => $totalDisbursed,
                'total_remaining' => $totalRemaining,
                'pending_disbursements' => $pendingDisbursements,
                'status' => $award->status,
            ],
            'payment_schedules' => [
                'total' => $scheduledPayments->count(),
                'completed' => $completedSchedules,
                'pending' => $pendingSchedules,
                'cancelled' => $cancelledSchedules,
            ],
            'receipts_stats' => [
                'total' => $allReceipts->count(),
                'verified' => $verifiedReceipts,
                'pending' => $pendingReceipts,
                'rejected' => $rejectedReceipts,
            ],
            'category_breakdown' => $categoryBreakdown,
            'disbursement_timeline' => $disbursementTimeline,
        ];
    }
}

