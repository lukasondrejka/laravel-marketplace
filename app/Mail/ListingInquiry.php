<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ListingInquiry extends Mailable
{
    public function __construct(
        public Listing $listing,
        public string $senderName,
        public string $senderEmail,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: __('Question about your listing: :title', ['title' => $this->listing->title]),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.listing-inquiry');
    }
}
