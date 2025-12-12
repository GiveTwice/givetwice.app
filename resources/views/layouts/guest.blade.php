<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GiftWithLove') }} - @yield('title', 'Welcome')</title>

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
                    <a href="{{ url('/' . app()->getLocale()) }}" class="flex items-center space-x-2">
                        <span class="text-coral-500 text-2xl">&#10084;</span>
                        <span class="text-xl font-semibold">
                            <span class="text-gray-900">Gift</span><span class="text-coral-500">WithLove</span>
                        </span>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-coral-600 transition-colors">{{ __('Login') }}</a>
                    <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="px-4 py-2 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-medium text-sm transition-colors">{{ __('Sign Up') }}</a>

                    <x-language-switcher />
                </div>
            </div>
        </nav>
    </header>

    {{-- Toast Notifications --}}
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="text-sm">{{ session('status') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-cream-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm flex items-center justify-center">
                <span class="text-coral-500 mr-2">&#10084;</span>
                {{ __('All affiliate revenue goes to charity.') }}
            </p>
        </div>
    </footer>
</body>
</html>
