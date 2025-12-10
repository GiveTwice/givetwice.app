<?php

namespace App\Listeners;

use App\Actions\FetchGiftDetailsAction;
use App\Events\GiftCreated;

class DispatchGiftDetailsFetch
{
    public function handle(GiftCreated $event): void
    {
        FetchGiftDetailsAction::dispatch($event->gift);
    }
}
