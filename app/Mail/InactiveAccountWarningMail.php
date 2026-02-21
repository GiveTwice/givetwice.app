<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InactiveAccountWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your GiveTwice account will be deleted soon'),
        );
    }

    public function content(): Content
    {
        $locale = $this->user->locale_preference ?? 'en';
        $loginUrl = url('/'.$locale.'/login');
        $exportUrl = url('/'.$locale.'/settings');

        return new Content(
            view: 'emails.inactive-account-warning',
            with: [
                'user' => $this->user,
                'loginUrl' => $loginUrl,
                'exportUrl' => $exportUrl,
            ],
        );
    }
}
