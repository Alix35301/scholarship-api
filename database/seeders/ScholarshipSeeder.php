<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $scholarships = [
            [
                'name' => 'Academic Excellence Scholarship',
                'description' => 'Awarded to students with outstanding academic performance',
                'amount' => 5000.00,
                'budget' => 50000.00,
                'status' => 'open',
                'application_deadline' => '2024-08-01',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'requirements' => 'Minimum GPA of 3.5, full-time enrollment required',
            ],
            [
                'name' => 'Need-Based Financial Aid',
                'description' => 'Financial assistance for students demonstrating financial need',
                'amount' => 3000.00,
                'budget' => 75000.00,
                'status' => 'open',
                'application_deadline' => '2024-07-15',
                'start_date' => '2024-09-01',
                'end_date' => '2025-05-31',
                'requirements' => 'FAFSA completion required, household income below $50,000',
            ],
            [
                'name' => 'Merit Scholarship',
                'description' => 'Recognition for exceptional talent and achievement',
                'amount' => 7500.00,
                'budget' => 100000.00,
                'status' => 'open',
                'application_deadline' => '2024-08-15',
                'start_date' => '2024-09-01',
                'end_date' => '2025-08-31',
                'requirements' => 'Outstanding achievements in academics, arts, or athletics',
            ],
        ];

        foreach ($scholarships as $scholarship) {
            Scholarship::create($scholarship);
        }
    }
}

