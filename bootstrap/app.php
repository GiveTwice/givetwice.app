<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackLastActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Spatie\OgImage\Http\Middleware\RenderOgImageMiddleware;

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

        // The OG image package adds its middleware itself, but only once the app has booted.
        // A test builds the HTTP kernel after that, and the kernel then drops it again.
        // Listing it here keeps it in every request. The router skips the second copy.
        $middleware->appendToGroup('web', [
            TrackLastActivity::class,
            RenderOgImageMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $locale = $request->route('locale') ?? app()->getLocale();

            return route('login', ['locale' => $locale]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request) {
            if ($request->routeIs('claim.undo', 'reservations.*')) {
                return response()->view('claims.link-unavailable', [], 403)->header('Cache-Control', 'private, no-store');
            }
        });
    })->create();
