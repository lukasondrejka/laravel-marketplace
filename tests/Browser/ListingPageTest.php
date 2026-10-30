<?php

use App\Mail\ListingInquiry;
use App\Models\Listing;
use Illuminate\Support\Facades\Mail;

const E2E_LIGHTBOX_OPEN = "(() => { const l = document.querySelector('[data-testid=\"lightbox\"]'); return !!l && getComputedStyle(l).display !== 'none'; })()";

it('opens and closes the gallery lightbox and steps through the photos', function () {
    $listing = Listing::factory()->create(['title' => 'Gallery gadget']);
    e2eAttachImages($listing, 3);

    $page = visit("/listings/{$listing->slug}")->inLightMode()->assertSee('Gallery gadget');
    e2eWait($page, "document.querySelector('[data-testid=\"gallery-counter\"]')?.innerText === '1 / 3'", 'gallery shows the first of three photos');

    $page->click('[data-testid="gallery-image"]:visible');
    e2eWait($page, E2E_LIGHTBOX_OPEN, 'lightbox opens on click');

    $page->click('@lightbox-next');
    e2eWait($page, "document.querySelector('[data-testid=\"gallery-counter\"]').innerText === '2 / 3'", 'next moves to the second photo');

    $page->click('@lightbox-close');
    e2eWait($page, '!'.E2E_LIGHTBOX_OPEN, 'lightbox closes with the close button');

    // Escape closes it as well.
    $page->click('[data-testid="gallery-image"]:visible');
    e2eWait($page, E2E_LIGHTBOX_OPEN, 'lightbox opens again');
    $page->keys('main#main', 'Escape');
    e2eWait($page, '!'.E2E_LIGHTBOX_OPEN, 'lightbox closes with Escape');

    $page->assertNoJavaScriptErrors()->assertNoBrokenImages();
});

it('sends a message to the seller', function () {
    Mail::fake();
    $listing = Listing::factory()->create(['title' => 'Chatty chair']);

    $page = visit("/listings/{$listing->slug}")->inLightMode()
        ->fill('name', 'Bob Buyer')
        ->fill('email', 'bob@example.com')
        ->fill('message', 'Is the chair still available?');
    $page->click('@contact-send');

    e2eWait($page, "document.querySelector('[data-testid=\"contact-sent\"]') !== null", 'success message');
    $page->assertSee('Your message was sent.');

    Mail::assertSent(ListingInquiry::class, fn (ListingInquiry $mail) => $mail->hasTo($listing->user->email) && $mail->hasReplyTo('bob@example.com'));
});

it('shows validation errors when the message is too short', function () {
    Mail::fake();
    $listing = Listing::factory()->create();

    $page = visit("/listings/{$listing->slug}")->inLightMode()
        ->fill('name', 'Bob Buyer')
        ->fill('email', 'bob@example.com')
        ->fill('message', 'short');
    $page->click('@contact-send');

    e2eWait($page, "document.body.innerText.includes('at least 10 characters')", 'validation error');
    Mail::assertNothingSent();
});
