@extends('layouts.app')

@section('title', __('Home'))

@section('content')
{{-- Hero Section --}}
<div class="relative overflow-hidden">
    <div class="grid lg:grid-cols-2 gap-8 items-center py-12 lg:py-20">
        {{-- Left: Text Content --}}
        <div class="text-left">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                {{ __('Give with') }} <span class="text-coral-500">{{ __('Love') }}</span>,<br>
                {{ __('Receive with Joy') }}
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-lg">
                {{ __('Create wishlists for any occasion. Share with family and friends. Add gifts from any online store.') }}
            </p>

            {{-- Benefit bullets --}}
            <ul class="space-y-3 mb-8">
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Add items from any online store') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Friends claim gifts secretly - no duplicates!') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('100% free - all revenue goes to charity') }} <span class="ml-1">&#10084;&#65039;</span>
                </li>
            </ul>

            {{-- CTA --}}
            <div>
                @guest
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                        {{ __('Start My Wishlist') }} <span class="ml-2">&#127873;</span>
                    </a>
                @else
                    <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                        {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
                    </a>
                @endguest
            </div>
        </div>

        {{-- Right: Visual Element --}}
        <div class="relative hidden lg:block">
            {{-- Decorative shapes inspired by LittleBuds --}}
            <div class="relative">
                {{-- Yellow blob background --}}
                <div class="absolute top-0 right-0 w-80 h-80 bg-sunny-200 rounded-full opacity-60 -z-10 transform translate-x-10"></div>

                {{-- Main card with gift illustration --}}
                <div class="relative bg-sunny-100 rounded-[2rem] p-8 transform rotate-2 shadow-lg">
                    <div class="bg-white rounded-2xl p-6 shadow-sm transform -rotate-2">
                        <div class="text-center">
                            <div class="text-6xl mb-4">&#127873;</div>
                            <h3 class="font-semibold text-gray-800 mb-2">{{ __('Birthday Wishlist') }}</h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <span class="text-sm text-gray-600">{{ __('Wireless Headphones') }}</span>
                                    <span class="text-xs bg-sunny-100 text-sunny-700 px-2 py-1 rounded-full">{{ __('Claimed') }}</span>
                                </div>
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <span class="text-sm text-gray-600">{{ __('Cozy Blanket') }}</span>
                                    <span class="text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full">{{ __('Available') }}</span>
                                </div>
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <span class="text-sm text-gray-600">{{ __('Book Set') }}</span>
                                    <span class="text-xs bg-sunny-100 text-sunny-700 px-2 py-1 rounded-full">{{ __('Claimed') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating notification card --}}
                <div class="absolute -bottom-4 -left-8 bg-white rounded-xl shadow-lg p-4 transform -rotate-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-coral-100 rounded-full flex items-center justify-center">
                            <span class="text-coral-500">&#10084;</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Sarah claimed a gift!') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Just now') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Decorative elements --}}
                <div class="absolute -top-4 right-20 text-coral-400">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="currentColor">
                        <path d="M20 0l2.5 17.5L40 20l-17.5 2.5L20 40l-2.5-17.5L0 20l17.5-2.5z"/>
                    </svg>
                </div>
                <div class="absolute top-1/2 -right-4 text-teal-300">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M4 12h16M4 6h16M4 18h16" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charity highlight box (replaces dual CTA) --}}
    @guest
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-6 lg:p-8 max-w-3xl mx-auto mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="text-4xl">&#10084;&#65039;</div>
                <div>
                    <p class="font-semibold text-gray-900">{{ __('Gifting That Gives Back') }}</p>
                    <p class="text-gray-600">{{ __('All affiliate revenue goes to charity') }}</p>
                </div>
            </div>
            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-5 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 font-medium transition-colors border border-gray-200">
                {{ __('Learn More') }} <span class="ml-2">&rarr;</span>
            </a>
        </div>
    </div>
    @endguest
</div>

{{-- How It Works Section --}}
<div id="how-it-works" class="bg-cream-100 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">{{ __('How It Works') }}</h2>
        <p class="text-center text-gray-600 mb-12">{{ __('Three simple steps to perfect gift-giving') }}</p>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Step 1 --}}
            <div class="text-center">
                <div class="w-16 h-16 bg-sunny-200 text-sunny-700 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 transform rotate-3">
                    &#127873;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">1. {{ __('Create') }}</h3>
                <p class="text-gray-600">{{ __('Add gifts from any online store by pasting a product URL. We\'ll fetch the details automatically.') }}</p>
            </div>

            {{-- Step 2 --}}
            <div class="text-center">
                <div class="w-16 h-16 bg-coral-100 text-coral-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 transform -rotate-2">
                    &#128140;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">2. {{ __('Share') }}</h3>
                <p class="text-gray-600">{{ __('Send your wishlist link to friends and family via email, chat, or social media.') }}</p>
            </div>

            {{-- Step 3 --}}
            <div class="text-center">
                <div class="w-16 h-16 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 transform rotate-1">
                    &#10003;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">3. {{ __('Receive') }}</h3>
                <p class="text-gray-600">{{ __('Others secretly claim gifts so you don\'t get duplicates. Everyone\'s happy!') }}</p>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                {{ __('Get Started - It\'s Free!') }}
            </a>
        </div>
    </div>
</div>

{{-- Mission Section --}}
<div class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-gradient-to-br from-coral-50 to-sunny-50 rounded-3xl p-8 lg:p-12 text-center border border-coral-100">
            <div class="text-5xl mb-6">&#10084;&#65039;</div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Gifting That Gives Back') }}</h2>
            <p class="text-lg text-gray-600 mb-6">
                {{ __('We believe gift-giving should spread joy everywhere. That\'s why we donate 100% of our affiliate commissions to charity.') }}
            </p>
            <p class="text-lg text-gray-600 mb-8">
                {{ __('Every gift on your wishlist has the potential to help someone in need.') }}
            </p>

            <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-coral-200">
                <span class="text-coral-500 mr-2">&#10084;</span>
                <span class="text-gray-800 font-medium">{{ __('All affiliate revenue goes to charity') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Testimonials Section --}}
<div class="bg-cream-50 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">{{ __('Loved by Families Everywhere') }}</h2>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-sunny-200 rounded-full flex items-center justify-center text-sunny-700 font-bold mr-3">S</div>
                    <div>
                        <p class="font-semibold text-gray-900">Sarah M.</p>
                        <p class="text-sm text-gray-500">{{ __('Mom of 3') }}</p>
                    </div>
                </div>
                <p class="text-gray-600">"{{ __('Finally, a wishlist app that doesn\'t spoil the surprise! My family loves it.') }}"</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-coral-100 rounded-full flex items-center justify-center text-coral-600 font-bold mr-3">T</div>
                    <div>
                        <p class="font-semibold text-gray-900">Thomas K.</p>
                        <p class="text-sm text-gray-500">{{ __('Gift enthusiast') }}</p>
                    </div>
                </div>
                <p class="text-gray-600">"{{ __('So easy to use, and knowing our purchases help charity makes it even better.') }}"</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 font-bold mr-3">E</div>
                    <div>
                        <p class="font-semibold text-gray-900">Emma L.</p>
                        <p class="text-sm text-gray-500">{{ __('Birthday planner') }}</p>
                    </div>
                </div>
                <p class="text-gray-600">"{{ __('No more duplicate gifts at Christmas! This app is a lifesaver.') }}"</p>
            </div>
        </div>
    </div>
</div>

{{-- Final CTA Section --}}
<div class="py-20 px-4 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Ready to Create Your Wishlist?') }}</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-xl mx-auto">{{ __('Join thousands of happy gift-givers. It only takes a minute to get started.') }}</p>
    @guest
        <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
            {{ __('Create Your Wishlist') }} <span class="ml-2">&#127873;</span>
        </a>
        <p class="mt-4 text-gray-500">{{ __('Free forever. No credit card required.') }}</p>
    @else
        <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
            {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
        </a>
    @endguest
</div>
@endsection
