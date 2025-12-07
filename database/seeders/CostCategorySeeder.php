<?php

namespace Database\Seeders;

use App\Models\CostCategory;
use Illuminate\Database\Seeder;

class CostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $costCategories = [
            [
                'name' => 'Tuition Fees',
                'category' => 'tuition',
            ],
            [
                'name' => 'Monthly Stipend',
                'category' => 'stipend',
            ],
            [
                'name' => 'Travel Expenses',
                'category' => 'travel',
            ],
        ];

        foreach ($costCategories as $costCategory) {
            CostCategory::create($costCategory);
        }
    }
}

