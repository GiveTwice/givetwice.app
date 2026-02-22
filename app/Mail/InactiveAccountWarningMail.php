<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InactiveAccountWarningMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your GiveTwice account will be deleted soon'),
        );
    }

    public function content(): Content
    {
        $locale = $this->user->locale_preference ?? config('app.locale');

        return new Content(
            view: 'emails.inactive-account-warning',
            with: [
                'loginUrl' => route('login', ['locale' => $locale]),
                'settingsUrl' => route('settings', ['locale' => $locale]),
            ],
        );
    }
}
