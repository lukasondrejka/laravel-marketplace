<?php

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Owners see all of their listings; everybody else only sees active/sold ones.
     */
    public function view(?User $user, Listing $listing): bool
    {
        return $listing->isOwnedBy($user) || $listing->status !== ListingStatus::Draft;
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Listing $listing): bool
    {
        return $listing->isOwnedBy($user);
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $listing->isOwnedBy($user);
    }
}
