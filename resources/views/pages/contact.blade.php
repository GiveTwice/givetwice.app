@extends('layouts.app')

@section('title', __('Contact'))

@section('content')
{{-- Hero Section --}}
<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-coral-100 text-coral-500 rounded-2xl text-3xl mb-6 transform -rotate-3">
        &#9993;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('Contact Us') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('Have questions or feedback? We\'d love to hear from you!') }}</p>
</div>

{{-- Main Content --}}
<div class="max-w-4xl mx-auto pb-16">
    {{-- Contact Card --}}
    <div class="bg-gradient-to-br from-coral-50 to-sunny-50 rounded-3xl p-8 lg:p-12 mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="flex-shrink-0">
                <div class="w-24 h-24 bg-white rounded-2xl shadow-sm flex items-center justify-center text-5xl transform rotate-3">
                    &#128140;
                </div>
            </div>
            <div class="text-center md:text-left">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Get in Touch') }}</h2>
                <p class="text-lg text-gray-700 leading-relaxed mb-4">{{ __('Whether you have a question about features, need help with your wishlist, or just want to say hello - we\'re here for you.') }}</p>
                <a href="mailto:hello@giftwithlove.com" class="inline-flex items-center text-xl font-semibold text-coral-600 hover:text-coral-700 transition-colors">
                    <span class="mr-2">&#9993;</span> hello@giftwithlove.com
                </a>
            </div>
        </div>
    </div>

    {{-- Response Time --}}
    <div class="bg-white border border-cream-200 rounded-2xl p-6 mb-12 text-center shadow-sm">
        <div class="flex items-center justify-center gap-3">
            <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-xl">
                &#9201;
            </div>
            <p class="text-gray-700">{{ __('We typically respond within 24-48 hours.') }}</p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('Quick Links') }}</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="block bg-white border border-cream-200 rounded-2xl p-6 hover:border-coral-300 hover:shadow-md transition-all group">
                <div class="w-12 h-12 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center text-2xl mb-4 group-hover:transform group-hover:rotate-3 transition-transform">
                    &#10067;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('FAQ') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Find answers to common questions') }}</p>
            </a>

            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="block bg-white border border-cream-200 rounded-2xl p-6 hover:border-coral-300 hover:shadow-md transition-all group">
                <div class="w-12 h-12 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center text-2xl mb-4 group-hover:transform group-hover:-rotate-3 transition-transform">
                    &#10084;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('About Us') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Learn more about our mission') }}</p>
            </a>

            <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="block bg-white border border-cream-200 rounded-2xl p-6 hover:border-coral-300 hover:shadow-md transition-all group">
                <div class="w-12 h-12 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-2xl mb-4 group-hover:transform group-hover:rotate-3 transition-transform">
                    &#128274;
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Privacy Policy') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('How we handle your data') }}</p>
            </a>
        </div>
    </div>

    {{-- Social/Community Section --}}
    <div class="bg-cream-100 rounded-3xl p-8 text-center mb-12">
        <div class="text-4xl mb-4">&#127873;</div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Join Our Community') }}</h2>
        <p class="text-gray-600 mb-6 max-w-xl mx-auto">{{ __('Follow us for updates, gift-giving tips, and to see how your wishlists are making a difference for charity.') }}</p>
        <div class="flex justify-center gap-4">
            <a href="#" class="w-12 h-12 bg-white border border-cream-200 rounded-xl flex items-center justify-center text-gray-600 hover:text-coral-500 hover:border-coral-300 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
            </a>
            <a href="#" class="w-12 h-12 bg-white border border-cream-200 rounded-xl flex items-center justify-center text-gray-600 hover:text-coral-500 hover:border-coral-300 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="#" class="w-12 h-12 bg-white border border-cream-200 rounded-xl flex items-center justify-center text-gray-600 hover:text-coral-500 hover:border-coral-300 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
            </a>
        </div>
    </div>

    {{-- Back to home --}}
    <div class="text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
