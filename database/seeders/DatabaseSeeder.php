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

        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role with full access',
        ]);
        Role::create([
            'name' => 'Student',
            'slug' => 'student',
            'description' => 'Student role for scholarship applicants',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());
        $adminToken = $admin->createToken('auth-token')->plainTextToken;

        $user = User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()->attach(Role::where('slug', 'student')->first());
        $userToken = $user->createToken('auth-token')->plainTextToken;


        echo('Admin Token: ' . $adminToken);
        echo('User Token: ' . $userToken);

        $this->call([
            ScholarshipSeeder::class,
            ScholarshipApplicationSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
