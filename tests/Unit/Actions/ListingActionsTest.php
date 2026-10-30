<?php

use App\Actions\Listings\ChangeListingStatus;
use App\Actions\Listings\CreateListing;
use App\Actions\Listings\DeleteListing;
use App\Actions\Listings\UpdateListing;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Validation\ValidationException;

function listingData(array $overrides = []): array
{
    return array_merge([
        'category_id' => Category::factory()->create()->id,
        'type' => 'offer',
        'title' => 'Fridge',
        'description' => 'Works perfectly, collect in person.',
        'price' => '99.00',
        'currency' => 'EUR',
        'condition' => 'good',
        'city' => null,
        'show_phone' => false,
    ], $overrides);
}

it('creates drafts and active listings and ignores unknown statuses', function (?string $status, ListingStatus $expected) {
    $listing = app(CreateListing::class)->handle(User::factory()->create(), listingData(['status' => $status]));

    expect($listing->status)->toBe($expected)
        ->and($listing->published_at !== null)->toBe($expected === ListingStatus::Active);
})->with([
    'active' => ['active', ListingStatus::Active],
    'draft' => ['draft', ListingStatus::Draft],
    'unknown' => ['bogus', ListingStatus::Draft],
    'sold' => ['sold', ListingStatus::Draft],
    'missing' => [null, ListingStatus::Draft],
]);

it('assigns the listing to the given user, not to a user_id in the data', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $listing = app(CreateListing::class)->handle($owner, listingData(['user_id' => $other->id]));

    expect($listing->user_id)->toBe($owner->id);
});

it('cannot change status or publication date through the update action', function () {
    $listing = Listing::factory()->draft()->create();

    app(UpdateListing::class)->handle($listing, listingData(['title' => 'Renamed', 'status' => 'active', 'published_at' => now()]), []);

    expect($listing->fresh())
        ->title->toBe('Renamed')
        ->status->toBe(ListingStatus::Draft)
        ->published_at->toBeNull();
});

it('soft deletes listings', function () {
    $listing = Listing::factory()->create();

    app(DeleteListing::class)->handle($listing);

    expect(Listing::find($listing->id))->toBeNull()
        ->and(Listing::withTrashed()->find($listing->id))->not->toBeNull();
});

describe('status changes', function () {
    it('keeps the first publication date when reactivating', function () {
        $listing = Listing::factory()->create(['published_at' => now()->subDays(10)]);
        $original = $listing->published_at;

        $action = app(ChangeListingStatus::class);
        $action->handle($listing, ListingStatus::Sold);
        $action->handle($listing, ListingStatus::Active);

        expect($listing->fresh()->published_at->equalTo($original))->toBeTrue();
    });

    it('treats a change to the current status as a no-op', function () {
        $listing = Listing::factory()->create();

        expect(app(ChangeListingStatus::class)->handle($listing, ListingStatus::Active)->status)->toBe(ListingStatus::Active);
    });

    it('rejects a forbidden transition with a validation error', function () {
        $listing = Listing::factory()->draft()->create();

        expect(fn () => app(ChangeListingStatus::class)->handle($listing, ListingStatus::Sold))->toThrow(ValidationException::class)
            ->and($listing->fresh()->status)->toBe(ListingStatus::Draft);
    });
});
