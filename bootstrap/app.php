<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackLastActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
            'admin' => AdminMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            TrackLastActivity::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $locale = $request->route('locale') ?? app()->getLocale();

            return route('login', ['locale' => $locale]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
