<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Summer Music Festival 2024',
                'description' => 'Annual summer music festival featuring local and international artists',
                'start_date' => '2024-07-15',
                'end_date' => '2024-07-17',
                'status' => 'upcoming',
                'location' => 'Central Park',
                'total_revenue' => 0,
                'total_expense' => 0,
            ],
            [
                'name' => 'Tech Conference 2024',
                'description' => 'Technology and innovation conference with industry leaders',
                'start_date' => '2024-09-20',
                'end_date' => '2024-09-22',
                'status' => 'upcoming',
                'location' => 'Convention Center',
                'total_revenue' => 0,
                'total_expense' => 0,
            ],
            [
                'name' => 'Food & Wine Expo',
                'description' => 'Showcasing local cuisine and wine from around the world',
                'start_date' => '2024-05-10',
                'end_date' => '2024-05-12',
                'status' => 'upcoming',
                'location' => 'Grand Hotel',
                'total_revenue' => 0,
                'total_expense' => 0,
            ],
            [
                'name' => 'Wedding Expo 2024',
                'description' => 'Everything you need for your perfect wedding day',
                'start_date' => '2024-06-01',
                'end_date' => '2024-06-02',
                'status' => 'upcoming',
                'location' => 'Luxury Resort',
                'total_revenue' => 0,
                'total_expense' => 0,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
