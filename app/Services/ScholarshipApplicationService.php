<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Http\Request;

class ScholarshipApplicationService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    public function createApplication(array $validatedData, User $user): ScholarshipApplication
    {
        $application = ScholarshipApplication::create([
            ...$validatedData,
            'student_id' => $user->id,
            'status' => $validatedData['status'] ?? ApplicationStatus::Pending,
            'applied_at' => now(),
        ]);

        $application->refresh();
        $application->load(['scholarship', 'student']);

        $this->activityLogService->log(
            $application,
            'Application created',
            [
                'scholarship_id' => $application->scholarship_id,
                'status' => $application->status?->value ?? 'pending',
            ],
            $user
        );

        return $application;
    }
}

