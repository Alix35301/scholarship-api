<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\ScholarshipApplication;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $application = ScholarshipApplication::first();

        if (!$application) {
            $this->command->warn('No scholarship application found. Please seed applications first.');
            return;
        }

        $student = $application->student;
        $admin = User::whereHas('roles', function($q) {
            $q->where('slug', 'admin');
        })->first();

        if (!$student || !$admin) {
            $this->command->warn('Student or admin user not found.');
            return;
        }

        $activityLogService = new ActivityLogService();

        // Log application creation
        $activityLogService->log(
            $application,
            'Application created',
            [
                'scholarship_id' => $application->scholarship_id,
                'status' => ApplicationStatus::Pending->value,
            ],
            $student
        );

        // Simulate status change to approved (if not already approved)
        if ($application->status !== ApplicationStatus::Approved) {
            $activityLogService->log(
                $application,
                'Application status updated to approved',
                [
                    'old_status' => ApplicationStatus::Pending->value,
                    'new_status' => ApplicationStatus::Approved->value,
                    'reviewed_by' => $admin->id,
                ],
                $admin
            );
        }

        // Log document upload
        $activityLogService->log(
            $application,
            'Document uploaded',
            [
                'document_type' => 'transcript',
                'file_name' => 'transcript.pdf',
            ],
            $student
        );

        $this->command->info("Activity logs seeded for application ID: {$application->id}");
    }
}

