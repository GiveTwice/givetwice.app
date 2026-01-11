@php
    use App\Enums\SupportedLocale;
    use App\Helpers\OccasionHelper;

    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];

    // Check if this is an occasion route and filter locales accordingly
    $occasionKey = null;
    if ($routeName && str_starts_with($routeName, 'occasion.')) {
        $occasionKey = str_replace('occasion.', '', $routeName);
    }

    // Filter locales for occasion pages (some are locale-restricted)
    $locales = SupportedLocale::cases();
    if ($occasionKey) {
        $locales = array_filter($locales, fn ($locale) => OccasionHelper::shouldShow($occasionKey, $locale->value));
    }
@endphp

@foreach ($locales as $locale)
    @php
        $newParams = array_merge($routeParams, ['locale' => $locale->value]);
        $url = $routeName ? route($routeName, $newParams) : url("/{$locale->value}");
    @endphp
    <link rel="alternate" hreflang="{{ $locale->hreflang() }}" href="{{ $url }}">
@endforeach

@php
    // For x-default, use the first available locale for this page
    $defaultLocale = $occasionKey
        ? collect($locales)->first() ?? SupportedLocale::default()
        : SupportedLocale::default();
    $defaultParams = array_merge($routeParams, ['locale' => $defaultLocale->value]);
    $defaultUrl = $routeName ? route($routeName, $defaultParams) : url("/{$defaultLocale->value}");
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}">
