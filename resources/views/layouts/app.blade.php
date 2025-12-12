<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GiftWithLove') }} - @yield('title', 'Home')</title>

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
<body class="min-h-screen bg-cream-50 flex flex-col">
    <header class="bg-white border-b border-cream-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <span class="text-2xl">&#10084;&#65039;</span>
                        <span class="text-xl font-bold text-gray-900">Gift<span class="text-coral-500">WithLove</span></span>
                    </a>
                </div>

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('How It Works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('About') }}</a>

                    <span class="text-cream-300">|</span>

                    @auth
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Dashboard') }}</a>
                        <form method="POST" action="{{ url('/logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Login') }}</a>
                        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="px-5 py-2 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-medium transition-colors">{{ __('Sign Up') }}</a>
                    @endauth

                    <x-language-switcher />
                </div>

                {{-- Mobile menu button --}}
                <div class="md:hidden flex items-center">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="space-y-2">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('How It Works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('About') }}</a>

                    <div class="border-t border-cream-200 my-2"></div>

                    @auth
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('Dashboard') }}</a>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('Login') }}</a>
                        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="block px-3 py-2 bg-coral-500 text-white rounded-lg text-center font-medium">{{ __('Sign Up') }}</a>
                    @endauth

                    <div class="border-t border-cream-200 my-2"></div>
                    <div class="px-3 py-2">
                        <x-language-switcher />
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Toast Notifications --}}
    <div class="fixed top-20 right-4 z-50 flex flex-col gap-3">
        @if (session('success'))
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="text-sm">{{ session('success') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
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
                class="p-4 bg-white border border-coral-200 text-coral-800 rounded-xl shadow-lg flex items-center gap-3 max-w-sm"
            >
                <span class="flex-shrink-0 w-6 h-6 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
                <span class="text-sm">{{ session('error') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-cream-200 mt-auto">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                {{-- Brand --}}
                <div class="md:col-span-1">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">&#10084;&#65039;</span>
                        <span class="text-xl font-bold text-gray-900">Gift<span class="text-coral-500">WithLove</span></span>
                    </a>
                    <p class="text-gray-500 text-sm">{{ __('Create and share wishlists. All affiliate revenue goes to charity.') }}</p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('Product') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('How It Works') }}</a></li>
                        <li><a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Create Wishlist') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('About') }}</a></li>
                        <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('Legal') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Terms of Service') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-cream-200 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    &copy; {{ date('Y') }} GiftWithLove. {{ __('All rights reserved.') }}
                </p>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-coral-500">&#10084;</span>
                    <span class="text-gray-600">{{ __('All affiliate revenue goes to charity') }}</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
