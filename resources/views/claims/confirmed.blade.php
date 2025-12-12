@extends('layouts.app')

@section('title', __('Claim Confirmed'))

@php
    // Extract domain name from URL (without www prefix)
    $siteName = '';
    if ($gift->url) {
        $parsedUrl = parse_url($gift->url);
        $host = $parsedUrl['host'] ?? '';
        $siteName = preg_replace('/^www\./', '', $host);
    }
@endphp

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
            <div class="flex gap-4 items-start bg-cream-50 rounded-xl p-4 border border-cream-200">
                {{-- Gift Image --}}
                <div class="w-24 h-24 flex-shrink-0 bg-white rounded-lg overflow-hidden border border-cream-200">
                    @if($gift->image_url)
                        <img
                            src="{{ $gift->image_url }}"
                            alt="{{ $gift->title }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-cream-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Gift Info --}}
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-gray-900 mb-1">{{ $gift->title ?: __('Untitled gift') }}</h2>
                    @if($gift->hasPrice())
                        <p class="text-lg font-bold text-coral-600 mb-2">
                            {{ $gift->formatPrice() }}
                        </p>
                    @endif
                    @if($gift->url && $siteName)
                        <a
                            href="{{ $gift->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-sm text-teal-600 hover:text-teal-700 font-medium"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('View on :site', ['site' => $siteName]) }}
                        </a>
                    @endif
                </div>
            </div>
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
