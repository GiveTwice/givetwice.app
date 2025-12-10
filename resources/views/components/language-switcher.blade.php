@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => 'English',
        'nl' => 'Nederlands',
        'fr' => 'Francais',
    ];

    // Get current route name and parameters
    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];
@endphp

<div class="flex items-center space-x-2">
    @foreach ($locales as $code => $name)
        @if ($code === $currentLocale)
            <span class="text-gray-900 font-medium">{{ strtoupper($code) }}</span>
        @else
            @php
                $newParams = array_merge($routeParams, ['locale' => $code]);
            @endphp
            <a href="{{ $routeName ? route($routeName, $newParams) : url("/{$code}") }}"
               class="text-gray-500 hover:text-gray-900">
                {{ strtoupper($code) }}
            </a>
        @endif
        @if (!$loop->last)
            <span class="text-gray-300">|</span>
        @endif
    @endforeach
</div>
