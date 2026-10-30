<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Sold = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Sold => __('Sold'),
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Active => 'success',
            self::Sold => 'ghost',
        };
    }

    /**
     * Statuses this status may move to.
     *
     * @return array<int, self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [self::Draft, self::Sold],
            self::Sold => [self::Active, self::Draft],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }
}
