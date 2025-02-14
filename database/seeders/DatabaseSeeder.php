<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'admin',
        //     'email' => 'ad@example.com',
        // ]);

        User::factory()->create([
            'name' => 'Amany',
            'email' => 'amanii.arif@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Adhu Arif',
            'email' => 'adhuarif@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Ainy',
            'email' => 'ainy@gmail.com',
        ]);

        $this->call([
            // EventSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
