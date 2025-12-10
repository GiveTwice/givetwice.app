<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Claim $claim
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Someone claimed a gift from your wishlist!'),
        );
    }

    public function content(): Content
    {
        $gift = $this->claim->gift;
        $owner = $gift->user;
        $dashboardUrl = url('/' . ($owner->locale_preference ?? 'en') . '/dashboard');

        return new Content(
            view: 'emails.claim-notification',
            with: [
                'gift' => $gift,
                'owner' => $owner,
                'dashboardUrl' => $dashboardUrl,
            ],
        );
    }
}
