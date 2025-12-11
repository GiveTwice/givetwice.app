<?php

namespace App\Listeners;

use App\Models\GiftList;
use Illuminate\Auth\Events\Registered;

class CreateDefaultListForNewUser
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Use the user's locale preference or the current app locale
        $locale = $user->locale_preference ?? app()->getLocale() ?? 'en';

        // Temporarily set locale to get the correct translation
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);

        GiftList::create([
            'user_id' => $user->id,
            'name' => __('My wishlist'),
            'is_default' => true,
        ]);

        // Restore original locale
        app()->setLocale($originalLocale);
    }
}
