<?php

use App\Livewire\Listings\Index;
use App\Models\Category;
use App\Models\Listing;
use Livewire\Livewire;

it('renders the home page and the browse page', function () {
    Listing::factory()->count(3)->create();

    $this->get('/')->assertOk()->assertSee(__('Latest listings'));
    $this->get('/listings')->assertOk();
});

it('only lists active listings', function () {
    $active = Listing::factory()->create(['title' => 'Visible bike']);
    Listing::factory()->draft()->create(['title' => 'Hidden draft']);
    Listing::factory()->sold()->create(['title' => 'Already sold']);
    Listing::factory()->create(['title' => 'Trashed one'])->delete();

    Livewire::test(Index::class)
        ->assertSee($active->title)
        ->assertDontSee('Hidden draft')
        ->assertDontSee('Already sold')
        ->assertDontSee('Trashed one');
});

it('searches multiple words with AND semantics across title and description', function () {
    Listing::factory()->create(['title' => 'Leather jacket', 'description' => 'Black, size M']);
    Listing::factory()->create(['title' => 'Leather sofa', 'description' => 'Grey corner sofa']);
    Listing::factory()->create(['title' => 'Denim jacket', 'description' => 'Blue, made of leather patches']);

    Livewire::test(Index::class)
        ->set('q', 'leather jacket')
        ->assertSee('Leather jacket')
        ->assertSee('Denim jacket')
        ->assertDontSee('Leather sofa');
});

it('ignores blank search words and escapes LIKE wildcards', function () {
    Listing::factory()->create(['title' => 'Alpha item']);
    Listing::factory()->create(['title' => 'Beta item']);

    Livewire::test(Index::class)->set('q', '  ,  ')->assertSee('Alpha item')->assertSee('Beta item');
    Livewire::test(Index::class)->set('q', '%')->assertDontSee('Alpha item');
});

it('filters by category slug and condition', function () {
    $books = Category::factory()->create(['slug' => 'books']);
    Listing::factory()->for($books)->create(['title' => 'Novel', 'condition' => 'good']);
    Listing::factory()->create(['title' => 'Drill', 'condition' => 'new']);

    Livewire::test(Index::class)->set('category', 'books')->assertSee('Novel')->assertDontSee('Drill');
    Livewire::test(Index::class)->set('condition', 'new')->assertSee('Drill')->assertDontSee('Novel');
});

it('filters by price range including zero as a bound', function () {
    Listing::factory()->create(['title' => 'Free box', 'price' => 0]);
    Listing::factory()->create(['title' => 'Cheap thing', 'price' => 10]);
    Listing::factory()->create(['title' => 'Pricey thing', 'price' => 500]);

    Livewire::test(Index::class)->set('max', '0')->assertSee('Free box')->assertDontSee('Cheap thing');
    Livewire::test(Index::class)->set('min', '10')->set('max', '100')->assertSee('Cheap thing')->assertDontSee('Free box')->assertDontSee('Pricey thing');
    Livewire::test(Index::class)->set('min', 'abc')->assertSee('Free box')->assertSee('Pricey thing');
});

it('sorts by newest and by price', function () {
    Listing::factory()->create(['title' => 'Mid', 'price' => 50, 'published_at' => now()->subDays(2)]);
    Listing::factory()->create(['title' => 'Cheap', 'price' => 5, 'published_at' => now()->subDays(3)]);
    Listing::factory()->create(['title' => 'Dear', 'price' => 500, 'published_at' => now()->subDay()]);

    Livewire::test(Index::class)->assertSeeInOrder(['Dear', 'Mid', 'Cheap']);
    Livewire::test(Index::class)->set('sort', 'price_asc')->assertSeeInOrder(['Cheap', 'Mid', 'Dear']);
    Livewire::test(Index::class)->set('sort', 'price_desc')->assertSeeInOrder(['Dear', 'Mid', 'Cheap']);
    Livewire::test(Index::class)->set('sort', 'bogus')->assertSeeInOrder(['Dear', 'Mid', 'Cheap']);
});

it('binds filters to the URL query string', function () {
    Listing::factory()->create(['title' => 'Red kettle']);
    Listing::factory()->create(['title' => 'Blue lamp']);

    $this->get('/listings?q=kettle')->assertOk()->assertSee('Red kettle')->assertDontSee('Blue lamp');
});

it('paginates results and keeps filters in page links', function () {
    Listing::factory()->count(Index::PER_PAGE + 3)->create(['title' => 'Widget '.fake()->unique()->word()]);

    $this->get('/listings?q=Widget')->assertOk()->assertSee('q=Widget', false)->assertSee('page=2', false);
    $this->get('/listings?q=Widget&page=2')->assertOk();
});

it('returns a real 404 status for unknown pages and listings', function () {
    $this->get('/nope')->assertNotFound()->assertSee('404');
    $this->get('/listings/does-not-exist')->assertNotFound();
});

it('filters by listing type', function () {
    Listing::factory()->create(['title' => 'Selling a desk', 'type' => 'offer']);
    Listing::factory()->create(['title' => 'Looking for a desk lamp', 'type' => 'wanted']);

    Livewire::test(Index::class)->set('type', 'wanted')->assertSee('Looking for a desk lamp')->assertDontSee('Selling a desk');
    Livewire::test(Index::class)->set('type', 'offer')->assertSee('Selling a desk')->assertDontSee('Looking for a desk lamp');
});

it('switches between grid and list views', function () {
    Listing::factory()->create(['title' => 'Some item']);

    Livewire::test(Index::class)->set('view', 'list')->assertSee('Some item')->assertSet('view', 'list');
});
