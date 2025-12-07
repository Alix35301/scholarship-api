<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScholarshipApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $scholarships = Scholarship::all();
        $students = User::whereHas('roles', function($q) {
            $q->where('slug', 'student');
        })->get();

        if ($scholarships->isEmpty() || $students->isEmpty()) {
            return;
        }

        $applications = [
            [
                'scholarship_id' => $scholarships->first()->id,
                'student_id' => $students->first()->id,
                'status' => ApplicationStatus::Pending,
                'application_essay' => 'I am a dedicated student with a strong academic record and a passion for learning. This scholarship would help me achieve my educational goals.',
                'additional_documents' => ['transcript.pdf', 'recommendation_letter.pdf'],
                'applied_at' => now()->subDays(5),
            ],
            [
                'scholarship_id' => $scholarships->first()->id,
                'student_id' => $students->last()->id ?? $students->first()->id,
                'status' => ApplicationStatus::Approved,
                'application_essay' => 'I have demonstrated excellence in my field of study and am committed to making a positive impact in my community.',
                'additional_documents' => ['transcript.pdf'],
                'reviewed_by' => User::whereHas('roles', function($q) {
                    $q->where('slug', 'admin');
                })->first()?->id,
                'reviewed_at' => now()->subDays(2),
                'applied_at' => now()->subDays(10),
            ],
            [
                'scholarship_id' => $scholarships->skip(1)->first()?->id ?? $scholarships->first()->id,
                'student_id' => $students->first()->id,
                'status' => ApplicationStatus::Rejected,
                'application_essay' => 'I am seeking financial assistance to continue my studies and pursue my career aspirations.',
                'additional_documents' => null,
                'rejection_reason' => 'Application does not meet minimum GPA requirements.',
                'reviewed_by' => User::whereHas('roles', function($q) {
                    $q->where('slug', 'admin');
                })->first()?->id,
                'reviewed_at' => now()->subDay(),
                'applied_at' => now()->subDays(3),
            ],
        ];

        foreach ($applications as $application) {
            ScholarshipApplication::create($application);
        }
    }
}

