<?php

namespace App\Actions\Listings;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateListing
{
    public function __construct(private SyncListingImages $syncImages) {}

    /**
     * @param  array<string, mixed>  $data  validated listing attributes (status is changed via ChangeListingStatus)
     * @param  array<int, int|UploadedFile>  $images  ordered image ids and new uploads
     */
    public function handle(Listing $listing, array $data, array $images): Listing
    {
        return DB::transaction(function () use ($listing, $data, $images) {
            unset($data['status'], $data['published_at']);

            $listing->update($data);
            $this->syncImages->handle($listing, $images);

            return $listing;
        });
    }
}
