<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GiveTwice') }} - @yield('title', 'Home')</title>

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
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-warm flex flex-col">
    <header class="bg-white border-b border-cream-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <x-logo />
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('How It Works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('About') }}</a>

                    <span class="text-cream-300">|</span>

                    @auth
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Login') }}</a>
                        <x-nav-button href="{{ url('/' . app()->getLocale() . '/register') }}">{{ __('Sign Up') }}</x-nav-button>
                    @endauth

                    <x-language-switcher />

                    @auth
                        <x-profile-dropdown :user="auth()->user()" />
                    @endauth
                </div>

                <div class="md:hidden flex items-center">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-600 hover:text-gray-900">
                        <x-icons.menu class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="space-y-2">
                    <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('How It Works') }}</a>
                    <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('About') }}</a>

                    <div class="border-t border-cream-200 my-2"></div>

                    @auth
                        @php
                            $mobileUser = auth()->user();
                            $mobileHasImage = $mobileUser->hasProfileImage();
                            $mobileImageUrl = $mobileUser->getProfileImageUrl('thumb');
                            $mobileInitials = $mobileUser->getInitials();
                        @endphp
                        {{-- User info --}}
                        <div
                            class="px-3 py-3 flex items-center gap-3"
                            x-data="{
                                hasImage: @js($mobileHasImage),
                                imageUrl: @js($mobileImageUrl),
                                initials: @js($mobileInitials)
                            }"
                            @profile-image-updated.window="hasImage = $event.detail.hasImage; imageUrl = $event.detail.imageUrl; initials = $event.detail.initials"
                        >
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center overflow-hidden"
                                :class="hasImage ? 'ring-2 ring-cream-200' : 'bg-gradient-to-br from-coral-400 to-coral-500'"
                            >
                                <template x-if="hasImage">
                                    <img :src="imageUrl" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!hasImage">
                                    <span class="text-white font-bold text-sm tracking-tight" x-text="initials"></span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="border-t border-cream-200 my-2"></div>

                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
                            <x-icons.home class="w-5 h-5" />
                            <span>{{ __('Dashboard') }}</span>
                        </a>
                        <a href="{{ url('/' . app()->getLocale() . '/settings') }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
                            <x-icons.settings class="w-5 h-5" />
                            <span>{{ __('Settings') }}</span>
                        </a>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-3 py-2 text-gray-600 hover:text-red-700 hover:bg-red-50 rounded-lg">
                                <x-icons.logout class="w-5 h-5" />
                                <span>{{ __('Log out') }}</span>
                            </button>
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

    <div class="fixed top-20 right-4 z-50 flex flex-col gap-3">
        @if (session('success'))
            <div
                x-data="{ show: false }"
                x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 8000) })"
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-cloak
                class="px-4 py-3 bg-teal-500 text-white rounded-xl shadow-lg shadow-teal-500/25 flex items-center gap-3 max-w-sm"
                role="alert"
                aria-live="polite"
            >
                <span class="flex-shrink-0 w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                    <x-icons.checkmark class="w-4 h-4" stroke-width="2.5" />
                </span>
                <span class="text-sm font-medium flex-1">{{ session('success') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-white/70 hover:text-white transition-colors" aria-label="{{ __('Dismiss') }}">
                    <x-icons.close class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: false }"
                x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 10000) })"
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-cloak
                class="px-4 py-3 bg-coral-500 text-white rounded-xl shadow-lg shadow-coral-500/25 flex items-center gap-3 max-w-sm"
                role="alert"
                aria-live="assertive"
            >
                <span class="flex-shrink-0 w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                    <x-icons.warning class="w-4 h-4" stroke-width="2.5" />
                </span>
                <span class="text-sm font-medium flex-1">{{ session('error') }}</span>
                <button x-on:click="show = false" class="flex-shrink-0 text-white/70 hover:text-white transition-colors" aria-label="{{ __('Dismiss') }}">
                    <x-icons.close class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto pt-6 pb-20 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-cream-200 mt-16">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">

                <div class="md:col-span-1">
                    <a href="{{ url('/') }}" class="inline-flex mb-4">
                        <x-logo />
                    </a>
                    <p class="text-gray-500 text-sm">{{ __('Create and share wishlists. All affiliate profits go to charity.') }}</p>
                </div>

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
                        <li><a href="{{ route('transparency', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Transparency') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-cream-200 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    &copy; {{ date('Y') }} GiveTwice. {{ __('All rights reserved.') }}
                </p>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-coral-500">&#10084;</span>
                    <span class="text-gray-600">{{ __('All affiliate profits go to charity') }}</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
