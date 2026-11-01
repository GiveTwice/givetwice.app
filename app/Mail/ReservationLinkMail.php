<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class ReservationLinkMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $email, public string $userLocale) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('All my reservations'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservations-link', text: 'emails.text.reservations-link', with: ['historyUrl' => URL::temporarySignedRoute('reservations.index', now()->addMinutes(30), ['locale' => $this->userLocale, 'email' => $this->email])]);
    }
}
