<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categories to seed.
     */
    public const CATEGORIES = [
        ['name' => 'Clothing and Accessories', 'slug' => 'clothing'],
        ['name' => 'Electronics', 'slug' => 'electronics'],
        ['name' => 'Home and Garden', 'slug' => 'home'],
        ['name' => 'Sports and Outdoors', 'slug' => 'sports'],
        ['name' => 'Automotive', 'slug' => 'automotive'],
        ['name' => 'Baby', 'slug' => 'baby'],
        ['name' => 'Health and Personal Care', 'slug' => 'health'],
        ['name' => 'Beauty', 'slug' => 'beauty'],
        ['name' => 'Other', 'slug' => 'other'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::create($category);
        }
    }
}
