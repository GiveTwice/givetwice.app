<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ReservationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $undoUrl;

    public function __construct(public Claim $claim, public string $userLocale = 'en')
    {
        $this->undoUrl = URL::signedRoute('claim.undo', ['locale' => $userLocale, 'claim' => $claim, 'email' => hash('sha256', $claim->claimer_email ?? $claim->user->email)]);
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Reserved: :gift', ['gift' => $this->claim->gift->title]));
    }

    public function content(): Content
    {
        $gift = $this->claim->gift;

        return new Content(view: 'emails.reservation', with: [
            'gift' => $gift,
            'listUrl' => $this->claim->historyUrl($this->userLocale),
            'undoUrl' => $this->undoUrl,
        ]);
    }
}
