<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Gifting App'))</title>
    <meta name="description" content="@yield('description', __('Create and share your wishlists. All affiliate revenue goes to charity.'))">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', config('app.name', 'Gifting App'))">
    <meta property="og:description" content="@yield('og_description', __('Create and share your wishlists. All affiliate revenue goes to charity.'))">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('og_title', config('app.name', 'Gifting App'))">
    <meta property="twitter:description" content="@yield('og_description', __('Create and share your wishlists. All affiliate revenue goes to charity.'))">
    @hasSection('og_image')
    <meta property="twitter:image" content="@yield('og_image')">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex flex-col">
    <header class="bg-white shadow">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="text-xl font-semibold text-gray-900">
                        {{ config('app.name', 'Gifting App') }}
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <x-language-switcher />

                    <span class="text-gray-300">|</span>

                    @auth
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="text-gray-600 hover:text-gray-900">{{ __('Dashboard') }}</a>
                        <form method="POST" action="{{ url('/logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900">{{ __('Login') }}</a>
                        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-gray-600 hover:text-gray-900">{{ __('Register') }}</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-600 font-medium mb-2">{{ __('All affiliate revenue goes to charity.') }}</p>
                <p class="text-gray-500 text-sm">
                    {{ __('Create your own wishlist') }} -
                    <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-blue-600 hover:underline">{{ __('Get Started') }}</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
