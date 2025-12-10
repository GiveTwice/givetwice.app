@extends('layouts.app')

@section('title', __('Contact'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Contact Us') }}</h1>

    <div class="bg-white border rounded-lg p-8">
        <p class="text-lg text-gray-600 mb-6">{{ __('Have questions or feedback? We\'d love to hear from you!') }}</p>

        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Email us at:') }}</h3>
                <a href="mailto:hello@example.com" class="text-green-600 hover:text-green-700 text-lg">
                    hello@example.com
                </a>
            </div>

            <p class="text-gray-500">{{ __('We typically respond within 24-48 hours.') }}</p>
        </div>
    </div>

    <div class="mt-8 grid md:grid-cols-3 gap-6">
        <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block bg-gray-50 border rounded-lg p-6 hover:border-green-300 transition-colors">
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('FAQ') }}</h3>
            <p class="text-gray-600 text-sm">Find answers to common questions</p>
        </a>

        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block bg-gray-50 border rounded-lg p-6 hover:border-green-300 transition-colors">
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('About Us') }}</h3>
            <p class="text-gray-600 text-sm">Learn more about our mission</p>
        </a>

        <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="block bg-gray-50 border rounded-lg p-6 hover:border-green-300 transition-colors">
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Privacy Policy') }}</h3>
            <p class="text-gray-600 text-sm">How we handle your data</p>
        </a>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">
            &larr; {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
