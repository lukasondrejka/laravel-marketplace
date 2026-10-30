<?php

namespace App\Actions\Listings;

use App\Mail\ListingInquiry;
use App\Models\Listing;
use Illuminate\Support\Facades\Mail;

class SendInquiry
{
    /**
     * Relay a buyer's message to the seller without ever revealing the seller's address.
     */
    public function handle(Listing $listing, string $name, string $email, string $message): void
    {
        Mail::to($listing->user)->send(new ListingInquiry($listing, $name, $email, $message));
    }
}
