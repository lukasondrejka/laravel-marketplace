<?php

use App\Models\Listing;
use App\Models\User;

it('shows an active listing with SEO tags and JSON-LD but never the seller email', function () {
    $seller = User::factory()->create(['email' => 'secret-seller@example.com', 'phone' => '+421 900 111 222']);
    $listing = Listing::factory()->for($seller)->create(['title' => 'Vintage lamp', 'price' => 45.5, 'show_phone' => true]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertSee('Vintage lamp')
        ->assertSee($seller->name)
        ->assertSee('+421 900 111 222')
        ->assertDontSee('secret-seller@example.com')
        ->assertSee('<link rel="canonical" href="'.route('listings.show', $listing).'"', false)
        ->assertSee('"@type":"Product"', false)
        ->assertSee('"priceCurrency":"EUR"', false);
});

it('hides drafts from everybody except the owner', function () {
    $listing = Listing::factory()->draft()->create();

    $this->get(route('listings.show', $listing))->assertNotFound();
    $this->actingAs(User::factory()->create())->get(route('listings.show', $listing))->assertNotFound();
    $this->actingAs($listing->user)->get(route('listings.show', $listing))->assertOk();
});

it('shows sold listings as sold and soft deleted ones as 404', function () {
    $sold = Listing::factory()->sold()->create();
    $this->get(route('listings.show', $sold))->assertOk()->assertSee('This item has been sold.');

    $sold->delete();
    $this->get(route('listings.show', $sold))->assertNotFound();
});

it('linkifies urls in the description safely', function () {
    $listing = Listing::factory()->create(['description' => 'See https://example.com/a?b=1 <script>alert(1)</script>']);

    expect((string) $listing->rich_description)
        ->toContain('<a href="https://example.com/a?b=1"')
        ->toContain('rel="nofollow noopener noreferrer ugc"')
        ->not->toContain('<script>');
});

it('shows the profile phone number only when the seller opted in', function () {
    $seller = User::factory()->create(['phone' => '+421 900 555 666']);
    $hidden = Listing::factory()->for($seller)->create(['show_phone' => false]);
    $shown = Listing::factory()->for($seller)->create(['show_phone' => true]);

    $this->get(route('listings.show', $hidden))->assertOk()->assertDontSee('+421 900 555 666');
    $this->get(route('listings.show', $shown))->assertOk()->assertSee('+421 900 555 666');
});

it('lists other active listings of the same seller below a listing', function () {
    $seller = User::factory()->create();
    $current = Listing::factory()->for($seller)->create(['title' => 'Current item']);
    Listing::factory()->for($seller)->create(['title' => 'Sellers other item']);
    Listing::factory()->for($seller)->draft()->create(['title' => 'Sellers draft']);
    Listing::factory()->create(['title' => 'Stranger item']);

    $this->get(route('listings.show', $current))
        ->assertOk()
        ->assertSee('Sellers other item')
        ->assertDontSee('Sellers draft');
});

it('renders a public seller page with only active listings and no email', function () {
    $seller = User::factory()->create(['email' => 'private@example.com', 'bio' => 'I sell old books.']);
    Listing::factory()->for($seller)->create(['title' => 'Public book']);
    Listing::factory()->for($seller)->draft()->create(['title' => 'Draft book']);

    $this->get(route('sellers.show', $seller))
        ->assertOk()
        ->assertSee($seller->name)
        ->assertSee('I sell old books.')
        ->assertSee('Public book')
        ->assertDontSee('Draft book')
        ->assertDontSee('private@example.com');
    $this->get('/sellers/999999')->assertNotFound();
});
