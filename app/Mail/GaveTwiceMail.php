<?php

namespace App\Mail;

use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GaveTwiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Claim $claim,
        public readonly Gift $gift,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You just gave twice').' ✨',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gave-twice',
            with: [
                'claim' => $this->claim,
                'gift' => $this->gift,
                'giftTitle' => $this->gift->title ?: __('A gift'),
            ],
        );
    }
}
