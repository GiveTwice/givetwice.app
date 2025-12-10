<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gifting App') }} - @yield('title', 'Welcome')</title>

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

                    <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900">{{ __('Login') }}</a>
                    <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-gray-600 hover:text-gray-900">{{ __('Register') }}</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                {{ __('All affiliate revenue goes to charity.') }}
            </p>
        </div>
    </footer>
</body>
</html>
