<?php

namespace App\Actions;

use App\Models\User;

class DeleteUserProfileImageAction
{
    /**
     * Delete the user's profile image.
     * Also clears any OAuth avatar URL.
     */
    public function execute(User $user): void
    {
        $user->clearMediaCollection('profile');
        $user->update(['avatar' => null]);
    }
}
