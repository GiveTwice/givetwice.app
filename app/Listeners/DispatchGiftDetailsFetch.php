<?php

namespace App\Listeners;

use App\Actions\FetchGiftDetailsAction;
use App\Events\GiftCreated;
use App\Events\GiftUrlChanged;

class DispatchGiftDetailsFetch
{
    public function handle(GiftCreated|GiftUrlChanged $event): void
    {
        FetchGiftDetailsAction::dispatch($event->gift);
    }
}
