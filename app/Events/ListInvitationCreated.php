<?php

namespace App\Events;

use App\Models\ListInvitation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListInvitationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ListInvitation $invitation
    ) {}
}
