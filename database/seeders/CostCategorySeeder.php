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
                'disbursement_type' => 'semester',
                'disbursement_config' => [
                    'frequency' => 'semester',
                    'periods' => 2,
                    'months' => [9, 1],
                ],
            ],
            [
                'name' => 'Monthly Stipend',
                'category' => 'stipend',
                'disbursement_type' => 'monthly',
                'disbursement_config' => [
                    'frequency' => 'monthly',
                    'duration_months' => 8,
                    'start_month' => 9,
                ],
            ],
            [
                'name' => 'Travel Expenses',
                'category' => 'travel',
                'disbursement_type' => 'reimbursement',
                'disbursement_config' => [
                    'frequency' => 'on_demand',
                    'max_claims' => 2,
                ],
            ],
        ];

        foreach ($costCategories as $costCategory) {
            CostCategory::create($costCategory);
        }
    }
}

