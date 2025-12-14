<?php

namespace App\Http\Middleware;

use App\Enums\SupportedLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && SupportedLocale::isSupported($locale)) {
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
        }

        return $next($request);
    }

    public static function detectBrowserLocale(Request $request): string
    {
        $acceptLanguage = $request->header('Accept-Language', 'en');
        $preferred = substr($acceptLanguage, 0, 2);

        if (SupportedLocale::isSupported($preferred)) {
            return $preferred;
        }

        return config('app.fallback_locale', SupportedLocale::default()->value);
    }
}
