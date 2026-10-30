<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        'Electronics',
        'Vehicles',
        'Home and Garden',
        'Furniture',
        'Clothing and Accessories',
        'Sports and Outdoors',
        'Baby and Kids',
        'Books, Music and Games',
        'Health and Beauty',
        'Pets',
        'Tools and Hobbies',
        'Other',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $order => $name) {
            Category::updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name, 'sort_order' => $order],
            );
        }
    }
}
