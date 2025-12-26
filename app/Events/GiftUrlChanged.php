<?php

namespace App\Events;

use App\Models\Gift;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftUrlChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Gift $gift,
    ) {}
}
