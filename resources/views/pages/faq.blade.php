@extends('layouts.app')

@section('title', __('FAQ'))

@section('content')
{{-- Hero Section --}}
<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-sunny-100 text-sunny-600 rounded-2xl text-3xl mb-6 transform -rotate-3">
        &#10067;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('Frequently Asked Questions') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('Everything you need to know about creating and sharing wishlists') }}</p>
</div>

{{-- FAQ Cards --}}
<div class="max-w-3xl mx-auto pb-16">
    <div class="space-y-4">
        {{-- Question 1 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    1
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('What is :app?', ['app' => config('app.name')]) }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __(':app is a free wishlist platform that lets you create and share gift lists with friends and family. When someone buys a gift through our links, we donate the affiliate commission to charity.', ['app' => config('app.name')]) }}</p>
                </div>
            </div>
        </div>

        {{-- Question 2 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    2
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('How do I create a wishlist?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Simply register for a free account, then paste product URLs from any online store. We\'ll automatically fetch the product details for you.') }}</p>
                </div>
            </div>
        </div>

        {{-- Question 3 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center font-bold">
                    3
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Can others see who claimed a gift?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('No! Claims are completely anonymous. The wishlist owner only sees that someone is getting the gift, not who.') }}</p>
                </div>
            </div>
        </div>

        {{-- Question 4 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    4
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Which charities do you support?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('We rotate our charity partners seasonally. Check back for updates on which organizations we\'re currently supporting.') }}</p>
                </div>
            </div>
        </div>

        {{-- Question 5 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    5
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Is it really free?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Yes! :app is completely free to use. We sustain ourselves through affiliate partnerships, and all that revenue goes to charity.', ['app' => config('app.name')]) }}</p>
                </div>
            </div>
        </div>

        {{-- Question 6 --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center font-bold">
                    6
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Do I need an account to claim a gift?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('No, you can claim gifts anonymously with just your email address. We\'ll send you a confirmation link to verify your claim.') }}</p>
                </div>
            </div>
        </div>

        {{-- Question 7 - New --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    7
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Can I add items from any store?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Yes! You can add items from virtually any online store. Just paste the product URL and we\'ll fetch the details automatically.') }}</p>
                </div>
            </div>
        </div>

        {{-- Question 8 - New --}}
        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    8
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('How do I share my wishlist?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Each wishlist has a unique shareable link. Simply copy the link and send it to friends and family via email, chat, or social media.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Still have questions? --}}
    <div class="mt-12 bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-8 text-center border border-coral-100">
        <div class="text-3xl mb-4">&#128172;</div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Still have questions?') }}</h2>
        <p class="text-gray-600 mb-6">{{ __('We\'re here to help! Reach out and we\'ll get back to you as soon as possible.') }}</p>
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
            {{ __('Contact Us') }}
        </a>
    </div>

    {{-- Back to home --}}
    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
