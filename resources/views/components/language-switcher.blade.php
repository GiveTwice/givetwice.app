@php
    use App\Enums\SupportedLocale;

    $currentLocale = app()->getLocale();
    $currentLocaleEnum = SupportedLocale::tryFrom($currentLocale) ?? SupportedLocale::default();

    // Get current route name and parameters
    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParams = $currentRoute?->parameters() ?? [];
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">

    <button
        @click="open = !open"
        type="button"
        class="flex items-center space-x-2 px-3 py-2 rounded-xl bg-cream-100 hover:bg-cream-200 border border-cream-200 transition-all duration-200 group"
        aria-expanded="false"
        aria-haspopup="true"
    >
        <span class="text-lg leading-none">{{ $currentLocaleEnum->flag() }}</span>
        <span class="text-gray-700 font-medium text-sm">{{ $currentLocaleEnum->label() }}</span>
        <svg
            class="w-4 h-4 text-gray-500 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 mt-2 w-48 origin-top-right z-50"
        style="display: none;"
    >

        <div class="absolute -top-2 right-4 w-4 h-4 bg-white border-l border-t border-cream-200 transform rotate-45"></div>

        <div class="relative bg-white rounded-2xl shadow-lg border border-cream-200 overflow-hidden">
            <div class="py-2">
                @foreach (SupportedLocale::cases() as $locale)
                    @php
                        $newParams = array_merge($routeParams, ['locale' => $locale->value]);
                        $url = $routeName ? route($routeName, $newParams) : url("/{$locale->value}");
                        $isActive = $locale->value === $currentLocale;
                    @endphp
                    <a
                        href="{{ $url }}"
                        class="flex items-center px-4 py-3 hover:bg-cream-50 transition-colors {{ $isActive ? 'bg-cream-50' : '' }}"
                    >
                        <span class="text-xl mr-3">{{ $locale->flag() }}</span>
                        <span class="font-medium {{ $isActive ? 'text-coral-600' : 'text-gray-700' }}">
                            {{ $locale->label() }}
                        </span>
                        @if ($isActive)
                            <span class="ml-auto text-coral-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
