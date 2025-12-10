<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['en', 'nl', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && in_array($locale, self::SUPPORTED_LOCALES)) {
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
        }

        return $next($request);
    }

    public static function detectBrowserLocale(Request $request): string
    {
        $acceptLanguage = $request->header('Accept-Language', 'en');
        $preferred = substr($acceptLanguage, 0, 2);

        if (in_array($preferred, self::SUPPORTED_LOCALES)) {
            return $preferred;
        }

        return config('app.fallback_locale', 'en');
    }
}
