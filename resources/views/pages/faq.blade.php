@extends('layouts.app')

@section('title', __('FAQ'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Frequently Asked Questions') }}</h1>

    <div class="space-y-6">
        {{-- Question 1 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('What is :app?', ['app' => config('app.name')]) }}</h3>
            <p class="text-gray-600">{{ __(':app is a free wishlist platform that lets you create and share gift lists with friends and family. When someone buys a gift through our links, we donate the affiliate commission to charity.', ['app' => config('app.name')]) }}</p>
        </div>

        {{-- Question 2 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('How do I create a wishlist?') }}</h3>
            <p class="text-gray-600">{{ __('Simply register for a free account, then paste product URLs from any online store. We\'ll automatically fetch the product details for you.') }}</p>
        </div>

        {{-- Question 3 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Can others see who claimed a gift?') }}</h3>
            <p class="text-gray-600">{{ __('No! Claims are completely anonymous. The wishlist owner only sees that someone is getting the gift, not who.') }}</p>
        </div>

        {{-- Question 4 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Which charities do you support?') }}</h3>
            <p class="text-gray-600">{{ __('We rotate our charity partners seasonally. Check back for updates on which organizations we\'re currently supporting.') }}</p>
        </div>

        {{-- Question 5 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Is it really free?') }}</h3>
            <p class="text-gray-600">{{ __('Yes! :app is completely free to use. We sustain ourselves through affiliate partnerships, and all that revenue goes to charity.', ['app' => config('app.name')]) }}</p>
        </div>

        {{-- Question 6 --}}
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Do I need an account to claim a gift?') }}</h3>
            <p class="text-gray-600">{{ __('No, you can claim gifts anonymously with just your email address. We\'ll send you a confirmation link to verify your claim.') }}</p>
        </div>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">
            &larr; {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
