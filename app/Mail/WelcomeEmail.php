<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Welcome to :app!', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        $dashboardUrl = url('/' . ($this->user->locale_preference ?? 'en') . '/dashboard');

        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
                'dashboardUrl' => $dashboardUrl,
            ],
        );
    }
}
