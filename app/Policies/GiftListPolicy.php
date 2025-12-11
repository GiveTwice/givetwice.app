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
        // All lists are public/viewable
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GiftList $giftList): bool
    {
        return $user->id === $giftList->user_id;
    }

    public function delete(User $user, GiftList $giftList): bool
    {
        // Cannot delete the default list
        if ($giftList->is_default) {
            return false;
        }

        return $user->id === $giftList->user_id;
    }

    public function restore(User $user, GiftList $giftList): bool
    {
        return $user->id === $giftList->user_id;
    }

    public function forceDelete(User $user, GiftList $giftList): bool
    {
        // Cannot force delete the default list
        if ($giftList->is_default) {
            return false;
        }

        return $user->id === $giftList->user_id && $user->is_admin;
    }
}
