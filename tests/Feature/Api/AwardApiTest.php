<?php

use App\Models\User;
use App\Models\Award;
use App\Models\Disbursement;

test('student can view their awards', function () {
    $student = User::factory()->create(['role' => 'student']);
    Award::factory()->count(2)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student, 'sanctum')
        ->getJson('/api/my-awards');

    $response->assertStatus(200);
});

test('student can view single award', function () {
    $student = User::factory()->create(['role' => 'student']);
    $award = Award::factory()->create(['user_id' => $student->id]);

    $response = $this->actingAs($student, 'sanctum')
        ->getJson("/api/my-awards/{$award->id}");

    $response->assertStatus(200);
});

test('user can view disbursements for award', function () {
    $user = User::factory()->create();
    $award = Award::factory()->create(['user_id' => $user->id]);
    Disbursement::factory()->count(2)->create(['award_id' => $award->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/awards/{$award->id}/disbursements");

    $response->assertStatus(200);
});

test('user can create disbursement', function () {
    $user = User::factory()->create();
    $award = Award::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/disbursements', [
            'award_id' => $award->id,
            'amount' => 1000,
            'description' => 'Test',
        ]);

    $response->assertStatus(201);
});

test('admin can process disbursement payment', function () {
    $admin = User::factory()->admin()->create();
    $disbursement = Disbursement::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/admin/disbursements/{$disbursement->id}/pay");

    $response->assertStatus(200);
});

