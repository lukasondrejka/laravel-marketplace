<?php

use App\Models\Category;
use App\Models\Listing;

const E2E_CARD_TITLES = "Array.from(document.querySelectorAll('[data-testid=\"listing-card\"] h3')).map((h) => h.innerText.trim())";

it('filters listings by keyword and category and clears the filters', function () {
    $bikes = Category::factory()->create(['name' => 'Bikes', 'slug' => 'bikes']);
    $books = Category::factory()->create(['name' => 'Books', 'slug' => 'books']);
    Listing::factory()->for($bikes)->create(['title' => 'Mountain bike']);
    Listing::factory()->for($bikes)->create(['title' => 'Road bike']);
    Listing::factory()->for($books)->create(['title' => 'Cookbook']);

    $page = visit('/listings')->inLightMode()->assertSee('3 results');

    $page->fill('q', 'mountain');
    e2eWait($page, E2E_CARD_TITLES.".join() === 'Mountain bike'", 'keyword narrows results to one listing');
    $page->assertSee('1 result');

    $page->click('Clear filters');
    e2eWait($page, E2E_CARD_TITLES.'.length === 3', 'clearing restores all listings');

    $page->select('category', 'books');
    e2eWait($page, E2E_CARD_TITLES.".join() === 'Cookbook'", 'category filter applies');
    $page->assertQueryStringHas('category', 'books');

    $page->fill('q', 'no such thing');
    e2eWait($page, "document.body.innerText.includes('No listings found')", 'empty state');
});

it('switches between grid and list layout and keeps it in the URL', function () {
    Listing::factory()->count(3)->create();

    $page = visit('/listings')->inLightMode();
    $layout = "document.querySelector('[data-testid=\"listing-results\"]').dataset.layout";

    e2eWait($page, "{$layout} === 'grid'", 'grid is the default');

    $page->click('@view-list');
    e2eWait($page, "{$layout} === 'list'", 'list layout');
    $page->assertQueryStringHas('view', 'list');

    $page->refresh();
    e2eWait($page, "{$layout} === 'list'", 'list layout survives a reload');

    $page->click('@view-grid');
    e2eWait($page, "{$layout} === 'grid'", 'back to grid');
});
