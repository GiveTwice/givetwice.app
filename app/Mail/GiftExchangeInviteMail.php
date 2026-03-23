<?php

namespace App\Mail;

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftExchangeInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GiftExchangeParticipant $participant,
        public readonly GiftExchange $exchange,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎲 '.__('You\'ve been drawn!').' '.$this->exchange->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exchange-invite',
            with: [
                'participant' => $this->participant,
                'exchange' => $this->exchange,
                'revealUrl' => route('exchanges.reveal', [
                    'locale' => $this->exchange->locale,
                    'token' => $this->participant->token,
                ]),
            ],
        );
    }
}
