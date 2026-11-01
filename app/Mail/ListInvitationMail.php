<?php

namespace App\Mail;

use App\Models\ListInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ListInvitation $invitation
    ) {
        $invitation->loadMissing('inviter', 'invitee');
        $this->locale($invitation->invitee->locale_preference ?? $invitation->inviter->locale_preference ?? 'en');
    }

    public function envelope(): Envelope
    {
        $inviterLocale = $this->locale;

        return new Envelope(
            subject: __(':name invited you to collaborate on ":list"', [
                'name' => $this->invitation->inviter->name,
                'list' => $this->invitation->list->name,
            ], $inviterLocale),
        );
    }

    public function content(): Content
    {
        $inviterLocale = $this->locale;
        $isExistingUser = $this->invitation->invitee_id !== null;

        $acceptUrl = url("/{$inviterLocale}/lists/invitation/{$this->invitation->token}");
        $registerUrl = url("/{$inviterLocale}/register").'?invitation='.$this->invitation->token;

        return new Content(
            view: 'emails.list-invitation',
            with: [
                'invitation' => $this->invitation,
                'inviter' => $this->invitation->inviter,
                'list' => $this->invitation->list,
                'isExistingUser' => $isExistingUser,
                'acceptUrl' => $acceptUrl,
                'registerUrl' => $registerUrl,
                'locale' => $inviterLocale,
            ],
        );
    }
}
