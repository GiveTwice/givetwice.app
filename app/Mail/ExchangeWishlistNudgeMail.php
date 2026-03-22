<?php

namespace App\Mail;

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExchangeWishlistNudgeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GiftExchangeParticipant $participant,
        public readonly GiftExchange $exchange,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Quick hint for whoever drew you').' — '.$this->exchange->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exchange-wishlist-nudge',
            with: [
                'participant' => $this->participant,
                'exchange' => $this->exchange,
                'registerUrl' => route('register', [
                    'locale' => $this->exchange->locale,
                ]).'?utm_source=givetwice&utm_medium=email&utm_campaign=wishlist-nudge',
            ],
        );
    }
}
