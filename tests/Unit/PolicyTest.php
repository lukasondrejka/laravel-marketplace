<?php

use App\Models\Listing;
use App\Models\User;
use App\Policies\ListingPolicy;

it('allows only the owner to update and delete', function () {
    $owner = User::factory()->make(['id' => 1]);
    $other = User::factory()->make(['id' => 2]);
    $listing = Listing::factory()->make(['user_id' => 1]);
    $policy = new ListingPolicy;

    expect($policy->update($owner, $listing))->toBeTrue()
        ->and($policy->delete($owner, $listing))->toBeTrue()
        ->and($policy->update($other, $listing))->toBeFalse()
        ->and($policy->delete($other, $listing))->toBeFalse();
});

it('requires a verified email to create listings', function () {
    $policy = new ListingPolicy;

    expect($policy->create(User::factory()->make()))->toBeTrue()
        ->and($policy->create(User::factory()->unverified()->make()))->toBeFalse();
});

it('hides drafts from non-owners', function () {
    $listing = Listing::factory()->draft()->make(['user_id' => 1]);
    $policy = new ListingPolicy;

    expect($policy->view(null, $listing))->toBeFalse()
        ->and($policy->view(User::factory()->make(['id' => 2]), $listing))->toBeFalse()
        ->and($policy->view(User::factory()->make(['id' => 1]), $listing))->toBeTrue();
});
