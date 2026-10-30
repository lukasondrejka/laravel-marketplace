<?php

use App\Models\Listing;
use App\Models\User;

it('reports health', function () {
    $this->get('/up')->assertOk();
});

it('renders friendly error pages with the right status', function () {
    $this->get('/definitely-not-a-page')->assertNotFound()->assertSee('does not exist');
    $this->get('/listings/no-such-listing')->assertNotFound();
    $this->get('/sellers/999999')->assertNotFound();
});

it('answers 403 for forbidden pages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('listings.edit', Listing::factory()->create()->slug))
        ->assertForbidden();
});

it('throttles the auth endpoints per IP', function () {
    foreach (range(1, 10) as $i) {
        $this->post('/forgot-password', ['email' => "user$i@example.com"]);
    }

    $this->post('/forgot-password', ['email' => 'user@example.com'])->assertStatus(429);
});

it('exposes only the public profile of sellers', function () {
    $seller = User::factory()->create(['email' => 'private@example.com', 'phone' => '+421 900 123 456', 'bio' => 'Hello there']);

    $this->get(route('sellers.show', $seller))
        ->assertOk()->assertSee('Hello there')
        ->assertDontSee('private@example.com')->assertDontSee('+421 900 123 456');
});

it('keeps guests and unverified users out of listing creation', function () {
    $this->get(route('listings.create'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->unverified()->create())->get(route('listings.create'))->assertRedirect(route('verification.notice'));
});
