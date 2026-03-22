<?php

namespace App\Mail;

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExchangeRevealReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GiftExchangeParticipant $participant,
        public readonly GiftExchange $exchange,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your draw is getting lonely').' — '.$this->exchange->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exchange-reveal-reminder',
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
