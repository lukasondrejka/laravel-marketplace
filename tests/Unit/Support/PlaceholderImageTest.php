<?php

use App\Support\PlaceholderImage;

it('creates a deterministic jpeg of the requested size', function () {
    $a = PlaceholderImage::make('seed', 120, 90);
    $b = PlaceholderImage::make('seed', 120, 90);

    [$width, $height, $type] = getimagesize($a);

    expect([$width, $height, $type])->toBe([120, 90, IMAGETYPE_JPEG])
        ->and(md5_file($a))->toBe(md5_file($b));

    @unlink($a);
    @unlink($b);
});
