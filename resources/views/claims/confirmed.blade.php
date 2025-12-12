@extends('layouts.app')

@section('title', __('Claim Confirmed'))

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Success Card --}}
    <div class="bg-white rounded-2xl border border-cream-200 overflow-hidden">
        {{-- Success Header --}}
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 p-8 text-center border-b border-teal-200">
            <div class="w-20 h-20 bg-teal-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-teal-800 mb-2">{{ __('Claim Confirmed!') }}</h1>
            <p class="text-teal-700 max-w-md mx-auto">
                {{ __('You have successfully claimed this gift. The list owner will see that someone is getting it, but not who.') }}
            </p>
        </div>

        {{-- Gift Preview --}}
        <div class="p-6">
            <x-gift-preview :gift="$gift" variant="compact" />
        </div>

        {{-- Next Steps --}}
        <div class="px-6 pb-6">
            <div class="bg-sunny-50 border border-sunny-200 rounded-xl p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-sunny-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm">
                        <p class="font-medium text-sunny-800">{{ __('What\'s next?') }}</p>
                        <p class="text-sunny-700">{{ __('Don\'t forget to actually buy the gift! The list owner is counting on you.') }}</p>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="text-center">
                <p class="text-gray-600 mb-4">{{ __('Want to create your own wishlist?') }}</p>
                <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="btn-primary">
                    <span>&#127873;</span>
                    {{ __('Create your own wishlist') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
