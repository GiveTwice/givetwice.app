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

        // Every request reaches us through Cloudflare, so without this request()->ip() is a Cloudflare server.
        // Every IP-based rate limit then counted all visitors behind that server together.
        // Only Cloudflare's own ranges are trusted, so a visitor who connects to the origin directly cannot fake an IP.
        // Copied from https://www.cloudflare.com/ips-v4 and /ips-v6 on 2026-11-10. Re-sync when Cloudflare adds a range.
        $middleware->trustProxies(at: [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
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
