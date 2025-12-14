<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

describe('SetLocale::detectBrowserLocale', function () {
    it('detects the correct locale from Accept-Language header', function (string $headerName, ?string $headerValue, string $expectedLocale) {
        $request = Request::create('/');

        if ($headerValue !== null) {
            $request->headers->set($headerName, $headerValue);
        }

        $locale = SetLocale::detectBrowserLocale($request);

        expect($locale)->toBe($expectedLocale);
    })->with([
        'simple en' => ['Accept-Language', 'en', 'en'],
        'simple nl' => ['Accept-Language', 'nl', 'nl'],
        'simple fr' => ['Accept-Language', 'fr', 'fr'],
        'lowercase header name' => ['accept-language', 'en', 'en'],
        'mixed case header name' => ['accept-Language', 'nl', 'nl'],
        'complex with quality values' => ['Accept-Language', 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5', 'fr'],
        'language-region en-US' => ['Accept-Language', 'en-US', 'en'],
        'language-region nl-BE' => ['Accept-Language', 'nl-BE, nl;q=0.9', 'nl'],
        'unsupported locale falls back' => ['Accept-Language', 'de', 'en'],
        'unsupported locale with region falls back' => ['Accept-Language', 'de-DE, de;q=0.9', 'en'],
        'missing header falls back' => ['Accept-Language', null, 'en'],
        'empty header falls back' => ['Accept-Language', '', 'en'],
    ]);
});
