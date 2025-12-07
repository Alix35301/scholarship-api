<?php

use App\Models\User;
use App\Models\Scholarship;
use App\Models\Budget;
use App\Models\CostCategory;

test('user can view scholarship budgets', function () {
    $user = User::factory()->create();
    $scholarship = Scholarship::factory()->create();
    Budget::factory()->count(2)->create(['scholarship_id' => $scholarship->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/scholarships/{$scholarship->id}/budgets");

    $response->assertStatus(200);
});

test('admin can create budget', function () {
    $admin = User::factory()->admin()->create();
    $scholarship = Scholarship::factory()->create();
    $category = CostCategory::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/scholarships/{$scholarship->id}/budgets", [
            'cost_category_id' => $category->id,
            'allocated_amount' => 5000,
        ]);

    $response->assertStatus(201);
});

test('student cannot create budget', function () {
    $student = User::factory()->create(['role' => 'student']);
    $scholarship = Scholarship::factory()->create();

    $response = $this->actingAs($student, 'sanctum')
        ->postJson("/api/scholarships/{$scholarship->id}/budgets", [
            'allocated_amount' => 5000,
        ]);

    $response->assertStatus(403);
});

test('user can view cost categories', function () {
    $user = User::factory()->create();
    CostCategory::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/cost-categories');

    $response->assertStatus(200);
});

