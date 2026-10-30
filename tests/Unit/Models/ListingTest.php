<?php

use App\Enums\ListingType;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\QueryException;

describe('slugs', function () {
    it('transliterates Slovak diacritics', function () {
        expect(Listing::uniqueSlug('Žltý bicykel, ľahký a čistý'))->toBe('zlty-bicykel-lahky-a-cisty');
    });

    it('falls back for titles without usable characters', function () {
        expect(Listing::uniqueSlug('!!!'))->toBe('listing');
    });

    it('never ends with a dash after truncation', function () {
        $title = str_repeat('a', 79).' b';

        expect(Listing::uniqueSlug($title))->not->toEndWith('-');
    });

    it('avoids reserved and already used slugs including soft deleted ones', function () {
        expect(Listing::uniqueSlug('Create'))->not->toBe('create');

        $listing = Listing::factory()->create(['title' => 'Old table']);
        $listing->delete();

        expect(Listing::uniqueSlug('Old table'))->not->toBe($listing->slug)->toStartWith('old-table-');
    });
});

describe('presentation', function () {
    it('escapes html and only links http(s) urls in descriptions', function () {
        $listing = Listing::factory()->make(['description' => "<script>alert(1)</script>\nSee https://example.com/a?b=1&c=2. javascript:alert(1) \"><img src=x>"]);
        $html = (string) $listing->rich_description;

        expect($html)
            ->not->toContain('<script>')
            ->not->toContain('<img')
            ->toContain('&lt;script&gt;')
            ->toContain('<a href="https://example.com/a?b=1&amp;c=2"')
            ->toContain('rel="nofollow noopener noreferrer ugc"')
            ->not->toContain('href="javascript:')
            ->toContain('<br');
    });

    it('does not swallow trailing punctuation into links', function () {
        $html = (string) Listing::factory()->make(['description' => 'Visit https://example.com/page, please.'])->rich_description;

        expect($html)->toContain('href="https://example.com/page"');
    });

    it('builds a plain text teaser of at most 140 characters', function () {
        $listing = Listing::factory()->make(['description' => '<b>'.str_repeat('word ', 80).'</b>']);

        expect($listing->teaser)->not->toContain('<b>')->and(mb_strlen($listing->teaser))->toBeLessThanOrEqual(143);
    });

    it('formats prices with the listing currency', function () {
        $listing = Listing::factory()->make(['price' => '1250.5', 'currency' => 'EUR']);

        expect($listing->formattedPrice())->toContain('1,250.50')->toContain('€');
    });

    it('has no cover image when it has no media', function () {
        $listing = Listing::factory()->create();

        expect($listing->coverMedia())->toBeNull()->and($listing->coverUrl())->toBeNull();
    });
});

describe('ownership and contact', function () {
    it('knows its owner', function () {
        $owner = User::factory()->create();
        $listing = Listing::factory()->for($owner)->create();

        expect($listing->isOwnedBy($owner))->toBeTrue()
            ->and($listing->isOwnedBy(User::factory()->create()))->toBeFalse()
            ->and($listing->isOwnedBy(null))->toBeFalse();
    });

    it('exposes the phone only when the seller opted in and has one', function () {
        $seller = User::factory()->create(['phone' => '+421 900 111 222']);

        expect(Listing::factory()->for($seller)->create(['show_phone' => true])->contactPhone())->toBe('+421 900 111 222')
            ->and(Listing::factory()->for($seller)->create(['show_phone' => false])->contactPhone())->toBeNull()
            ->and(Listing::factory()->for(User::factory()->create(['phone' => null]))->create(['show_phone' => true])->contactPhone())->toBeNull();
    });
});

describe('scopes', function () {
    it('treats future publication dates and drafts as not active', function () {
        $live = Listing::factory()->create();
        Listing::factory()->draft()->create();
        Listing::factory()->sold()->create();
        Listing::factory()->create(['published_at' => now()->addDay()]);

        expect(Listing::active()->pluck('id')->all())->toBe([$live->id]);
    });

    it('applies inclusive price bounds where zero is a bound', function () {
        $free = Listing::factory()->create(['price' => 0]);
        $mid = Listing::factory()->create(['price' => 50]);
        $high = Listing::factory()->create(['price' => 100]);

        expect(Listing::priceBetween(0, 0)->pluck('id')->all())->toBe([$free->id])
            ->and(Listing::priceBetween(50, 100)->pluck('id')->sort()->values()->all())->toBe([$mid->id, $high->id])
            ->and(Listing::priceBetween(null, null)->count())->toBe(3);
    });

    it('filters by type', function () {
        Listing::factory()->create(['type' => ListingType::Offer]);
        $wanted = Listing::factory()->create(['type' => ListingType::Wanted]);

        expect(Listing::ofType(ListingType::Wanted)->pluck('id')->all())->toBe([$wanted->id])
            ->and(Listing::ofType(null)->count())->toBe(2);
    });

    it('caps the number of search words and the keyword length', function () {
        $sql = Listing::search(implode(' ', range(1, 30)))->toSql();
        expect(substr_count($sql, 'like'))->toBe(16);

        $bindings = Listing::search(str_repeat('a', 500))->getBindings();
        expect(mb_strlen($bindings[0]))->toBe(102);
    });

    it('sorts by newest and price with a stable tiebreak', function () {
        $a = Listing::factory()->create(['price' => 10, 'published_at' => now()->subDays(3)]);
        $b = Listing::factory()->create(['price' => 10, 'published_at' => now()->subDay()]);
        $c = Listing::factory()->create(['price' => 5, 'published_at' => now()->subDays(2)]);

        expect(Listing::sortBy('newest')->pluck('id')->all())->toBe([$b->id, $c->id, $a->id])
            ->and(Listing::sortBy('price_asc')->pluck('id')->all())->toBe([$c->id, $b->id, $a->id])
            ->and(Listing::sortBy('price_desc')->pluck('id')->all())->toBe([$b->id, $a->id, $c->id]);
    });
});

it('restricts the images collection to jpeg, png and webp', function () {
    $listing = new Listing;
    $listing->registerMediaCollections();

    $collection = $listing->getMediaCollection(Listing::IMAGES);

    expect(($collection->acceptsMimeTypes))->toBe(['image/jpeg', 'image/png', 'image/webp']);
});

it('keeps categories restricted while listings exist', function () {
    $category = Category::factory()->create();
    Listing::factory()->create(['category_id' => $category->id]);

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});
