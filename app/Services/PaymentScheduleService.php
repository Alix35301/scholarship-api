<?php

namespace App\Services;

use App\Models\CostCategory;
use App\Models\PaymentSchedule;
use App\Models\PlannedDisbursement;
use Carbon\Carbon;

class PaymentScheduleService
{
    public function generateSchedulesForPlannedDisbursement(PlannedDisbursement $plannedDisbursement): array
    {
        $costCategory = $plannedDisbursement->costCategory;
        
        if (!$costCategory->disbursement_type || !$costCategory->disbursement_config) {
            return [];
        }

        $schedules = [];
        $config = $costCategory->disbursement_config;

        switch ($costCategory->disbursement_type) {
            case 'semester':
                $schedules = $this->generateSemesterSchedules($plannedDisbursement, $config);
                break;
            case 'monthly':
                $schedules = $this->generateMonthlySchedules($plannedDisbursement, $config);
                break;
            case 'reimbursement':
                $schedules = $this->generateReimbursementSchedules($plannedDisbursement, $config);
                break;
        }

        return $schedules;
    }

    protected function generateSemesterSchedules(PlannedDisbursement $plannedDisbursement, array $config): array
    {
        $schedules = [];
        $periods = $config['periods'] ?? 2;
        $months = $config['months'] ?? [9, 1];
        $amountPerPeriod = $plannedDisbursement->allocated_amount / $periods;
        
        $year = now()->year;
        
        foreach ($months as $index => $month) {
            if ($index > 0 && $month < $months[$index - 1]) {
                $year++;
            }
            
            $date = Carbon::create($year, $month, 1);
            
            $schedule = PaymentSchedule::create([
                'planned_disbursement_id' => $plannedDisbursement->id,
                'amount' => $amountPerPeriod,
                'date' => $date,
                'status' => 'pending',
            ]);
            
            $schedules[] = $schedule;
        }

        return $schedules;
    }

    protected function generateMonthlySchedules(PlannedDisbursement $plannedDisbursement, array $config): array
    {
        $schedules = [];
        $durationMonths = $config['duration_months'] ?? 12;
        $startMonth = $config['start_month'] ?? now()->month;
        $amountPerMonth = $plannedDisbursement->allocated_amount / $durationMonths;
        
        $currentDate = Carbon::create(now()->year, $startMonth, 1);
        
        for ($i = 0; $i < $durationMonths; $i++) {
            $schedule = PaymentSchedule::create([
                'planned_disbursement_id' => $plannedDisbursement->id,
                'amount' => $amountPerMonth,
                'date' => $currentDate->copy(),
                'status' => 'pending',
            ]);
            
            $schedules[] = $schedule;
            $currentDate->addMonth();
        }

        return $schedules;
    }

    protected function generateReimbursementSchedules(PlannedDisbursement $plannedDisbursement, array $config): array
    {
        $schedules = [];
        $maxClaims = $config['max_claims'] ?? 1;
        $amountPerClaim = $plannedDisbursement->allocated_amount / $maxClaims;
        
        for ($i = 0; $i < $maxClaims; $i++) {
            $schedule = PaymentSchedule::create([
                'planned_disbursement_id' => $plannedDisbursement->id,
                'amount' => $amountPerClaim,
                'date' => now()->addWeeks($i * 2),
                'status' => 'pending',
                'notes' => "Reimbursement claim " . ($i + 1),
            ]);
            
            $schedules[] = $schedule;
        }

        return $schedules;
    }

    public function createManualSchedule(PlannedDisbursement $plannedDisbursement, array $data): PaymentSchedule
    {
        return PaymentSchedule::create([
            'planned_disbursement_id' => $plannedDisbursement->id,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ]);
    }
}

