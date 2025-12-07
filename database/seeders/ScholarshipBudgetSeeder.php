<?php

namespace Database\Seeders;

use App\Models\CostCategory;
use App\Models\Scholarship;
use App\Models\ScholarshipBudget;
use Illuminate\Database\Seeder;

class ScholarshipBudgetSeeder extends Seeder
{
    public function run(): void
    {
        $scholarships = Scholarship::all();
        $costCategories = CostCategory::all();

        if ($scholarships->isEmpty() || $costCategories->isEmpty()) {
            return;
        }

        foreach ($scholarships as $scholarship) {
            foreach ($costCategories as $costCategory) {
                ScholarshipBudget::create([
                    'scholarship_id' => $scholarship->id,
                    'cost_category_id' => $costCategory->id,
                    'budget' => match($costCategory->category) {
                        'tuition' => $scholarship->budget * 0.5,
                        'stipend' => $scholarship->budget * 0.3,
                        'travel' => $scholarship->budget * 0.2,
                        default => $scholarship->budget * 0.33,
                    },
                ]);
            }
        }
    }
}

