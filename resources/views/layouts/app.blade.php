<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gifting App') }} - @yield('title', 'Home')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
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

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
            <p class="text-center text-gray-500 text-sm">
                {{ __('All affiliate revenue goes to charity.') }}
            </p>
        </div>
    </footer>
</body>
</html>
