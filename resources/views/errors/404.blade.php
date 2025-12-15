@extends('layouts.app')

@section('title', __('Page Not Found'))

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">

    <div class="mb-8 relative">
        <svg class="w-40 h-40 md:w-52 md:h-52" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">

            <path
                d="M60 100C60 100 15 70 15 40C15 25 27 15 42 15C52 15 58 22 60 28C62 22 68 15 78 15C93 15 105 25 105 40C105 70 60 100 60 100Z"
                fill="url(#heartGradient)"
            />

            <path
                d="M42 20C32 20 22 28 22 42C22 48 25 55 30 62"
                stroke="url(#highlightGradient)"
                stroke-width="6"
                stroke-linecap="round"
                fill="none"
                opacity="0.4"
            />

            <path
                d="M60 28L55 45L65 55L55 70L60 100"
                stroke="#b91c1c"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
                fill="none"
                opacity="0.6"
            />

            <line
                x1="10"
                y1="75"
                x2="110"
                y2="35"
                stroke="#9ca3af"
                stroke-width="4"
                stroke-linecap="round"
            />

            <polygon
                points="110,35 95,30 100,45"
                fill="#9ca3af"
            />

            <polygon
                points="10,75 20,82 20,68"
                fill="#9ca3af"
            />

            <defs>
                <linearGradient id="heartGradient" x1="15" y1="15" x2="90" y2="100" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#ff6b6b" />
                    <stop offset="50%" stop-color="#f43f5e" />
                    <stop offset="100%" stop-color="#dc2626" />
                </linearGradient>
                <linearGradient id="highlightGradient" x1="22" y1="20" x2="30" y2="62" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="100%" stop-color="#fecaca" />
                </linearGradient>
            </defs>
        </svg>

        <div class="absolute -top-2 -right-2 w-4 h-4 bg-red-300 rounded-full opacity-60 animate-pulse"></div>
        <div class="absolute top-4 -left-4 w-3 h-3 bg-red-400 rounded-full opacity-40 animate-pulse" style="animation-delay: 0.5s"></div>
    </div>

    <p class="text-coral-400 font-semibold text-lg mb-2">404</p>

    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
        {{ __("Oops! This page got lost") }}
    </h1>

    <p class="text-gray-600 text-lg mb-8 max-w-md">
        {{ __("We looked everywhere, but couldn't find what you're looking for. Maybe the link is broken, or the page has moved.") }}
    </p>

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ url('/' . app()->getLocale()) }}" class="btn-secondary">
            <x-icons.arrow-left class="w-5 h-5" />
            {{ __('Back to Home') }}
        </a>

        @auth
            <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-primary">
                <x-icons.home class="w-5 h-5" />
                {{ __('Go to Dashboard') }}
            </a>
        @else
            <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="btn-primary">
                <x-icons.plus class="w-5 h-5" />
                {{ __('Create Your Wishlist') }}
            </a>
            <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="btn-secondary">
                {{ __('Sign In') }}
            </a>
        @endauth
    </div>

    <p class="mt-12 text-sm text-gray-400">
        {{ __("If you think this is a mistake, please") }}
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-500 hover:text-coral-600 underline">{{ __('contact us') }}</a>.
    </p>
</div>
@endsection
