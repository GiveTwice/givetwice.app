<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') | {{ config('app.name', 'GiveTwice') }}</title>

    <!-- Meta description -->
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @else
        <meta name="description" content="{{ __('meta.home') }}">
    @endif

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Robots meta -->
    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @endif

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Welcome') | {{ config('app.name', 'GiveTwice') }}">
    @hasSection('description')
        <meta property="og:description" content="@yield('description')">
    @else
        <meta property="og:description" content="{{ __('meta.home') }}">
    @endif
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:site_name" content="{{ config('app.name', 'GiveTwice') }}">
    @php $currentLocale = \App\Enums\SupportedLocale::tryFrom(app()->getLocale()); @endphp
    @if($currentLocale)
        <meta property="og:locale" content="{{ $currentLocale->ogLocale() }}">
        @foreach(\App\Enums\SupportedLocale::cases() as $locale)
            @if($locale !== $currentLocale)
                <meta property="og:locale:alternate" content="{{ $locale->ogLocale() }}">
            @endif
        @endforeach
    @endif

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@GiveTwiceApp">
    <meta name="twitter:creator" content="@GiveTwiceApp">
    <meta name="twitter:title" content="@yield('title', 'Welcome') | {{ config('app.name', 'GiveTwice') }}">
    @hasSection('description')
        <meta name="twitter:description" content="@yield('description')">
    @else
        <meta name="twitter:description" content="{{ __('meta.home') }}">
    @endif
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#f97066">

    <!-- Hreflang alternate URLs for SEO -->
    <x-hreflang-tags />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-warm flex flex-col">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-coral-500 focus:text-white focus:rounded-lg focus:font-medium">
        {{ __('Skip to content') }}
    </a>

    <header class="bg-white border-b border-cream-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ mobileOpen: false }" @click.outside="mobileOpen = false">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <x-logo />
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('How it works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('About') }}</a>

                    <span class="text-cream-300">|</span>

                    <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-coral-600 transition-colors">{{ __('Login') }}</a>
                    @if(config('app.allow_registration'))
                        <x-nav-button href="{{ url('/' . app()->getLocale() . '/register') }}">{{ __('Sign Up') }}</x-nav-button>
                    @endif

                    <x-language-switcher />
                </div>

                <div class="md:hidden flex items-center">
                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        class="relative w-10 h-10 flex items-center justify-center text-gray-600 hover:text-gray-900 rounded-lg transition-colors"
                        aria-label="{{ __('Toggle navigation') }}"
                    >
                        <x-icons.menu
                            class="h-6 w-6 absolute transition-all duration-200"
                            x-bind:class="mobileOpen ? 'opacity-0 rotate-90 scale-75' : 'opacity-100 rotate-0 scale-100'"
                            aria-hidden="true"
                        />
                        <x-icons.close
                            class="h-6 w-6 absolute transition-all duration-200"
                            x-bind:class="mobileOpen ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-75'"
                            aria-hidden="true"
                        />
                    </button>
                </div>
            </div>

            <div
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                x-cloak
                class="md:hidden pb-4">
                <div class="space-y-2">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('How it works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('About') }}</a>

                    <div class="border-t border-cream-200 my-2"></div>

                    <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('Login') }}</a>
                    @if(config('app.allow_registration'))
                        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="block px-3 py-2 bg-coral-500 text-white rounded-lg text-center font-medium">{{ __('Sign Up') }}</a>
                    @endif

                    <div class="border-t border-cream-200 my-2"></div>
                    <div class="px-3 py-2">
                        <x-language-switcher />
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="fixed top-20 right-4 z-50 flex flex-col gap-3">
        @if (session('status'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 15000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-8"
                class="p-4 bg-white border border-teal-200 text-teal-800 rounded-xl shadow-lg flex items-center gap-3 max-w-sm"
            >
                <span class="flex-shrink-0 w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center">
                    <x-icons.checkmark class="w-4 h-4" />
                </span>
                <span class="text-sm">{{ session('status') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <x-icons.close class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>

    <main id="main-content" class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-cream-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">
                    &copy; {{ date('Y') }} GiveTwice. {{ __('All rights reserved.') }}
                </p>
                <div class="flex items-center gap-4">
                    <a href="https://github.com/GiveTwice" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="GitHub">
                        <x-icons.github class="w-5 h-5" />
                    </a>
                    <a href="https://x.com/GiveTwiceApp" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="X (Twitter)">
                        <x-icons.x-twitter class="w-5 h-5" />
                    </a>
                </div>
                <p class="text-gray-500 text-sm flex items-center">
                    <span class="text-coral-500 mr-2">&#10084;</span>
                    {{ __('All affiliate profits go to charity.') }}
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
