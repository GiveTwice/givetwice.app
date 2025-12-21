<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

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
        Model::preventLazyLoading(! $this->app->isProduction());

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', AppleProvider::class);
        });

        $this->registerHealthChecks();
    }

    private function registerHealthChecks(): void
    {
        $checks = [
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
            DatabaseCheck::new(),
            RedisCheck::new(),
            HorizonCheck::new(),
            CacheCheck::new(),
            SecurityAdvisoriesCheck::new()
                ->cacheResultsForMinutes(60),
        ];

        if ($this->app->isProduction()) {
            $checks[] = DebugModeCheck::new();
            $checks[] = EnvironmentCheck::new();
            $checks[] = OptimizedAppCheck::new();
        }

        Health::checks($checks);
    }
}
