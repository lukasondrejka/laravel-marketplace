<?php

use App\Livewire\Listings\ContactSeller;
use App\Mail\ListingInquiry;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    $this->seller = User::factory()->create(['email' => 'seller@example.com']);
    $this->listing = Listing::factory()->for($this->seller)->create();
});

it('lets a guest message the seller, replying to the guest address', function () {
    Livewire::test(ContactSeller::class, ['listing' => $this->listing])
        ->set('name', 'Guest Buyer')
        ->set('email', 'buyer@example.com')
        ->set('message', 'Is this still available?')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Mail::assertSent(ListingInquiry::class, function (ListingInquiry $mail) {
        return $mail->hasTo('seller@example.com') && $mail->hasReplyTo('buyer@example.com');
    });
});

it('prefills name and email for signed in users', function () {
    $buyer = User::factory()->create();

    Livewire::actingAs($buyer)->test(ContactSeller::class, ['listing' => $this->listing])
        ->assertSet('name', $buyer->name)
        ->assertSet('email', $buyer->email)
        ->set('message', 'Can I pick it up on Friday?')
        ->call('send')
        ->assertHasNoErrors();

    Mail::assertSent(ListingInquiry::class);
});

it('validates the message', function () {
    Livewire::test(ContactSeller::class, ['listing' => $this->listing])
        ->set('name', '')->set('email', 'nope')->set('message', 'short')
        ->call('send')
        ->assertHasErrors(['name', 'email', 'message']);

    Mail::assertNothingSent();
});

it('does not let sellers message themselves or inactive listings', function () {
    Livewire::actingAs($this->seller)->test(ContactSeller::class, ['listing' => $this->listing])
        ->set('message', 'Talking to myself')->call('send')->assertForbidden();

    $draft = Listing::factory()->draft()->create();
    Livewire::test(ContactSeller::class, ['listing' => $draft])
        ->set('name', 'A')->set('email', 'a@example.com')->set('message', 'Hello there friend')->call('send')->assertNotFound();
});

it('rate limits messages', function () {
    $component = Livewire::test(ContactSeller::class, ['listing' => $this->listing])
        ->set('name', 'Spammer')->set('email', 'spam@example.com');

    foreach (range(1, 5) as $i) {
        $component->set('message', "Message number $i here")->call('send');
    }

    $component->set('message', 'One message too many')->call('send')->assertHasErrors('message');
    Mail::assertSent(ListingInquiry::class, 5);
});

it('never exposes the seller email on the listing page', function () {
    $this->get(route('listings.show', $this->listing))->assertOk()->assertDontSee('seller@example.com');
});
