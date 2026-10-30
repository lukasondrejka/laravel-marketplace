<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;

/** Opens the create form with a logged-in seller and the mandatory fields filled in. */
function e2eFilledForm(string $title): object
{
    $category = Category::factory()->create(['name' => 'Bikes']);
    test()->actingAs(User::factory()->create());

    return visit('/listings/create')
        ->inLightMode()
        ->fill('[name="form.title"]', $title)
        ->select('[name="form.category_id"]', (string) $category->id)
        ->fill('[name="form.description"]', 'Well kept, new tyres.')
        ->fill('[name="form.price"]', '120');
}

/** First image tile source, used to tell tiles apart (every upload has its own preview URL). */
const E2E_TILE_SOURCES = "Array.from(document.querySelectorAll('[data-testid=\"image-tile\"] img')).map((i) => i.src)";

it('creates and publishes a listing with a photo chosen through the file input', function () {
    $page = e2eFilledForm('Red city bike');

    e2eUpload($page, ['one.jpg' => e2eImage()]);
    e2eWait($page, 'document.querySelectorAll(\'[data-testid="image-tile"]\').length === 1', 'one image tile');

    $page->click('@publish');
    e2eWait($page, "location.pathname === '/listings/red-city-bike'", 'redirect to the new listing');
    $page->assertSee('Red city bike')->assertSee('Listing published.')->assertNoJavaScriptErrors();

    $listing = Listing::query()->firstWhere('title', 'Red city bike');
    expect($listing->status)->toBe(ListingStatus::Active)
        ->and($listing->getMedia(Listing::IMAGES))->toHaveCount(1);
});

it('adds photos by drag and drop, reorders them, changes the cover and removes one', function () {
    $page = e2eFilledForm('Photo heavy bike');

    e2eUpload($page, ['a.jpg' => e2eImage(220, 40, 40), 'b.jpg' => e2eImage(40, 220, 40), 'c.jpg' => e2eImage(40, 40, 220)], viaDrop: true);
    e2eWait($page, 'document.querySelectorAll(\'[data-testid="image-tile"]\').length === 3', 'three image tiles');
    e2eWait($page, 'Array.from(document.querySelectorAll(\'[data-testid="image-tile"] img\')).every((i) => i.complete && i.naturalWidth > 0)', 'tile previews loaded');

    $original = $page->script('() => '.E2E_TILE_SOURCES);
    expect($original)->toHaveCount(3);

    // Drag the last tile onto the first one: it becomes the cover.
    $page->drag('[data-testid="image-tile"]:nth-child(3)', '[data-testid="image-tile"]:nth-child(1)');
    e2eWait($page, E2E_TILE_SOURCES.'[0] === '.json_encode($original[2]), 'dragged tile moved to the first position');
    expect($page->script('() => '.E2E_TILE_SOURCES))->toBe([$original[2], $original[0], $original[1]]);

    // Promote the (new) second tile to the cover with the button.
    $page->click('[data-testid="image-tile"]:nth-child(2) [data-testid="make-cover"]');
    e2eWait($page, E2E_TILE_SOURCES.'[0] === '.json_encode($original[0]), 'second tile became the cover');
    expect($page->script('() => '.E2E_TILE_SOURCES))->toBe([$original[0], $original[2], $original[1]]);

    // Remove the last tile.
    $page->click('[data-testid="image-tile"]:nth-child(3) [aria-label="Remove image"]');
    e2eWait($page, 'document.querySelectorAll(\'[data-testid="image-tile"]\').length === 2', 'tile removed');

    $page->click('@publish');
    e2eWait($page, "location.pathname === '/listings/photo-heavy-bike'", 'redirect to the new listing');

    $media = Listing::query()->firstWhere('title', 'Photo heavy bike')->getMedia(Listing::IMAGES);
    expect($media)->toHaveCount(2);
});
