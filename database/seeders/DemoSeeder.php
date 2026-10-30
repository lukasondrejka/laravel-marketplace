<?php

namespace Database\Seeders;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@example.com';

    public const DEMO_PASSWORD = 'password';

    /** @var array<string, array<int, array{0: string, 1: int, 2: int}>> category slug => [title, min price, max price] */
    private const CATALOG = [
        'electronics' => [['iPhone 13 128GB, excellent condition', 380, 520], ['Sony WH-1000XM4 headphones', 120, 190], ['Gaming laptop RTX 3060', 650, 950], ['Samsung 55" 4K TV', 280, 420], ['Mechanical keyboard, brown switches', 35, 80], ['Nintendo Switch OLED with 2 games', 210, 290]],
        'vehicles' => [['City bike, 28", 7 speeds', 90, 180], ['Electric scooter Xiaomi Pro 2', 230, 380], ['Child car seat 9-36 kg', 30, 70], ['Roof rack for hatchback', 40, 90], ['Winter tyres 205/55 R16, set of 4', 120, 220]],
        'home-and-garden' => [['Cordless lawn mower', 110, 190], ['Dyson V8 vacuum cleaner', 140, 230], ['Set of 6 garden chairs', 60, 140], ['Espresso machine De Longhi', 90, 160], ['Air fryer 5L', 35, 70]],
        'furniture' => [['Solid oak dining table', 220, 420], ['IKEA Billy bookcase, white', 25, 55], ['L-shaped corner sofa, grey', 280, 520], ['Ergonomic office chair', 70, 160], ['Wardrobe with sliding doors', 120, 240]],
        'clothing-and-accessories' => [['Winter jacket, size M', 40, 90], ['Leather backpack', 45, 95], ['Nike Air Max, size 43', 50, 110], ['Wool coat, women size S', 35, 80], ['Wristwatch, automatic', 150, 360]],
        'sports-and-outdoors' => [['Trekking tent for 3 persons', 80, 170], ['Dumbbell set 2x10 kg', 30, 60], ['Ski set 165 cm with boots', 110, 230], ['Yoga mat and blocks', 10, 25], ['Road bike helmet', 20, 45]],
        'baby-and-kids' => [['Baby stroller 3-in-1', 150, 300], ['Wooden toy kitchen', 30, 65], ['LEGO City police station', 25, 55], ['Baby cot with mattress', 60, 120]],
        'books-music-and-games' => [['Acoustic guitar with case', 80, 170], ['Harry Potter complete book set', 35, 70], ['Board game collection (10 games)', 40, 90], ['Vinyl records, rock classics', 5, 25]],
        'health-and-beauty' => [['Electric toothbrush Oral-B', 25, 55], ['Hair dryer Dyson Supersonic', 150, 240], ['Massage chair pad', 40, 90]],
        'pets' => [['Large dog crate', 35, 75], ['Cat tree 150 cm', 40, 85], ['Aquarium 120 L with stand', 90, 190]],
        'tools-and-hobbies' => [['Bosch cordless drill 18V', 60, 120], ['Complete socket set, 94 pieces', 25, 55], ['Beginner watercolour set', 10, 30], ['Sewing machine Singer', 70, 130]],
        'other' => [['Moving boxes, 20 pieces', 0, 0], ['Suitcase set of 3', 40, 85]],
    ];

    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $demo = User::updateOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo User',
                'password' => self::DEMO_PASSWORD,
                'city' => 'Bratislava',
                'phone' => '+421 900 000 000',
                'bio' => 'Selling things I no longer use. Pickup in Bratislava, happy to answer questions.',
                'email_verified_at' => now(),
            ],
        );

        $sellers = User::factory()->count(8)->create()->push($demo);
        $categories = Category::all()->keyBy('slug');

        foreach (self::CATALOG as $slug => $items) {
            foreach ($items as [$title, $min, $max]) {
                $seller = $sellers->random();
                // The demo account owns a healthy share of the data so the dashboard is not empty.
                if (fake()->boolean(25)) {
                    $seller = $demo;
                }

                $wanted = fake()->boolean(8);

                $listing = Listing::factory()->create([
                    'type' => $wanted ? ListingType::Wanted : ListingType::Offer,
                    'user_id' => $seller->id,
                    'category_id' => $categories[$slug]->id,
                    'title' => $wanted ? 'Wanted: '.$title : $title,
                    'description' => $this->description($title),
                    'price' => $max === 0 ? 0 : fake()->numberBetween($min, $max),
                    'condition' => fake()->randomElement(ListingCondition::cases()),
                    'status' => fake()->randomElement([...array_fill(0, 12, ListingStatus::Active), ListingStatus::Sold, ListingStatus::Draft]),
                    'city' => $seller->city ?? 'Bratislava',
                ]);

                if ($listing->status === ListingStatus::Draft) {
                    $listing->update(['published_at' => null]);
                }

                $this->attachImages($listing);
            }
        }
    }

    private function description(string $title): string
    {
        return "{$title}.\n\n".fake()->paragraph(3)."\n\nPersonal pickup preferred. More photos and details on request. See also https://example.com/guide for the manual.";
    }

    private function attachImages(Listing $listing): void
    {
        foreach (range(1, fake()->numberBetween(1, 4)) as $i) {
            $path = PlaceholderImage::make($listing->title.$i);
            $listing->addMedia($path)->usingFileName("{$listing->slug}-{$i}.jpg")->toMediaCollection(Listing::IMAGES);
        }
    }
}
