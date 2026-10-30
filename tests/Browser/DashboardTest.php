<?php

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;

it('publishes a draft from the dashboard', function () {
    $user = User::factory()->create();
    $draft = Listing::factory()->draft()->for($user)->create(['title' => 'Draft drone']);
    $this->actingAs($user);

    $page = visit('/dashboard')->inLightMode()->assertSee('Draft drone');
    $page->click('@status-active');

    e2eWait($page, "document.querySelector('[data-testid=\"listing-row\"]').innerText.includes('Active')", 'status badge switches to Active');
    expect($draft->fresh()->status)->toBe(ListingStatus::Active);
});

it('deletes a listing only after confirming in the modal', function () {
    $user = User::factory()->create();
    $listing = Listing::factory()->for($user)->create(['title' => 'Doomed desk']);
    $this->actingAs($user);

    $page = visit('/dashboard')->inLightMode()->assertSee('Doomed desk');
    $modalVisible = "(() => { const m = document.querySelector('[role=dialog][aria-label=\"Delete this listing?\"]'); return !!m && getComputedStyle(m).display !== 'none'; })()";

    // Cancel keeps the listing.
    $page->click('@delete-listing');
    e2eWait($page, $modalVisible, 'delete modal opens');
    $page->click('Cancel');
    e2eWait($page, '!'.$modalVisible, 'delete modal closes');
    expect($listing->fresh())->not->toBeNull();

    // Confirm removes it.
    $page->click('@delete-listing');
    e2eWait($page, $modalVisible, 'delete modal opens again');
    $page->click('@confirm-delete');
    e2eWait($page, "!document.body.innerText.includes('Doomed desk')", 'listing disappears from the dashboard');
    expect(Listing::query()->find($listing->id))->toBeNull();
});
