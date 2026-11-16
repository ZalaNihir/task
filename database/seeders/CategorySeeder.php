<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics' => [
                'Smartphones & Accessories',
                'Laptops & Computers',
                'Audio & Headphones',
                'Cameras & Photography',
            ],
            'Fashion & Apparel' => [
                'Men Clothing',
                'Women Clothing',
                'Footwear & Shoes',
                'Watches & Jewelry',
            ],
            'Home & Kitchen' => [
                'Furniture & Decor',
                'Kitchen Appliances',
                'Bedding & Bath',
                'Lighting & Fixtures',
            ],
            'Beauty & Personal Care' => [
                'Skincare',
                'Hair Care',
                'Makeup & Cosmetics',
                'Fragrances & Perfumes',
            ],
            'Sports & Outdoors' => [
                'Fitness & Gym Equipment',
                'Camping & Hiking',
                'Cycling & Biking',
                'Team Sports Gear',
            ],
            'Books & Stationery' => [
                'Fiction & Literature',
                'Business & Finance Books',
                'Academic & Textbooks',
                'Office & Art Supplies',
            ],
            'Toys & Games' => [
                'Board Games & Puzzles',
                'Action Figures & Collectibles',
                'Educational & STEM Toys',
                'Video Games & Consoles',
            ],
            'Automotive' => [
                'Car Electronics & GPS',
                'Interior Accessories',
                'Exterior Care & Detailing',
                'Replacement Parts & Tools',
            ],
            'Health & Wellness' => [
                'Vitamins & Supplements',
                'Medical Supplies & Monitors',
                'Yoga & Meditation',
                'Organic & Health Foods',
            ],
            'Pet Supplies' => [
                'Dog Food & Treats',
                'Cat Litter & Accessories',
                'Aquarium & Fish Supplies',
                'Pet Toys & Grooming',
            ],
        ];

        foreach ($categories as $parentName => $subcategories) {
            $parentSlug = Str::slug($parentName);

            $parent = Category::updateOrCreate(
                ['slug' => $parentSlug],
                [
                    'name'      => $parentName,
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );

            foreach ($subcategories as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name'      => $childName,
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
