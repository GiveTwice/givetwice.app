<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FriendDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $digestData,
    ) {
        $this->locale($user->locale_preference ?? 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Updates from your friends\' wishlists'),
        );
    }

    public function content(): Content
    {
        $locale = $this->user->locale_preference ?? 'en';

        return new Content(
            view: 'emails.friend-digest',
            with: [
                'user' => $this->user,
                'digestData' => $this->digestData,
                'friendsUrl' => route('friends.index', ['locale' => $locale]),
                'settingsUrl' => route('settings', ['locale' => $locale]),
                'locale' => $locale,
            ],
        );
    }
}
