<?php

namespace App\Http\Responses;

use App\Enums\SupportedLocale;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): Response
    {
        $locale = $this->extractLocaleFromReferer($request);

        return redirect()->to('/'.$locale);
    }

    private function extractLocaleFromReferer(Request $request): string
    {
        $referer = $request->header('referer', '');
        $path = parse_url($referer, PHP_URL_PATH) ?? '';

        $segments = array_filter(explode('/', $path));
        $firstSegment = reset($segments) ?: '';

        if (SupportedLocale::tryFrom($firstSegment)) {
            return $firstSegment;
        }

        return config('app.locale', 'en');
    }
}
