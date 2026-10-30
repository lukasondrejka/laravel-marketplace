<?php

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Enums\ListingType;

it('defines the allowed status transitions', function (ListingStatus $from, ListingStatus $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    'draft to active' => [ListingStatus::Draft, ListingStatus::Active, true],
    'draft to sold' => [ListingStatus::Draft, ListingStatus::Sold, false],
    'active to draft' => [ListingStatus::Active, ListingStatus::Draft, true],
    'active to sold' => [ListingStatus::Active, ListingStatus::Sold, true],
    'sold to active' => [ListingStatus::Sold, ListingStatus::Active, true],
    'sold to draft' => [ListingStatus::Sold, ListingStatus::Draft, true],
    'draft to draft' => [ListingStatus::Draft, ListingStatus::Draft, false],
]);

it('gives every enum case a label and every status a badge', function () {
    foreach (ListingStatus::cases() as $status) {
        expect($status->label())->toBeString()->not->toBeEmpty()
            ->and($status->badge())->toBeString()->not->toBeEmpty();
    }

    foreach ([...ListingCondition::cases(), ...ListingType::cases()] as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }

    foreach (ListingCondition::cases() as $condition) {
        expect($condition->schemaUrl())->toStartWith('https://schema.org/');
    }
});
