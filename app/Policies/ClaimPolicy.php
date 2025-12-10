<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Claim $claim): bool
    {
        // Users can view their own claims
        return $user->id === $claim->user_id;
    }

    public function create(User $user, Gift $gift): bool
    {
        // Cannot claim own gifts
        if ($user->id === $gift->user_id) {
            return false;
        }

        // Cannot claim already confirmed gifts
        if ($gift->isClaimed()) {
            return false;
        }

        return true;
    }

    public function update(User $user, Claim $claim): bool
    {
        // Only the claimer can update their claim
        return $user->id === $claim->user_id;
    }

    public function delete(User $user, Claim $claim): bool
    {
        // Only the claimer can delete (unclaim)
        return $user->id === $claim->user_id;
    }

    public function restore(User $user, Claim $claim): bool
    {
        return $user->id === $claim->user_id;
    }

    public function forceDelete(User $user, Claim $claim): bool
    {
        return $user->id === $claim->user_id && $user->is_admin;
    }
}
