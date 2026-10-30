<?php

namespace App\Enums;

enum ListingCondition: string
{
    case New = 'new';
    case LikeNew = 'like_new';
    case Good = 'good';
    case Fair = 'fair';
    case ForParts = 'for_parts';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::LikeNew => __('Like new'),
            self::Good => __('Good'),
            self::Fair => __('Fair'),
            self::ForParts => __('For parts'),
        };
    }

    /**
     * schema.org itemCondition URL.
     */
    public function schemaUrl(): string
    {
        return match ($this) {
            self::New => 'https://schema.org/NewCondition',
            self::LikeNew, self::Good, self::Fair => 'https://schema.org/UsedCondition',
            self::ForParts => 'https://schema.org/DamagedCondition',
        };
    }
}
