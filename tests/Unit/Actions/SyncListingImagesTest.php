<?php

use App\Actions\Listings\SyncListingImages;
use App\Models\Listing;
use App\Support\PlaceholderImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->listing = Listing::factory()->create();
});

function attachImage(Listing $listing, string $name)
{
    return $listing->addMedia(PlaceholderImage::make($name))->usingName($name)->toMediaCollection(Listing::IMAGES);
}

it('adds uploaded files in the given order and generates webp conversions', function () {
    app(SyncListingImages::class)->handle($this->listing, [
        UploadedFile::fake()->image('first.jpg', 1800, 1200),
        UploadedFile::fake()->image('second.png', 600, 400),
    ]);

    $media = $this->listing->fresh()->getMedia(Listing::IMAGES);

    expect($media)->toHaveCount(2)
        ->and($media->pluck('name')->all())->toBe(['first', 'second'])
        ->and($media[0]->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($media[0]->hasGeneratedConversion('large'))->toBeTrue()
        ->and($media[0]->getUrl('thumb'))->toEndWith('.webp')
        ->and($media[0]->getUrl('large'))->toEndWith('.webp')
        ->and($media[0]->file_name)->not->toBe('first.jpg');
});

it('keeps listed images, deletes unlisted ones and applies the order', function () {
    $a = attachImage($this->listing, 'a');
    $b = attachImage($this->listing, 'b');
    $c = attachImage($this->listing, 'c');

    app(SyncListingImages::class)->handle($this->listing->fresh(), [$c->id, $a->id]);

    $media = $this->listing->fresh()->getMedia(Listing::IMAGES);
    expect($media->pluck('id')->all())->toBe([$c->id, $a->id])
        ->and($b->fresh())->toBeNull();
});

it('ignores ids of images that belong to other listings', function () {
    $foreign = attachImage(Listing::factory()->create(), 'foreign');

    app(SyncListingImages::class)->handle($this->listing, [$foreign->id]);

    expect($foreign->fresh()->model_id)->not->toBe($this->listing->id)
        ->and($this->listing->fresh()->getMedia(Listing::IMAGES))->toHaveCount(0);
});

it('drops duplicates and never exceeds the image limit', function () {
    $a = attachImage($this->listing, 'a');

    $files = collect(range(1, Listing::MAX_IMAGES + 3))->map(fn ($i) => UploadedFile::fake()->image("$i.jpg", 50, 50))->all();
    app(SyncListingImages::class)->handle($this->listing->fresh(), [$a->id, $a->id, ...$files]);

    $media = $this->listing->fresh()->getMedia(Listing::IMAGES);
    expect($media)->toHaveCount(Listing::MAX_IMAGES)
        ->and($media->pluck('id')->filter(fn ($id) => $id === $a->id))->toHaveCount(1);
});

it('removes all images when given an empty list', function () {
    $a = attachImage($this->listing, 'a');

    app(SyncListingImages::class)->handle($this->listing->fresh(), []);

    expect($a->fresh())->toBeNull();
});
