<?php

use App\Actions\Users\DeleteAccount;
use App\Enums\ListingStatus;
use App\Livewire\Listings\Dashboard;
use App\Livewire\Listings\Form;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\PlaceholderImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create(['city' => 'Nitra']);
    $this->category = Category::factory()->create();
});

function fillForm($component, Category $category, array $overrides = [])
{
    $values = array_merge([
        'form.title' => 'Mountain bike',
        'form.category_id' => (string) $category->id,
        'form.description' => 'Well maintained mountain bike, 29 inch wheels.',
        'form.type' => 'offer',
        'form.price' => '250.50',
        'form.condition' => 'good',
        'form.city' => 'Zilina',
        'form.show_phone' => true,
    ], $overrides);

    foreach ($values as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

it('creates a draft listing with a unique slug', function () {
    fillForm(Livewire::actingAs($this->user)->test(Form::class), $this->category)
        ->call('save', 'draft')
        ->assertHasNoErrors()
        ->assertRedirect();

    $listing = Listing::firstWhere('title', 'Mountain bike');
    expect($listing)
        ->user_id->toBe($this->user->id)
        ->slug->toBe('mountain-bike')
        ->status->toBe(ListingStatus::Draft)
        ->published_at->toBeNull()
        ->and((string) $listing->price)->toBe('250.50');

    fillForm(Livewire::actingAs($this->user)->test(Form::class), $this->category)->call('save', 'active');
    $second = Listing::where('title', 'Mountain bike')->latest('id')->first();
    expect($second->slug)->not->toBe('mountain-bike')->and($second->status)->toBe(ListingStatus::Active)->and($second->published_at)->not->toBeNull();
});

it('does not use a reserved slug', function () {
    expect(Listing::uniqueSlug('Create'))->not->toBe('create');
});

it('validates listing input', function () {
    Livewire::actingAs($this->user)->test(Form::class)
        ->set('form.title', 'ab')
        ->set('form.price', '-5')
        ->set('form.category_id', '9999')
        ->call('save')
        ->assertHasErrors(['form.title', 'form.price', 'form.category_id', 'form.description']);

    fillForm(Livewire::actingAs($this->user)->test(Form::class), $this->category, ['form.price' => '10.999'])
        ->call('save')->assertHasErrors('form.price');

    fillForm(Livewire::actingAs($this->user)->test(Form::class), $this->category, ['form.price' => '0'])
        ->call('save')->assertHasNoErrors();
});

it('uploads multiple images, keeps their order and generates conversions', function () {
    $component = fillForm(Livewire::actingAs($this->user)->test(Form::class), $this->category)
        ->set('uploads', [
            UploadedFile::fake()->image('a.jpg', 800, 600),
            UploadedFile::fake()->image('b.png', 800, 600),
        ])
        ->assertHasNoErrors()
        ->assertCount('imageOrder', 2);

    $keys = $component->get('imageOrder');
    $component->call('makeCover', $keys[1])->call('save', 'active')->assertHasNoErrors();

    $listing = Listing::firstWhere('title', 'Mountain bike');
    $media = $listing->getMedia(Listing::IMAGES);
    expect($media)->toHaveCount(2)
        ->and($media->first()->name)->toBe('b')
        ->and($media->first()->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($listing->coverUrl())->toContain('thumb');
});

it('reorders images by drag and drop', function () {
    $component = Livewire::actingAs($this->user)->test(Form::class)
        ->set('uploads', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
            UploadedFile::fake()->image('c.jpg'),
        ]);
    [$a, $b, $c] = $component->get('imageOrder');

    $component->call('moveImageTo', $c, 0)->assertSet('imageOrder', [$c, $a, $b]);
    $component->call('moveImageTo', $c, 99)->assertSet('imageOrder', [$a, $b, $c]);
    $component->call('moveImageTo', 'bogus', 0)->assertSet('imageOrder', [$a, $b, $c]);
});

it('rejects non-images, oversized files and more than the allowed image count', function () {
    $component = Livewire::actingAs($this->user)->test(Form::class)
        ->set('uploads', [UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf')])
        ->assertHasErrors('uploads')
        ->assertCount('imageOrder', 0);

    $component->set('uploads', [UploadedFile::fake()->image('big.jpg')->size(6000)])
        ->assertHasErrors('uploads');

    $files = collect(range(1, Listing::MAX_IMAGES + 2))->map(fn ($i) => UploadedFile::fake()->image("$i.jpg"))->all();
    $component->set('uploads', $files)->assertHasErrors('uploads')->assertCount('imageOrder', Listing::MAX_IMAGES);
});

it('removes and reorders images when editing', function () {
    $listing = Listing::factory()->for($this->user)->create();
    foreach (['one', 'two', 'three'] as $name) {
        $listing->addMedia(PlaceholderImage::make($name))->usingName($name)->toMediaCollection(Listing::IMAGES);
    }
    $ids = $listing->fresh()->getMedia(Listing::IMAGES)->pluck('id');

    Livewire::actingAs($this->user)->test(Form::class, ['listing' => $listing])
        ->call('removeImage', 'm:'.$ids[0])
        ->call('makeCover', 'm:'.$ids[2])
        ->call('save')
        ->assertHasNoErrors();

    $names = $listing->fresh()->getMedia(Listing::IMAGES)->pluck('name')->all();
    expect($names)->toBe(['three', 'two']);
});

it('cannot delete or steal images belonging to another listing', function () {
    $mine = Listing::factory()->for($this->user)->create();
    $other = Listing::factory()->create();
    $foreign = $other->addMedia(PlaceholderImage::make('x'))->toMediaCollection(Listing::IMAGES);

    Livewire::actingAs($this->user)->test(Form::class, ['listing' => $mine])
        ->set('imageOrder', ['m:'.$foreign->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($foreign->fresh())->not->toBeNull()
        ->and($foreign->fresh()->model_id)->toBe($other->id)
        ->and($mine->fresh()->getMedia(Listing::IMAGES))->toHaveCount(0);
});

it('only lets the owner open the edit page', function () {
    $listing = Listing::factory()->for($this->user)->create();

    $this->actingAs($this->user)->get(route('listings.edit', $listing))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('listings.edit', $listing))->assertForbidden();
});

it('redirects guests from the edit page to login', function () {
    $listing = Listing::factory()->create();

    $this->get(route('listings.edit', $listing))->assertRedirect('/login');
});

it('updates a listing without changing its slug or status', function () {
    $listing = Listing::factory()->for($this->user)->create(['title' => 'Old title', 'slug' => 'old-title']);

    Livewire::actingAs($this->user)->test(Form::class, ['listing' => $listing])
        ->set('form.title', 'New title')
        ->call('save')
        ->assertHasNoErrors();

    expect($listing->fresh())->title->toBe('New title')->slug->toBe('old-title')->status->toBe(ListingStatus::Active);
});

it('forbids non-owners from updating through the component', function () {
    $listing = Listing::factory()->create();

    Livewire::actingAs($this->user)->test(Form::class, ['listing' => $listing])->assertForbidden();
});

it('blocks unverified users from creating listings', function () {
    $this->actingAs(User::factory()->unverified()->create())->get('/listings/create')->assertRedirect('/email/verify');
});

it('changes status along allowed transitions and sets published_at once', function () {
    $listing = Listing::factory()->draft()->for($this->user)->create();
    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    $component->call('setStatus', $listing->id, 'active');
    $published = $listing->fresh()->published_at;
    expect($listing->fresh()->status)->toBe(ListingStatus::Active)->and($published)->not->toBeNull();

    $component->call('setStatus', $listing->id, 'sold');
    expect($listing->fresh()->status)->toBe(ListingStatus::Sold);

    $component->call('setStatus', $listing->id, 'active');
    expect($listing->fresh()->published_at->equalTo($published))->toBeTrue();
});

it('rejects invalid status transitions', function () {
    $listing = Listing::factory()->draft()->for($this->user)->create();

    Livewire::actingAs($this->user)->test(Dashboard::class)
        ->call('setStatus', $listing->id, 'sold')
        ->assertHasErrors('status');

    expect($listing->fresh()->status)->toBe(ListingStatus::Draft);
});

it('soft deletes a listing from the dashboard', function () {
    $listing = Listing::factory()->for($this->user)->create();

    Livewire::actingAs($this->user)->test(Dashboard::class)->call('confirmDelete', $listing->id)->call('delete');

    expect(Listing::find($listing->id))->toBeNull()
        ->and(Listing::withTrashed()->find($listing->id))->not->toBeNull();
});

it('prevents changing or deleting other users listings', function () {
    $listing = Listing::factory()->create();

    Livewire::actingAs($this->user)->test(Dashboard::class)
        ->call('setStatus', $listing->id, 'sold')->assertForbidden();
    Livewire::actingAs($this->user)->test(Dashboard::class)
        ->call('confirmDelete', $listing->id)->call('delete')->assertForbidden();

    expect($listing->fresh())->deleted_at->toBeNull()->status->toBe(ListingStatus::Active);
});

it('shows only my listings on the dashboard', function () {
    Listing::factory()->for($this->user)->create(['title' => 'Mine']);
    Listing::factory()->create(['title' => 'Theirs']);

    $this->actingAs($this->user)->get('/dashboard')->assertOk()->assertSee('Mine')->assertDontSee('Theirs');
});

it('removes listings and their files when the account is deleted', function () {
    $listing = Listing::factory()->for($this->user)->create();
    $listing->addMedia(PlaceholderImage::make('x'))->toMediaCollection(Listing::IMAGES);

    (new DeleteAccount)->handle($this->user);

    expect(Listing::withTrashed()->count())->toBe(0)
        ->and(Media::count())->toBe(0);
});
