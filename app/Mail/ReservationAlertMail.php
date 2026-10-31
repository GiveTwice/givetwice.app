<?php

namespace App\Mail;

use App\Models\ReservationAlert;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReservationAlertMail extends Mailable
{
    public function __construct(public ReservationAlert $alert) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('reservations.changed', ['owner' => $this->alert->before['owner']]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservation-alert');
    }
}
