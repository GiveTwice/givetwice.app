<?php

namespace App\Listeners;

use App\Actions\CreateListAction;
use App\Helpers\OccasionHelper;
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

            $action = new CreateListAction;
            $action->execute($user, $listName, isDefault: true);
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
