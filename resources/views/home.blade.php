@extends('layouts.app')

@section('title', __('Home'))

@section('content')

<div class="relative overflow-hidden">
    <div class="grid lg:grid-cols-2 gap-8 items-center py-12 lg:py-20">

        <div class="text-left">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                {{ __('Every Gift') }}<br>
                {{ __('Gives') }} <span class="text-coral-500">{{ __('Twice') }}</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-lg">
                {{ __('Create wishlists your loved ones will love. When they buy, charity wins too — at no extra cost to anyone.') }}
            </p>

            <ul class="space-y-3 mb-8">
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Your loved one gets exactly what they wished for') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Charity receives 100% of our affiliate profits') }}
                </li>
                <li class="flex items-center text-gray-700">
                    <span class="text-teal-500 mr-3">&#10003;</span>
                    {{ __('Add gifts from any store · Secret claims · Always free') }}
                </li>
            </ul>

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

        <div class="relative hidden lg:block">

            <div class="relative">

                <div class="absolute top-0 right-0 w-80 h-80 bg-sunny-200 rounded-full opacity-60 -z-10 transform translate-x-10"></div>

                <div class="relative bg-sunny-100 rounded-[2rem] p-8 transform rotate-2 shadow-lg">
                    <div class="bg-white rounded-2xl p-6 shadow-sm transform -rotate-2">
                        <div class="text-center">
                            <div class="text-6xl mb-4">&#127873;</div>
                            <h3 class="font-semibold text-gray-800 mb-2">{{ __('Birthday Wishlist') }}</h3>
                            <div class="space-y-2">

                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <span class="text-sm text-gray-600">{{ __('Wireless Headphones') }}</span>
                                    <span class="text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full">{{ __('Available') }}</span>
                                </div>

                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 relative hero-item-cozy">
                                    <span class="text-sm text-gray-600">{{ __('Cozy Blanket') }}</span>
                                    <div class="relative">

                                        <span class="hero-badge-available text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full">{{ __('Available') }}</span>

                                        <span class="hero-badge-claimed absolute inset-0 text-xs bg-sunny-100 text-sunny-700 px-2 py-1 rounded-full">{{ __('Claimed') }}</span>
                                    </div>

                                    <div class="hero-claim-highlight-cozy absolute inset-0 rounded-lg pointer-events-none"></div>
                                </div>

                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 relative hero-item-book">
                                    <span class="text-sm text-gray-600">{{ __('Book Set') }}</span>
                                    <div class="relative">

                                        <span class="hero-badge-available-book text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full">{{ __('Available') }}</span>

                                        <span class="hero-badge-claimed-book absolute inset-0 text-xs bg-sunny-100 text-sunny-700 px-2 py-1 rounded-full">{{ __('Claimed') }}</span>
                                    </div>

                                    <div class="hero-claim-highlight-book absolute inset-0 rounded-lg pointer-events-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-notification-sarah absolute -bottom-4 -left-8 bg-white rounded-xl shadow-lg p-4 transform -rotate-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-coral-100 rounded-full flex items-center justify-center">
                            <span class="text-coral-500">&#10084;</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Sarah claimed a gift!') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Just now') }}</p>
                        </div>
                    </div>

                    <div class="hero-claim-arrow absolute -top-2 right-8 w-3 h-3 bg-coral-400 rounded-full opacity-0"></div>
                </div>

                <div class="hero-notification-nick absolute -bottom-4 left-4 bg-white rounded-xl shadow-lg p-4 transform rotate-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                            <span class="text-teal-500">&#10084;</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Nick claimed a gift!') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Just now') }}</p>
                        </div>
                    </div>

                    <div class="hero-claim-arrow-nick absolute -top-2 right-12 w-3 h-3 bg-teal-400 rounded-full opacity-0"></div>
                </div>

                <div class="absolute -top-4 right-20 text-coral-400">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="currentColor">
                        <path d="M20 0l2.5 17.5L40 20l-17.5 2.5L20 40l-2.5-17.5L0 20l17.5-2.5z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @guest
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-6 lg:p-8 max-w-3xl mx-auto mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="text-4xl">&#10084;&#65039;</div>
                <div>
                    <p class="font-semibold text-gray-900">{{ __('One gift. Two smiles.') }}</p>
                    <p class="text-gray-600">{{ __('Your purchase powers donations — automatically.') }}</p>
                </div>
            </div>
            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-5 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 font-medium transition-colors border border-gray-200">
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
        <p class="text-lg text-gray-600 mb-10 text-center max-w-2xl mx-auto">{{ __('When someone buys from your wishlist, stores pay us a commission. We donate all of it.') }}</p>

        <div class="relative">
            {{-- Connection line (desktop) --}}
            <div class="hidden lg:block absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-0.5 bg-gradient-to-r from-coral-300 to-teal-300"></div>

            <div class="grid md:grid-cols-2 gap-6 lg:gap-16">
                {{-- First give: To your loved one --}}
                <div class="relative bg-gradient-to-br from-coral-50 to-coral-100/50 rounded-2xl p-6 lg:p-8 border border-coral-200/60">
                    <div class="absolute -top-3 -left-1 bg-coral-500 text-white text-xs font-bold px-3 py-1 rounded-full">{{ __('GIVE #1') }}</div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-3xl">
                            &#127873;
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ __('Your loved one') }}</h3>
                            <p class="text-gray-600">{{ __('Gets exactly what they wished for. No duplicates, no guessing.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Second give: To charity --}}
                <div class="relative bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-2xl p-6 lg:p-8 border border-teal-200/60">
                    <div class="absolute -top-3 -left-1 bg-teal-500 text-white text-xs font-bold px-3 py-1 rounded-full">{{ __('GIVE #2') }}</div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-3xl">
                            &#10084;&#65039;
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ __('Those in need') }}</h3>
                            <p class="text-gray-600">{{ __('100% of our affiliate profits go straight to charity.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-gray-500 mt-8 text-sm">{{ __('No extra cost to you or the gift-giver. Just extra impact.') }}</p>
    </div>
</div>

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
                <p class="text-gray-600">"{{ __('Love the \'give twice\' concept — every birthday gift now helps charity too. No extra cost!') }}"</p>
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

<div class="py-20 px-4 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Ready to Give Twice?') }}</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-xl mx-auto">{{ __('Start your wishlist in seconds. Every gift makes a double impact.') }}</p>
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
