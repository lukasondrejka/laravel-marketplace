<?php

namespace App\Actions\Listings;

use App\Models\Listing;

class DeleteListing
{
    /**
     * Soft delete: the row is hidden everywhere but kept (and restorable).
     */
    public function handle(Listing $listing): void
    {
        $listing->delete();
    }
}
