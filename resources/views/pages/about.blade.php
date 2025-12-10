@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('About Us') }}</h1>

    <div class="prose prose-lg text-gray-600 space-y-6">
        <p>{{ __('Welcome to :app, a wishlist platform with a purpose.', ['app' => config('app.name')]) }}</p>

        <h2 class="text-xl font-semibold text-gray-900 mt-8">{{ __('Our Mission') }}</h2>
        <p>{{ __('We believe gift-giving should be joyful and meaningful.') }}</p>
        <p>{{ __('That\'s why we donate all affiliate commissions from purchases made through our platform to charity.') }}</p>

        <h2 class="text-xl font-semibold text-gray-900 mt-8">{{ __('How It Works') }}</h2>
        <p>{{ __(':app is a free wishlist platform that lets you create and share gift lists with friends and family. When someone buys a gift through our links, we donate the affiliate commission to charity.', ['app' => config('app.name')]) }}</p>

        <h2 class="text-xl font-semibold text-gray-900 mt-8">{{ __('Why We Built This') }}</h2>
        <p>We noticed that existing wishlist apps often feel impersonal or cluttered with ads. We wanted to create something simple, focused, and meaningful.</p>
        <p>By donating all affiliate revenue to charity, we turn every gift into an opportunity to help others.</p>

        <div class="mt-8 p-6 bg-green-50 rounded-lg border border-green-200">
            <p class="text-green-800 font-medium text-center">{{ __('All affiliate revenue goes to charity.') }}</p>
        </div>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">
            &larr; {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
