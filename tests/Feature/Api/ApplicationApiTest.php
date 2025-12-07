<?php

use App\Models\User;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;

test('student can create application', function () {
    $student = User::factory()->create(['role' => 'student']);
    $scholarship = Scholarship::factory()->create();

    $response = $this->actingAs($student, 'sanctum')
        ->postJson('/api/applications', [
            'scholarship_id' => $scholarship->id,
            'essay' => 'My essay content',
        ]);

    $response->assertStatus(201);
});

test('student can view their applications', function () {
    $student = User::factory()->create(['role' => 'student']);
    ScholarshipApplication::factory()->count(2)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student, 'sanctum')
        ->getJson('/api/my-applications');

    $response->assertStatus(200);
});

test('user can view single application', function () {
    $user = User::factory()->create();
    $application = ScholarshipApplication::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/applications/{$application->id}");

    $response->assertStatus(200);
});

test('admin can view all applications', function () {
    $admin = User::factory()->admin()->create();
    ScholarshipApplication::factory()->count(3)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/applications');

    $response->assertStatus(200);
});

test('admin can review application', function () {
    $admin = User::factory()->admin()->create();
    $application = ScholarshipApplication::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/admin/applications/{$application->id}/review", [
            'status' => 'approved',
            'notes' => 'Approved',
        ]);

    $response->assertStatus(200);
});

test('non-student cannot create application', function () {
    $admin = User::factory()->admin()->create();
    $scholarship = Scholarship::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/applications', [
            'scholarship_id' => $scholarship->id,
        ]);

    $response->assertStatus(403);
});

