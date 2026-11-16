<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id')->all();

        foreach (range(1, 50) as $number) {
            $sku = 'PRD-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'name' => fake()->words(3, true),
                    'description' => fake()->sentence(),
                    'base_price' => fake()->randomFloat(2, 100, 50000),
                    'is_active' => fake()->boolean(85),
                ]
            );
        }
    }
}
