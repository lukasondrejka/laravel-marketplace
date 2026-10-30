<?php

namespace App\Actions\Listings;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class ChangeListingStatus
{
    /**
     * @throws ValidationException when the transition is not allowed
     */
    public function handle(Listing $listing, ListingStatus $status): Listing
    {
        if ($listing->status === $status) {
            return $listing;
        }

        if (! $listing->status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => __('A :from listing cannot be changed to :to.', [
                    'from' => $listing->status->label(),
                    'to' => $status->label(),
                ]),
            ]);
        }

        $listing->status = $status;

        if ($status === ListingStatus::Active && $listing->published_at === null) {
            $listing->published_at = now();
        }

        $listing->save();

        return $listing;
    }
}
