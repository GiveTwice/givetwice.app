<?php

namespace App\Listeners;

use App\Events\ListInvitationCreated;
use App\Mail\ListInvitationMail;
use Illuminate\Support\Facades\Mail;

class SendListInvitationEmail
{
    public function handle(ListInvitationCreated $event): void
    {
        Mail::to($event->invitation->email)
            ->queue(new ListInvitationMail($event->invitation));
    }
}
