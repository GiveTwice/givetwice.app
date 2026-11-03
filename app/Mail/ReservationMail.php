<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class ReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $undoUrl;

    public Collection $claims;

    public Collection $undoUrls;

    public function __construct(public Claim $claim, public string $userLocale = 'en', ?Collection $claims = null)
    {
        $this->claims = $claims ?? collect([$claim]);
        $this->undoUrls = $this->claims->mapWithKeys(fn (Claim $item) => [$item->id => URL::signedRoute('claim.undo', [
            'locale' => $userLocale, 'claim' => $item, 'email' => hash('sha256', $item->claimer_email ?? $item->user->email),
        ])]);
        $this->undoUrl = $this->undoUrls->get($claim->id);
        $this->locale($userLocale);
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: trans_choice('reservations.receipt_subject', $this->claims->count(), ['gift' => $this->claim->gift->title, 'count' => $this->claims->count()]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservation', text: 'emails.text.reservation', with: [
            'listUrl' => $this->claim->historyUrl($this->userLocale),
        ]);
    }
}
