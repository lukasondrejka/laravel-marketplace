<?php

use App\Enums\ListingStatus;
use App\Livewire\Listings\Dashboard;
use App\Models\Listing;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('counts listings per status and filters by the status tab', function () {
    Listing::factory()->for($this->user)->count(2)->create();
    Listing::factory()->for($this->user)->draft()->create();
    Listing::factory()->for($this->user)->sold()->create();
    Listing::factory()->for($this->user)->create()->delete();
    Listing::factory()->create();

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    expect($component->get('counts'))->toBe(['' => 4, 'draft' => 1, 'active' => 2, 'sold' => 1]);
    expect($component->set('status', 'draft')->get('listings')->total())->toBe(1);
    expect($component->set('status', 'nonsense')->get('listings')->total())->toBe(4);
});

it('paginates at ten listings', function () {
    Listing::factory()->for($this->user)->count(12)->create();

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    expect($component->get('listings')->items())->toHaveCount(10);
});

it('publishes, unpublishes and marks as sold', function () {
    $listing = Listing::factory()->for($this->user)->draft()->create();

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);
    $component->call('setStatus', $listing->id, 'active')->assertDispatched('notify');
    expect($listing->fresh()->status)->toBe(ListingStatus::Active);

    $component->call('setStatus', $listing->id, 'sold');
    expect($listing->fresh()->status)->toBe(ListingStatus::Sold);
});

it('rejects unknown status values and forbidden transitions', function () {
    $draft = Listing::factory()->for($this->user)->draft()->create();

    Livewire::actingAs($this->user)->test(Dashboard::class)->call('setStatus', $draft->id, 'archived')->assertStatus(422);
    Livewire::actingAs($this->user)->test(Dashboard::class)->call('setStatus', $draft->id, 'sold')->assertHasErrors('status');

    expect($draft->fresh()->status)->toBe(ListingStatus::Draft);
});

it('does not let an unverified owner publish a listing', function () {
    $unverified = User::factory()->unverified()->create();
    $draft = Listing::factory()->for($unverified)->draft()->create();

    Livewire::actingAs($unverified)->test(Dashboard::class)
        ->call('setStatus', $draft->id, 'active')->assertForbidden();

    expect($draft->fresh()->status)->toBe(ListingStatus::Draft);
});

it('still lets an unverified owner hide or mark as sold', function () {
    $unverified = User::factory()->unverified()->create();
    $listing = Listing::factory()->for($unverified)->create();

    Livewire::actingAs($unverified)->test(Dashboard::class)->call('setStatus', $listing->id, 'draft');

    expect($listing->fresh()->status)->toBe(ListingStatus::Draft);
});

it('answers 404 for missing or soft deleted listings', function () {
    $gone = Listing::factory()->for($this->user)->create();
    $gone->delete();

    Livewire::actingAs($this->user)->test(Dashboard::class)->call('setStatus', $gone->id, 'draft')->assertNotFound();
    Livewire::actingAs($this->user)->test(Dashboard::class)->call('setStatus', 999999, 'draft')->assertNotFound();
});

it('refuses to delete without a confirmed target and cannot be pointed at foreign listings', function () {
    $foreign = Listing::factory()->create();

    Livewire::actingAs($this->user)->test(Dashboard::class)->call('delete')->assertStatus(422);
    Livewire::actingAs($this->user)->test(Dashboard::class)->call('confirmDelete', $foreign->id)->call('delete')->assertForbidden();

    expect($foreign->fresh()->trashed())->toBeFalse();
});

it('shows a prompt to verify the email to unverified users', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Verify your email address to post listings.');
});
