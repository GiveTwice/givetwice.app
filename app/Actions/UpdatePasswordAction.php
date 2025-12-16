<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    public function execute(User $user, string $newPassword, ?string $currentPassword = null): void
    {
        if ($user->password && $currentPassword === null) {
            throw ValidationException::withMessages([
                'current_password' => [__('Current password is required.')],
            ]);
        }

        if ($user->password && ! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The provided password does not match your current password.')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();
    }
}
