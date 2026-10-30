<?php

namespace App\Enums;

enum ListingType: string
{
    case Offer = 'offer';
    case Wanted = 'wanted';

    public function label(): string
    {
        return match ($this) {
            self::Offer => __('Offer'),
            self::Wanted => __('Wanted'),
        };
    }
}
