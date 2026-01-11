<?php

namespace App\Listeners;

use App\Helpers\OccasionHelper;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class CreateDefaultListForNewUser
{
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $userLocale = $user->locale_preference ?? app()->getLocale();
        $originalLocale = app()->getLocale();

        try {
            app()->setLocale($userLocale);

            $occasion = session()->pull('registration_occasion');
            $occasionData = $occasion ? OccasionHelper::get($occasion) : null;
            $listName = $occasionData ? __($occasionData['list_name']) : __('My wishlist');

            $list = GiftList::create([
                'creator_id' => $user->id,
                'name' => $listName,
                'is_default' => true,
            ]);

            $list->users()->attach($user->id, ['joined_at' => now()]);
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
