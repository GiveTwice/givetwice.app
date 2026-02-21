<?php

namespace App\Policies;

use App\Models\GiftList;
use App\Models\User;

class GiftListPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, GiftList $giftList): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function delete(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function invite(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function leave(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function viewCollaborators(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function restore(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user);
    }

    public function forceDelete(User $user, GiftList $giftList): bool
    {
        return $giftList->hasUser($user) && $user->is_admin;
    }
}
