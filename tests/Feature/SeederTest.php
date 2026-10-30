<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds the categories idempotently', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::count())->toBe(count(CategorySeeder::CATEGORIES))
        ->and(Category::orderBy('sort_order')->first()->slug)->toBe('electronics')
        ->and(Category::pluck('slug')->unique())->toHaveCount(count(CategorySeeder::CATEGORIES));
});

it('seeds a usable demo marketplace', function () {
    Storage::fake('public');

    $this->seed(DemoSeeder::class);

    $demo = User::firstWhere('email', DemoSeeder::DEMO_EMAIL);

    expect($demo)->not->toBeNull()->and($demo->hasVerifiedEmail())->toBeTrue()
        ->and(User::count())->toBe(9)
        ->and(Listing::count())->toBeGreaterThan(40)
        ->and(Listing::where('status', ListingStatus::Active)->count())->toBeGreaterThan(0)
        ->and(Listing::where('status', ListingStatus::Draft)->whereNotNull('published_at')->count())->toBe(0)
        ->and(Listing::doesntHave('media')->count())->toBe(0);

    $this->post('/login', ['email' => DemoSeeder::DEMO_EMAIL, 'password' => DemoSeeder::DEMO_PASSWORD])->assertRedirect();
    $this->assertAuthenticatedAs($demo);
});
