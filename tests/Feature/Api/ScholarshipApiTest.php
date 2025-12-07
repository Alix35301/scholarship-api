<?php

use App\Models\User;
use App\Models\Scholarship;

test('user can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['user', 'token']);
});

test('user can login', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['user', 'token']);
});

test('authenticated user can view scholarships', function () {
    $user = User::factory()->create();
    Scholarship::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/scholarships');

    $response->assertStatus(200);
    $response->assertJsonStructure(['data']);
});

test('authenticated user can view single scholarship', function () {
    $user = User::factory()->create();
    $scholarship = Scholarship::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/scholarships/{$scholarship->id}");

    $response->assertStatus(200);
});

test('admin can create scholarship', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/scholarships', [
            'name' => 'Test Scholarship',
            'description' => 'Test Description',
            'amount' => 5000,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
        ]);

    $response->assertStatus(201);
});

test('student cannot create scholarship', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student, 'sanctum')
        ->postJson('/api/scholarships', [
            'name' => 'Test Scholarship',
        ]);

    $response->assertStatus(403);
});

test('guest cannot access scholarships', function () {
    $response = $this->getJson('/api/scholarships');
    $response->assertStatus(401);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/logout');

    $response->assertStatus(200);
});
