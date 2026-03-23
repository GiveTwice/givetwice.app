<?php

namespace App\Listeners;

use App\Events\GiftExchangeDrawCompleted;
use App\Mail\GiftExchangeInviteMail;
use Illuminate\Support\Facades\Mail;

class SendGiftExchangeInvites
{
    public function handle(GiftExchangeDrawCompleted $event): void
    {
        $exchange = $event->exchange;
        $exchange->load('participants');

        /** @var \App\Models\GiftExchangeParticipant $participant */
        foreach ($exchange->participants as $participant) {
            Mail::to($participant->email)
                ->queue(new GiftExchangeInviteMail($participant, $exchange));
        }
    }
}
