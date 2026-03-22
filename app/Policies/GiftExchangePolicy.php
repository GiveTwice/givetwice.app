<?php

namespace App\Policies;

use App\Models\GiftExchange;
use App\Models\User;

class GiftExchangePolicy
{
    public function view(User $user, GiftExchange $exchange): bool
    {
        return $exchange->organizer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function draw(User $user, GiftExchange $exchange): bool
    {
        return $exchange->organizer_id === $user->id && $exchange->isDraft();
    }

    public function viewStatus(User $user, GiftExchange $exchange): bool
    {
        return $exchange->organizer_id === $user->id;
    }
}
