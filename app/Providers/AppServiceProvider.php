<?php

namespace App\Providers;

use App\Events\GiftCreated;
use App\Listeners\CreateDefaultListForNewUser;
use App\Listeners\DispatchGiftDetailsFetch;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Registered::class, CreateDefaultListForNewUser::class);
        Event::listen(GiftCreated::class, DispatchGiftDetailsFetch::class);
    }
}
