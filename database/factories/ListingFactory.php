<?php

namespace Database\Factories;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(fake()->numberBetween(2, 4), true);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'type' => fake()->randomElement([...array_fill(0, 9, ListingType::Offer), ListingType::Wanted]),
            'title' => ucfirst($title),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 1, 1500),
            'currency' => 'EUR',
            'condition' => fake()->randomElement(ListingCondition::cases()),
            'status' => ListingStatus::Active,
            'city' => fake()->randomElement(['Bratislava', 'Košice', 'Prešov', 'Žilina', 'Nitra', 'Banská Bystrica', 'Trnava', 'Trenčín', 'Martin', 'Poprad']),
            'show_phone' => fake()->boolean(50),
            'published_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ListingStatus::Draft, 'published_at' => null]);
    }

    public function sold(): static
    {
        return $this->state(['status' => ListingStatus::Sold]);
    }
}
