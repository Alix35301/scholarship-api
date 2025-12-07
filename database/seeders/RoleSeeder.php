<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Administrator role with full access',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'student'],
            [
                'name' => 'Student',
                'description' => 'Student role for scholarship applicants',
            ]
        );
    }
}

