@extends('layouts.app')

@section('title', __('Home'))

@section('description', __('meta.home'))

@section('content')

<div class="relative overflow-hidden">
    <div class="grid lg:grid-cols-2 gap-8 items-center py-12 lg:py-20">

        <div class="text-left">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-[1.15]">
                <span class="block">{{ __('Your wishlist.') }}</span>
                <span class="block">{{ __('Gifts for you.') }}</span>
                <span class="block text-coral-500">{{ __('Good done quietly.') }}</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-lg">
                {{ __('Make a wishlist. Share it with friends and family. When they buy from it, we donate our commission to charity. Simple as that.') }}
            </p>

            <ul class="space-y-3 mb-8">
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Your friends get exactly what you asked for') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('100% of our profits go to charity') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Any online store · No duplicate gifts · Free to use') }}
                </li>
            </ul>

            <div>
                @guest
                    @if(config('app.allow_registration'))
                        <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                            {{ __('Start My Wishlist') }} <span class="ml-2">&#127873;</span>
                        </a>
                    @else
                        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                            {{ __('Get in touch') }}
                        </a>
                        <p class="mt-3 text-gray-500 text-sm">{{ __('Launching soon') }}</p>
                    @endif
                @else
                    <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                        {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
                    </a>
                @endguest
            </div>
        </div>

        <x-occasion-hero-card
            :title="__('Birthday Wishlist')"
            :gifts="[
                ['emoji' => '🎧', 'name' => 'Wireless Headphones', 'price' => 79, 'gradient' => 'from-blue-100 to-blue-200'],
                ['emoji' => '🧣', 'name' => 'Warm Scarf', 'price' => 45, 'gradient' => 'from-amber-100 to-orange-200'],
                ['emoji' => '📚', 'name' => 'Book Set', 'price' => 32, 'gradient' => 'from-emerald-100 to-teal-200'],
            ]"
        />
    </div>

    @guest
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-6 lg:p-8 max-w-3xl mx-auto mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="text-4xl">&#10084;&#65039;</div>
                <div>
                    <p class="font-semibold text-gray-900">{{ __('Here\'s the thing') }}</p>
                    <p class="text-gray-600">{{ __('Stores pay us a commission. We donate all of it. You pay nothing extra.') }}</p>
                </div>
            </div>
            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-5 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 font-medium transition-colors border border-gray-200 whitespace-nowrap">
                {{ __('How it works') }} <span class="ml-2">&rarr;</span>
            </a>
        </div>
    </div>
    @endguest
</div>

<x-how-it-works />

<div class="py-16 px-4">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-3 text-center">{{ __('Why "Twice"?') }}</h2>
        <p class="text-lg text-gray-600 mb-10 text-center max-w-2xl mx-auto">{{ __('Every gift from your wishlist does two things at once.') }}</p>

        <div class="relative">
            {{-- Connection line (desktop) --}}
            <div class="hidden lg:block absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-0.5 bg-gradient-to-r from-coral-300 to-teal-300"></div>

            <div class="grid md:grid-cols-2 gap-6 lg:gap-16">
                {{-- First give: To your friend --}}
                <div class="relative bg-gradient-to-br from-coral-50 to-coral-100/50 rounded-2xl p-6 lg:p-8 border border-coral-200/60">
                    <div class="absolute -top-3 -left-1 bg-coral-500 text-white text-xs font-bold px-3 py-1 rounded-full">{{ __('GIVE #1') }}</div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-3xl">
                            &#127873;
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ __('Your friend') }}</h3>
                            <p class="text-gray-600">{{ __('Gets exactly what they asked for. No duplicates, no guessing.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Second give: To charity --}}
                <div class="relative bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-2xl p-6 lg:p-8 border border-teal-200/60">
                    <div class="absolute -top-3 -left-1 bg-teal-700 text-white text-xs font-bold px-3 py-1 rounded-full">{{ __('GIVE #2') }}</div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-3xl">
                            &#10084;&#65039;
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ __('Charity') }}</h3>
                            <p class="text-gray-600">{{ __('We donate 100% of our profits. The store pays us, not you.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-gray-500 mt-8 text-sm">{{ __('You pay nothing extra. The gift-giver pays nothing extra. The donation comes from us.') }}</p>
    </div>
</div>

<div class="bg-cream-50 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">{{ __('Why we built this') }}</h2>
        <p class="text-lg text-gray-600 text-center mb-10 max-w-2xl mx-auto">{{ __('We kept running into the same problems with wishlists.') }}</p>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="w-12 h-12 bg-coral-100 rounded-xl flex items-center justify-center text-2xl mb-4">🎁</div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Duplicate gifts') }}</h3>
                <p class="text-gray-600 text-sm">{{ __('No way to know if someone already bought something. So you end up with three copies of the same book.') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="w-12 h-12 bg-sunny-100 rounded-xl flex items-center justify-center text-2xl mb-4">😬</div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Ugly apps') }}</h3>
                <p class="text-gray-600 text-sm">{{ __('Most wishlist apps look like they haven\'t been updated since 2010. Clunky, confusing, covered in ads.') }}</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-2xl mb-4">👁️</div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Your data, sold') }}</h3>
                <p class="text-gray-600 text-sm">{{ __('When the product is free, you\'re usually the product. Your gift preferences sold to advertisers.') }}</p>
            </div>
        </div>

        <p class="text-center text-gray-700 mt-10 max-w-2xl mx-auto">{{ __('So we built something better. A wishlist app that works, doesn\'t track you, and donates every cent of profit to charity.') }}</p>
    </div>
</div>

<div class="py-20 px-4 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Ready to try it?') }}</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-xl mx-auto">{{ __('Create your first wishlist in about a minute.') }}</p>
    @guest
        @if(config('app.allow_registration'))
            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
                {{ __('Create your wishlist') }} <span class="ml-2">&#127873;</span>
            </a>
            <p class="mt-4 text-gray-500">{{ __('Free. No credit card. No catch.') }}</p>
        @else
            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
                {{ __('Get in touch') }}
            </a>
            <p class="mt-4 text-gray-500">{{ __('Launching soon') }}</p>
        @endif
    @else
        <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
            {{ __('Go to my wishlists') }} <span class="ml-2">&#127873;</span>
        </a>
    @endguest
</div>
@endsection

@push('scripts')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "GiveTwice",
    "url": "{{ config('app.url') }}",
    "logo": "{{ asset('android-chrome-512x512.png') }}",
    "description": "{{ __('A wishlist platform where every gift makes a double impact.') }}",
    "sameAs": [
        "https://github.com/GiveTwice",
        "https://x.com/GiveTwiceApp"
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "GiveTwice",
    "url": "{{ config('app.url') }}",
    "description": "{{ __('meta.home') }}",
    "inLanguage": ["en", "nl", "fr"]
}
</script>
@endpush
