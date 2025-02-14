<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Product 1',
                'description' => 'This is product 1 description',
                'price' => 99.99,
                'stock' => 100,
                'owner_id' => 1,
                'event_id' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Product 2',
                'description' => 'This is product 2 description',
                'price' => 149.99,
                'stock' => 50,
                'owner_id' => 2,
                'event_id' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Product 3',
                'description' => 'This is product 3 description',
                'price' => 199.99,
                'stock' => 75,
                'owner_id' => 3,
                'event_id' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
