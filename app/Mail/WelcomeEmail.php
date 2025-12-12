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
        $locale = $this->user->locale_preference ?? 'en';

        // Get the user's default list for the direct link
        /** @var \App\Models\GiftList|null $defaultList */
        $defaultList = $this->user->lists()->where('is_default', true)->first();
        $wishlistUrl = $defaultList
            ? url('/'.$locale.'/list/'.$defaultList->slug)
            : url('/'.$locale.'/dashboard');

        $faqUrl = url('/'.$locale.'/faq');

        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
                'wishlistUrl' => $wishlistUrl,
                'faqUrl' => $faqUrl,
            ],
        );
    }
}
