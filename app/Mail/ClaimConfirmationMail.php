<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Claim $claim,
        public string $locale = 'en'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Confirm your gift claim'),
        );
    }

    public function content(): Content
    {
        $confirmUrl = url('/'.$this->locale.'/claim/confirm/'.$this->claim->confirmation_token);

        return new Content(
            view: 'emails.claim-confirmation',
            with: [
                'claim' => $this->claim,
                'gift' => $this->claim->gift,
                'confirmUrl' => $confirmUrl,
            ],
        );
    }
}
