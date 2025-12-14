@php
    use App\Enums\SupportedLocale;

    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];
@endphp

@foreach (SupportedLocale::cases() as $locale)
    @php
        $newParams = array_merge($routeParams, ['locale' => $locale->value]);
        $url = $routeName ? route($routeName, $newParams) : url("/{$locale->value}");
    @endphp
    <link rel="alternate" hreflang="{{ $locale->hreflang() }}" href="{{ $url }}">
@endforeach

@php
    $defaultLocale = SupportedLocale::default();
    $defaultParams = array_merge($routeParams, ['locale' => $defaultLocale->value]);
    $defaultUrl = $routeName ? route($routeName, $defaultParams) : url("/{$defaultLocale->value}");
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}">
