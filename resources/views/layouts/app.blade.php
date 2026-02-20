<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Home') | {{ config('app.name', 'GiveTwice') }}</title>

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
    <meta property="og:title" content="@yield('title', 'Home') | {{ config('app.name', 'GiveTwice') }}">
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
    <meta name="twitter:title" content="@yield('title', 'Home') | {{ config('app.name', 'GiveTwice') }}">
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
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-warm flex flex-col">
    @impersonating($guard = null)
        <div class="bg-coral-500 text-white text-center py-2 px-4 text-sm font-medium sticky top-0 z-[60]">
            You are impersonating <strong>{{ auth()->user()->name }}</strong>.
            <a href="{{ route('impersonate.leave') }}" class="underline ml-2 font-bold hover:text-coral-100">
                Stop impersonating
            </a>
        </div>
    @endImpersonating

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

                    @auth
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Login') }}</a>
                        @if(config('app.allow_registration'))
                            <x-nav-button href="{{ url('/' . app()->getLocale() . '/register') }}">{{ __('Sign Up') }}</x-nav-button>
                        @endif
                    @endauth

                    <x-language-switcher />

                    @auth
                        <x-profile-dropdown :user="auth()->user()" />
                    @endauth
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
                        @if(config('app.allow_registration'))
                            <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="block px-3 py-2 bg-coral-500 text-white rounded-lg text-center font-medium">{{ __('Sign Up') }}</a>
                        @endif
                    @endauth

                    <div class="border-t border-cream-200 my-2"></div>
                    <div class="px-3 py-2">
                        <x-language-switcher />
                    </div>
                </div>
            </div>
        </nav>
    </header>

    @auth
        @if(auth()->user()->hasPendingListInvitations())
            <x-invitation-banner :invitations="auth()->user()->pendingListInvitations()->with('list:id,name,slug', 'inviter:id,name')->get()" />
        @endif
    @endauth

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

    <main id="main-content" class="flex-grow">
        <div class="max-w-7xl mx-auto pt-6 pb-20 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
