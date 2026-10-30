<?php

namespace App\Actions\Listings;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SyncListingImages
{
    /**
     * Make the listing's images match the given ordered list. The first image becomes the cover.
     *
     * Each entry is either the id of an image already attached to THIS listing (kept) or an
     * uploaded file (added). Existing images that are not listed are deleted. Ids that do not
     * belong to the listing are ignored, so another user's media can never be touched.
     * Duplicates are dropped and the list is capped at Listing::MAX_IMAGES.
     *
     * @param  array<int, int|UploadedFile>  $ordered
     */
    public function handle(Listing $listing, array $ordered): void
    {
        $ordered = collect($ordered)
            ->unique(fn ($item) => is_int($item) ? $item : spl_object_id($item))
            ->take(Listing::MAX_IMAGES)
            ->values()
            ->all();

        $existing = $listing->getMedia(Listing::IMAGES)->keyBy('id');
        $keep = collect($ordered)->filter(fn ($item) => is_int($item) && $existing->has($item))->all();

        $existing->except($keep)->each(fn (Media $media) => $media->delete());

        $finalIds = [];

        foreach ($ordered as $item) {
            if ($item instanceof UploadedFile) {
                $finalIds[] = $listing->addMedia($item->getRealPath())
                    ->usingFileName($item->hashName())
                    ->usingName(pathinfo($item->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection(Listing::IMAGES)
                    ->getKey();
            } elseif (in_array($item, $keep, true)) {
                $finalIds[] = $item;
            }
        }

        if ($finalIds !== []) {
            Media::setNewOrder($finalIds);
        }

        $listing->unsetRelation('media');
    }
}
