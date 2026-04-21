@php
    use App\Enums\SupportedLocale;
    use App\Helpers\ExchangeHelper;
    use App\Helpers\OccasionHelper;

    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];

    // Check if this is an occasion route and filter locales accordingly
    $occasionKey = null;
    if ($routeName && str_starts_with($routeName, 'occasion.')) {
        $occasionKey = str_replace('occasion.', '', $routeName);
    }

    // Exchange landing pages use locale-specific route names AND slugs.
    $isExchangeLanding = $routeName && str_starts_with($routeName, 'exchange-landing.');

    // Filter locales for occasion pages (some are locale-restricted)
    $locales = SupportedLocale::cases();
    if ($occasionKey) {
        $locales = array_filter($locales, fn ($locale) => OccasionHelper::shouldShow($occasionKey, $locale->value));
    }

    $urlForLocale = function (SupportedLocale $locale) use ($routeName, $routeParams, $isExchangeLanding) {
        if ($isExchangeLanding) {
            $targetExchange = ExchangeHelper::getForLocale($locale->value);

            return $targetExchange
                ? route("exchange-landing.{$targetExchange['key']}", ['locale' => $locale->value])
                : url("/{$locale->value}");
        }

        $newParams = array_merge($routeParams, ['locale' => $locale->value]);

        return $routeName ? route($routeName, $newParams) : url("/{$locale->value}");
    };
@endphp

@foreach ($locales as $locale)
    <link rel="alternate" hreflang="{{ $locale->hreflang() }}" href="{{ $urlForLocale($locale) }}">
@endforeach

@php
    // For x-default, use the first available locale for this page
    $defaultLocale = $occasionKey
        ? collect($locales)->first() ?? SupportedLocale::default()
        : SupportedLocale::default();
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $urlForLocale($defaultLocale) }}">
