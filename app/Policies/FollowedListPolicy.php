<?php

namespace App\Policies;

use App\Models\FollowedList;
use App\Models\User;

class FollowedListPolicy
{
    public function view(User $user, FollowedList $followedList): bool
    {
        return $followedList->user_id === $user->id;
    }

    public function update(User $user, FollowedList $followedList): bool
    {
        return $followedList->user_id === $user->id;
    }

    public function delete(User $user, FollowedList $followedList): bool
    {
        return $followedList->user_id === $user->id;
    }
}
