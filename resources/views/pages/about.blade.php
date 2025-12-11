@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
{{-- Hero Section --}}
<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-coral-100 text-coral-500 rounded-2xl text-3xl mb-6 transform rotate-3">
        &#10084;&#65039;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('About Us') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('Welcome to :app, a wishlist platform with a purpose.', ['app' => config('app.name')]) }}</p>
</div>

{{-- Main Content --}}
<div class="max-w-4xl mx-auto pb-16">
    {{-- Our Mission Section --}}
    <div class="bg-gradient-to-br from-coral-50 to-sunny-50 rounded-3xl p-8 lg:p-12 mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-white rounded-2xl shadow-sm flex items-center justify-center text-5xl transform -rotate-3">
                    &#127873;
                </div>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Our Mission') }}</h2>
                <p class="text-lg text-gray-700 leading-relaxed mb-4">{{ __('We believe gift-giving should be joyful and meaningful.') }}</p>
                <p class="text-lg text-gray-700 leading-relaxed">{{ __('That\'s why we donate all affiliate commissions from purchases made through our platform to charity.') }}</p>
            </div>
        </div>
    </div>

    {{-- How It Works Section --}}
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('How It Works') }}</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white border border-cream-200 rounded-2xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4 transform rotate-2">
                    &#128221;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Create') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Add gifts from any online store by pasting a product URL. We\'ll fetch the details automatically.') }}</p>
            </div>
            <div class="bg-white border border-cream-200 rounded-2xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4 transform -rotate-2">
                    &#128140;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Share') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Send your wishlist link to friends and family via email, chat, or social media.') }}</p>
            </div>
            <div class="bg-white border border-cream-200 rounded-2xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4 transform rotate-1">
                    &#10003;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Receive') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Others secretly claim gifts so you don\'t get duplicates. Everyone\'s happy!') }}</p>
            </div>
        </div>
    </div>

    {{-- Why We Built This Section --}}
    <div class="bg-cream-100 rounded-3xl p-8 lg:p-12 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Why We Built This') }}</h2>
        <div class="space-y-4 text-gray-700 leading-relaxed">
            <p>{{ __('We noticed that existing wishlist apps often feel impersonal or cluttered with ads. We wanted to create something simple, focused, and meaningful.') }}</p>
            <p>{{ __('By donating all affiliate revenue to charity, we turn every gift into an opportunity to help others. When you create a wishlist on :app, you\'re not just helping your friends find the perfect gift - you\'re also contributing to a better world.', ['app' => config('app.name')]) }}</p>
        </div>
    </div>

    {{-- Our Values --}}
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('Our Values') }}</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="flex items-start gap-4 bg-white border border-cream-200 rounded-2xl p-6 shadow-sm">
                <div class="flex-shrink-0 w-12 h-12 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center text-xl">
                    &#10084;
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ __('Generosity') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Every purchase through our platform contributes to charity. Giving feels good!') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4 bg-white border border-cream-200 rounded-2xl p-6 shadow-sm">
                <div class="flex-shrink-0 w-12 h-12 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-xl">
                    &#128274;
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ __('Privacy') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Claims are anonymous - the surprise stays a surprise!') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4 bg-white border border-cream-200 rounded-2xl p-6 shadow-sm">
                <div class="flex-shrink-0 w-12 h-12 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center text-xl">
                    &#9734;
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ __('Simplicity') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('No clutter, no distractions. Just beautiful, easy-to-use wishlists.') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4 bg-white border border-cream-200 rounded-2xl p-6 shadow-sm">
                <div class="flex-shrink-0 w-12 h-12 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center text-xl">
                    &#127760;
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ __('Accessibility') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Free for everyone, always. Add items from any online store worldwide.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charity Highlight --}}
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-8 text-center border border-coral-100 mb-12">
        <div class="text-4xl mb-4">&#10084;&#65039;</div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Gifting That Gives Back') }}</h2>
        <p class="text-gray-600 mb-4 max-w-xl mx-auto">{{ __('We believe gift-giving should spread joy everywhere. That\'s why we donate 100% of our affiliate commissions to charity.') }}</p>
        <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-coral-200">
            <span class="text-coral-500 mr-2">&#10084;</span>
            <span class="text-gray-800 font-medium">{{ __('All affiliate revenue goes to charity') }}</span>
        </div>
    </div>

    {{-- CTA Section --}}
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Ready to get started?') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Create your first wishlist in minutes. It\'s free!') }}</p>
        @guest
            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                {{ __('Create Your Wishlist') }} <span class="ml-2">&#127873;</span>
            </a>
        @else
            <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
            </a>
        @endguest
    </div>

    {{-- Back to home --}}
    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
