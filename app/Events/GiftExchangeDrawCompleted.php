<?php

namespace App\Events;

use App\Models\GiftExchange;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftExchangeDrawCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly GiftExchange $exchange,
    ) {}
}
