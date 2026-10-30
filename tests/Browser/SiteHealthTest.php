<?php

use App\Models\Listing;
use App\Models\User;

/**
 * Every main page must render without JavaScript errors, console output or broken images in both colour schemes.
 */
dataset('schemes', ['light', 'dark']);

dataset('pages', [
    'home' => fn () => '/',
    'browse' => fn () => '/listings',
    'listing' => function () {
        $listing = Listing::factory()->create();
        e2eAttachImages($listing, 2);

        return "/listings/{$listing->slug}";
    },
    'login' => fn () => '/login',
    'dashboard' => function () {
        $user = User::factory()->create();
        Listing::factory()->for($user)->create();
        test()->actingAs($user);

        return '/dashboard';
    },
]);

it('renders without console errors or broken images', function (Closure $url, string $scheme) {
    Listing::factory()->count(2)->create();

    $path = $url();
    $pending = visit($path);
    $page = $scheme === 'dark' ? $pending->inDarkMode() : $pending->inLightMode();

    e2eWait($page, "document.documentElement.dataset.theme === '{$scheme}'", "{$scheme} theme applied");
    // The stylesheet must really be loaded and themed (daisyUI sets color-scheme per theme).
    e2eWait($page, "getComputedStyle(document.documentElement).colorScheme === '{$scheme}'", "{$scheme} styles applied");

    // Load lazy images too, then wait until every image settled; only then is "broken" a meaningful verdict.
    $page->script("() => document.querySelectorAll('img[loading=lazy]').forEach((img) => { img.loading = 'eager'; })");
    e2eWait($page, 'document.readyState === "complete" && Array.from(document.images).every((img) => img.complete)', 'all images settled');

    // console.log calls and uncaught errors (Alpine/Livewire expression errors are rethrown), broken images ...
    $page->assertNoSmoke()->assertNoBrokenImages();

    // ... and any asset (CSS, JS, fonts, images) that answered with an error status.
    expect($page->script("() => performance.getEntriesByType('resource').filter((r) => r.responseStatus >= 400).map((r) => r.responseStatus + ' ' + r.name)"))->toBe([]);
})->with('pages')->with('schemes');

it('shows the friendly 404 page for unknown URLs', function () {
    $page = visit('/this-page-does-not-exist')->inLightMode();

    e2eWait($page, "document.body.innerText.includes('Page not found')", '404 page content');
    $page->assertSee('404')->assertSee('Back to home')->assertNoJavaScriptErrors();

    $page->click('Back to home');
    e2eWait($page, "location.pathname === '/'", 'back on the home page');
});

it('shows the 404 page for an unknown listing', function () {
    $page = visit('/listings/does-not-exist')->inLightMode();

    e2eWait($page, "document.body.innerText.includes('Page not found')", '404 page content');
});
