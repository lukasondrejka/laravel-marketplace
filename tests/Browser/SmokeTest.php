<?php

use App\Models\Listing;

it('renders the home page', function () {
    Listing::factory()->create(['title' => 'Vintage lamp']);

    visit('/')->assertSee('Vintage lamp')->assertNoSmoke();
});
