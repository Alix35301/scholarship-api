<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\ScholarshipSeeder;
use Database\Seeders\ScholarshipApplicationSeeder;
use App\Models\Role;
use Database\Seeders\ActivityLogSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Admin role with full access',
            ]
        );
        Role::firstOrCreate(
            ['slug' => 'student'],
            [
                'name' => 'Student',
                'description' => 'Student role for scholarship applicants',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->roles()->attach(Role::where('slug', 'admin')->first());
        }
        $admin->tokens()->delete();
        $adminToken = $admin->createToken('auth-token')->plainTextToken;

        $user = User::firstOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$user->hasRole('student')) {
            $user->roles()->attach(Role::where('slug', 'student')->first());
        }
        $user->tokens()->delete();
        $userToken = $user->createToken('auth-token')->plainTextToken;

        echo('Admin Token: ' . $adminToken . PHP_EOL);
        echo('User Token: ' . $userToken . PHP_EOL);

        $this->call([
            ScholarshipSeeder::class,
            ScholarshipApplicationSeeder::class,
            CostCategorySeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
