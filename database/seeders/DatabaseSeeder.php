<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\ScholarshipSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $adminToken = $admin->createToken('auth-token')->plainTextToken;

        $user = User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
        ]);
        $userToken = $user->createToken('auth-token')->plainTextToken;


        echo('Admin Token: ' . $adminToken);
        echo('User Token: ' . $userToken);

        $this->call([
            ScholarshipSeeder::class,
        ]);
    }
}
