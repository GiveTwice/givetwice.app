<?php

namespace App\Policies;

use App\Models\Gift;
use App\Models\User;

class GiftPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Gift $gift): bool
    {
        return $user->id === $gift->user_id || $gift->hasAccessibleListFor($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Gift $gift): bool
    {
        return $user->id === $gift->user_id || $gift->hasAccessibleListFor($user);
    }

    public function delete(User $user, Gift $gift): bool
    {
        return $user->id === $gift->user_id || $gift->hasAccessibleListFor($user);
    }

    public function restore(User $user, Gift $gift): bool
    {
        return $user->id === $gift->user_id || $gift->hasAccessibleListFor($user);
    }

    public function forceDelete(User $user, Gift $gift): bool
    {
        return ($user->id === $gift->user_id || $gift->hasAccessibleListFor($user)) && $user->is_admin;
    }
}
