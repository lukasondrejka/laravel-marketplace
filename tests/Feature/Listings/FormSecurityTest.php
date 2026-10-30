<?php

use App\Enums\ListingStatus;
use App\Livewire\Listings\Form;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

function validListing($component, Category $category)
{
    return $component
        ->set('form.title', 'Mountain bike')
        ->set('form.category_id', (string) $category->id)
        ->set('form.description', 'Well maintained mountain bike, 29 inch wheels.')
        ->set('form.price', '250')
        ->set('form.condition', 'good');
}

it('only publishes through the explicit active status, anything else becomes a draft', function (string $status) {
    validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category)->call('save', $status);

    expect(Listing::firstWhere('title', 'Mountain bike')->status)->toBe(ListingStatus::Draft);
})->with(['sold', 'ACTIVE', '', 'draft; drop table']);

it('rate limits creating listings per user', function () {
    RateLimiter::clear('listing-create:'.$this->user->id);

    foreach (range(1, Form::CREATE_LIMIT_PER_HOUR) as $i) {
        validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category)->call('save', 'draft')->assertHasNoErrors();
    }

    validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category)
        ->call('save', 'draft')
        ->assertHasErrors('form.title')
        ->assertNoRedirect();

    expect(Listing::count())->toBe(Form::CREATE_LIMIT_PER_HOUR);

    // Another user is unaffected.
    $other = User::factory()->create();
    validListing(Livewire::actingAs($other)->test(Form::class), $this->category)->call('save', 'draft')->assertRedirect();
});

it('does not rate limit editing', function () {
    $listing = Listing::factory()->for($this->user)->create();
    RateLimiter::increment('listing-create:'.$this->user->id, amount: 100);

    Livewire::actingAs($this->user)->test(Form::class, ['listing' => $listing])->call('save')->assertHasNoErrors()->assertRedirect();
});

it('caps tampered image orders on save', function () {
    $component = validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category);
    $files = collect(range(1, Listing::MAX_IMAGES))->map(fn ($i) => UploadedFile::fake()->image("$i.jpg", 40, 40))->all();
    $component->set('uploads', $files);

    $order = $component->get('imageOrder');
    $component->set('imageOrder', [...$order, ...$order])->call('save', 'draft')->assertHasNoErrors();

    expect(Listing::firstWhere('title', 'Mountain bike')->getMedia(Listing::IMAGES))->toHaveCount(Listing::MAX_IMAGES);
});

it('validates every listing field', function (string $field, mixed $value) {
    validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category)
        ->set($field, $value)
        ->call('save', 'draft')
        ->assertHasErrors($field);

    expect(Listing::count())->toBe(0);
})->with([
    'type' => ['form.type', 'swap'],
    'short title' => ['form.title', 'ab'],
    'long title' => ['form.title', str_repeat('a', 121)],
    'unknown category' => ['form.category_id', '99999'],
    'short description' => ['form.description', 'too short'],
    'long description' => ['form.description', str_repeat('a', 5001)],
    'negative price' => ['form.price', '-1'],
    'text price' => ['form.price', 'free'],
    'three decimals' => ['form.price', '1.234'],
    'huge price' => ['form.price', '100000000'],
    'condition' => ['form.condition', 'broken'],
    'long city' => ['form.city', str_repeat('a', 101)],
]);

it('does not open the edit page of a soft deleted listing', function () {
    $listing = Listing::factory()->for($this->user)->create();
    $listing->delete();

    $this->actingAs($this->user)->get(route('listings.edit', $listing->slug))->assertNotFound();
});

it('stores html in titles as inert text', function () {
    validListing(Livewire::actingAs($this->user)->test(Form::class), $this->category)
        ->set('form.title', '<script>alert(1)</script> bike')
        ->call('save', 'active');

    $listing = Listing::firstOrFail();
    $this->get(route('listings.show', $listing))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
});
