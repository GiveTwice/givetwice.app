<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
    @unless(View::hasSection('dynamic_og_image'))
        <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    @endunless
    <meta property="og:site_name" content="{{ config('app.name', 'GiveTwice') }}">
    @php use App\Enums\SupportedLocale; $currentLocale = SupportedLocale::tryFrom(app()->getLocale()); @endphp
    @if($currentLocale)
        <meta property="og:locale" content="{{ $currentLocale->ogLocale() }}">
        @foreach(SupportedLocale::cases() as $locale)
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
    @unless(View::hasSection('dynamic_og_image'))
        <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    @endunless

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#f97066">
    <x-ios-pwa-tags />

    <!-- Hreflang alternate URLs for SEO -->
    <x-hreflang-tags />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-warm flex flex-col">
    @impersonating($guard = null)
        <div class="bg-coral-500 text-white text-center py-2 px-4 text-sm font-medium sticky top-0 z-[60] safe-area-header safe-area-x">
            You are impersonating <strong>{{ auth()->user()->name }}</strong>.
            <a href="{{ route('impersonate.leave') }}" class="underline ml-2 font-bold hover:text-coral-100">
                Stop impersonating
            </a>
        </div>
    @endImpersonating

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-coral-500 focus:text-white focus:rounded-lg focus:font-medium">
        {{ __('Skip to content') }}
    </a>

    <header class="bg-white border-b border-cream-200 sticky top-0 z-40 md:static md:z-auto safe-area-header safe-area-x">
        <nav class="{{ auth()->check() ? 'app-frame' : 'max-w-7xl mx-auto' }} px-4 sm:px-6 lg:px-8" x-data="{ mobileOpen: false }" @click.outside="mobileOpen = false">
            @php
                $navExchangeSlugs = ['en' => 'secret-santa', 'nl' => 'lootjes-trekken', 'fr' => 'tirage-au-sort'];
                $navExchangeSlug = $navExchangeSlugs[app()->getLocale()] ?? 'secret-santa';
                $navExchangeLabels = ['en' => __('Secret Santa'), 'nl' => __('Lootjes trekken'), 'fr' => __('Tirage au sort')];
                $navExchangeLabel = $navExchangeLabels[app()->getLocale()] ?? __('Secret Santa');
                $dashboardUrl = route('dashboard.locale', ['locale' => app()->getLocale()]);
                $secretSantaUrl = route('dashboard.secret-santa', ['locale' => app()->getLocale()]);
                $friendsUrl = route('friends.index', ['locale' => app()->getLocale()]);
                $settingsUrl = route('settings', ['locale' => app()->getLocale()]);
                $mobileHelpLabel = auth()->check() ? __('FAQ') : __('How it works');
                $isAppNavActive = request()->routeIs('dashboard*', 'friends.*', 'settings*');
            @endphp

            <div class="flex h-16 items-center justify-between gap-4 sm:gap-6">
                <div class="flex items-center gap-3 sm:gap-4 md:gap-8 lg:gap-12">
                    <x-logo />

                    @auth
                        <div class="hidden md:flex items-center gap-8 lg:gap-10 text-[17px] text-gray-700">
                            <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="transition-colors hover:text-gray-900">{{ __('FAQ') }}</a>
                            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="transition-colors hover:text-gray-900">{{ __('About') }}</a>
                        </div>
                    @endauth
                </div>

                <div class="hidden md:flex items-center gap-4 lg:gap-6">
                    @auth
                        <x-language-switcher />

                        <a
                            href="{{ $dashboardUrl }}"
                            class="inline-flex items-center gap-2 rounded-full bg-coral-500 px-2.5 py-2 text-sm font-semibold text-white shadow-sm shadow-coral-500/20 transition-all duration-200 hover:bg-coral-600"
                            @if($isAppNavActive) aria-current="page" @endif
                        >
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15 text-white">
                                <x-icons.device-desktop class="h-4 w-4" />
                            </span>
                            <span>{{ __('Dashboard') }}</span>
                        </a>
                    @else
                        <a href="{{ route('exchanges.landing', ['locale' => app()->getLocale(), 'exchangeType' => $navExchangeSlug]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">🎲 {{ $navExchangeLabel }}</a>
                        <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('How it works') }}</a>
                        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('About') }}</a>
                        <span class="text-cream-300">|</span>
                        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Login') }}</a>
                        @if(config('app.allow_registration'))
                            <x-nav-button href="{{ url('/' . app()->getLocale() . '/register') }}">{{ __('Sign Up') }}</x-nav-button>
                        @endif
                        <x-language-switcher />
                    @endauth

                    @auth
                        <div class="flex items-center gap-3 border-l border-cream-200 pl-4">
                            <div class="hidden lg:block text-right">
                                <p class="text-sm font-semibold text-gray-900 truncate max-w-36">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ __('Signed in') }}</p>
                            </div>
                            <x-profile-dropdown :user="auth()->user()" />
                        </div>
                    @endauth
                </div>

                <x-mobile-menu-toggle />
            </div>

            <x-mobile-menu-panel>
                @auth
                    <a
                        href="{{ $dashboardUrl }}"
                        class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition-all {{ $isAppNavActive ? 'border-coral-500 bg-coral-500 text-white shadow-coral-500/20' : 'border-cream-200 bg-cream-50 text-gray-900' }}"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $isAppNavActive ? 'bg-white/15 text-white' : 'bg-white text-coral-500' }}">
                            <x-icons.device-desktop class="h-5 w-5" />
                        </span>
                        <span class="font-semibold">{{ __('Dashboard') }}</span>
                    </a>
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="mt-2 block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('Home') }}</a>
                @else
                    <a href="{{ route('exchanges.landing', ['locale' => app()->getLocale(), 'exchangeType' => $navExchangeSlug ?? 'secret-santa']) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">🎲 {{ $navExchangeLabel ?? __('Secret Santa') }}</a>
                @endauth
                <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ $mobileHelpLabel }}</a>
                <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">{{ __('About') }}</a>

                <div class="border-t border-cream-200 my-2"></div>

                @auth
                    @php
                        $mobileUser = auth()->user();
                        $mobileHasImage = $mobileUser->hasProfileImage();
                        $mobileImageUrl = $mobileUser->getProfileImageUrl('thumb');
                        $mobileInitials = $mobileUser->getInitials();
                    @endphp
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

                    <a href="{{ $dashboardUrl }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-cream-100 text-base">&#127873;</span>
                        <span>{{ __('Lists') }}</span>
                    </a>
                    <a href="{{ $secretSantaUrl }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-cream-100 text-base">&#127922;</span>
                        <span>{{ __('Secret Santa') }}</span>
                    </a>
                    <a href="{{ $friendsUrl }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
                        <x-icons.users class="w-5 h-5" />
                        <span>{{ __('Friends') }}</span>
                    </a>
                    <a href="{{ $settingsUrl }}" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-cream-100 rounded-lg">
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
            </x-mobile-menu-panel>
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

    <div
        x-data="{
            online: navigator.onLine,
            init() {
                window.addEventListener('online', () => this.online = true);
                window.addEventListener('offline', () => this.online = false);
            }
        }"
        x-show="!online"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        x-cloak
        class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 bg-cream-100 border border-cream-300 rounded-full shadow-lg flex items-center gap-2"
        role="status"
        aria-live="polite"
    >
        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
        <span class="text-sm font-medium text-gray-600">{{ __('Viewing offline') }}</span>
    </div>

    <main id="main-content" class="flex-grow safe-area-x">
        <div class="{{ auth()->check() ? 'app-frame' : 'max-w-7xl mx-auto' }} pt-6 pb-20 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <x-footer />

    <x-install-banner />

    @stack('scripts')
</body>
</html>
