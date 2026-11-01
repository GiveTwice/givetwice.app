<?php

namespace App\Mail;

use App\Models\ReservationAlert;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class ReservationAlertMail extends Mailable
{
    public Collection $alerts;

    public function __construct(public ReservationAlert $alert, ?Collection $alerts = null)
    {
        $this->alerts = $alerts ?? collect([$alert]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('reservations.changed', ['owner' => $this->alert->before['owner']]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservation-alert', text: 'emails.reservation-alert-text');
    }
}
