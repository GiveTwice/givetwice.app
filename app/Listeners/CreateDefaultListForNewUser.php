<?php

namespace App\Listeners;

use App\Models\GiftList;
use Illuminate\Auth\Events\Registered;

class CreateDefaultListForNewUser
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        GiftList::create([
            'user_id' => $user->id,
            'name' => __('My Wishlist'),
            'is_default' => true,
            'is_public' => false,
            'filter_type' => 'manual',
        ]);
    }
}
