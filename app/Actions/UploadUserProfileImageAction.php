<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadUserProfileImageAction
{
    /**
     * Upload a profile image for the user.
     * Clears any OAuth avatar URL as uploaded image takes precedence.
     */
    public function execute(User $user, UploadedFile $file): void
    {
        $user->addMedia($file)
            ->toMediaCollection('profile');

        $user->update(['avatar' => null]);
    }
}
