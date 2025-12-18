@extends('layouts.app')

@section('title', __('Invalid Link'))

@section('robots', 'noindex, nofollow')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">

        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center">

                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-coral-50 to-coral-100 border border-coral-200/50 flex items-center justify-center mb-5">
                    <svg class="w-9 h-9 text-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    {{ __('Invalid or Expired Link') }}
                </h1>
                <p class="text-gray-500 max-w-sm">
                    {{ __('This confirmation link is invalid or has already been used.') }}
                </p>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-5 bg-cream-50/50 border-t border-cream-100">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-cream-200 rounded-full flex items-center justify-center flex-shrink-0">
                    <x-icons.info-circle class="w-4 h-4 text-gray-500" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ __('What happened?') }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('The link may have expired or already been used. Please try claiming the gift again from the wishlist.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ url('/' . app()->getLocale()) }}" class="btn-primary inline-flex">
            <x-icons.home class="w-5 h-5" />
            {{ __('Go to Homepage') }}
        </a>
    </div>
</div>
@endsection
