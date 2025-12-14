<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - @yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex">

        <aside class="w-64 bg-gray-800 min-h-screen fixed">
            <div class="p-4">
                <h1 class="text-white text-xl font-bold">Admin Panel</h1>
                <p class="text-gray-400 text-sm">{{ config('app.name') }}</p>
            </div>

            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}"
                   class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.users*') ? 'bg-gray-700 text-white' : '' }}">
                    Users
                </a>
                <a href="{{ url('/horizon') }}"
                   class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white" target="_blank">
                    Horizon (Queues)
                </a>
            </nav>

            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-700">
                <div class="text-gray-400 text-sm mb-2">
                    Logged in as: {{ auth()->user()->name }}
                </div>
                <a href="{{ url('/en/dashboard') }}" class="block text-gray-300 hover:text-white text-sm mb-2">
                    &larr; Back to Site
                </a>
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-300 hover:text-white text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="ml-64 flex-1 p-8">
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
    </div>
</body>
</html>
