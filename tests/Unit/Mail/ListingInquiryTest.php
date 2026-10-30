<?php

use App\Mail\ListingInquiry;
use App\Models\Listing;
use App\Models\User;

function inquiry(string $name = 'Buyer', string $body = 'Is it available?', ?Listing $listing = null): ListingInquiry
{
    $listing ??= Listing::factory()->for(User::factory()->create(['email' => 'seller@example.com']))->create(['title' => 'Blue bike']);

    return new ListingInquiry($listing, $name, 'buyer@example.com', $body);
}

it('sets reply-to and subject and renders the listing link', function () {
    $mail = inquiry();

    $mail->assertHasSubject('Question about your listing: Blue bike');
    expect($mail->hasReplyTo('buyer@example.com'))->toBeTrue();
    $mail->assertSeeInHtml('Is it available?');
    $mail->assertSeeInHtml(route('listings.show', $mail->listing));
});

it('neutralises Markdown and HTML in visitor supplied text', function () {
    $mail = inquiry(
        name: '**Bank** support',
        body: "Please [verify your account](https://evil.example/login)\n![pixel](https://evil.example/p.png)\n<script>alert(1)</script>",
    );

    $html = $mail->render();

    expect($html)
        ->not->toContain('href="https://evil.example')
        ->not->toContain('<img src="https://evil.example')
        ->not->toContain('<script>alert')
        ->not->toContain('<strong>Bank</strong>')
        ->toContain('verify your account');
});

it('keeps ordinary punctuation readable', function () {
    expect(inquiry(body: 'Can I pick it up at 5pm? Price: 50 - 60 EUR (negotiable)!')->render())
        ->toContain('50 - 60 EUR')->toContain('negotiable');
});
