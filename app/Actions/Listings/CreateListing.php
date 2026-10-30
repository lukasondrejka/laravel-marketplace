<?php

namespace App\Actions\Listings;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateListing
{
    public function __construct(private SyncListingImages $syncImages) {}

    /**
     * @param  array<string, mixed>  $data  validated listing attributes
     * @param  array<int, UploadedFile>  $images  ordered, first is the cover
     */
    public function handle(User $user, array $data, array $images = []): Listing
    {
        return DB::transaction(function () use ($user, $data, $images) {
            $listing = new Listing($data);
            $listing->user()->associate($user);
            $listing->status = ListingStatus::tryFrom((string) ($data['status'] ?? '')) ?? ListingStatus::Draft;
            $listing->published_at = $listing->status === ListingStatus::Active ? now() : null;
            $listing->save();

            $this->syncImages->handle($listing, $images);

            return $listing;
        });
    }
}
