@php
    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];

    $locales = [
        'en' => 'en',
        'nl' => 'nl',
        'fr' => 'fr',
    ];

    $defaultLocale = 'en';
@endphp

@foreach ($locales as $code => $hreflang)
    @php
        $newParams = array_merge($routeParams, ['locale' => $code]);
        $url = $routeName ? route($routeName, $newParams) : url("/{$code}");
    @endphp
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
@endforeach

@php
    $defaultParams = array_merge($routeParams, ['locale' => $defaultLocale]);
    $defaultUrl = $routeName ? route($routeName, $defaultParams) : url("/{$defaultLocale}");
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}">
