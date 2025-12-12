@extends('layouts.app')

@section('title', __('Claim Confirmed'))

@php
    $siteName = '';
    if ($gift->url) {
        $parsedUrl = parse_url($gift->url);
        $host = $parsedUrl['host'] ?? '';
        $siteName = preg_replace('/^www\./', '', $host);
    }
@endphp

@section('content')
{{-- Success Hero Card --}}
<div class="mb-6 bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">
    {{-- Main content area --}}
    <div class="p-5 sm:p-6">
        <div class="flex items-start gap-4 sm:gap-5">
            {{-- Success icon --}}
            <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200/50 flex items-center justify-center">
                <svg class="w-8 h-8 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            {{-- Success message --}}
            <div class="flex-1 min-w-0">
                <p class="text-teal-500 text-xs sm:text-sm tracking-wide uppercase font-medium">
                    {{ __('Step 3 of 3') }}
                </p>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5">
                    {{ __('Claim Confirmed!') }}
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ __('The list owner will see that someone is getting this gift, but not who.') }}
                </p>
            </div>

            {{-- Completion badge --}}
            <div class="flex-shrink-0 hidden sm:block">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 rounded-full">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium text-white">{{ __('Done') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Completed steps footer --}}
    <div class="px-5 sm:px-6 py-3 bg-cream-50/50 border-t border-cream-100">
        <div class="flex items-center gap-3 text-sm">
            <span class="flex items-center gap-1.5">
                <span class="w-6 h-6 bg-teal-100 text-teal-500 rounded-full flex items-center justify-center text-xs font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </span>
                <span class="text-gray-400 font-medium">{{ __('Browse') }}</span>
            </span>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="flex items-center gap-1.5">
                <span class="w-6 h-6 bg-teal-100 text-teal-500 rounded-full flex items-center justify-center text-xs font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </span>
                <span class="text-gray-400 font-medium">{{ __('Claim') }}</span>
            </span>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="flex items-center gap-1.5">
                <span class="w-6 h-6 bg-sunny-400 text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">3</span>
                <span class="text-gray-900 font-semibold">{{ __('Gift') }}</span>
            </span>
        </div>
    </div>
</div>

{{-- Gift Details Card --}}
<div class="bg-white rounded-2xl shadow-sm border border-cream-200/60 overflow-hidden">
    {{-- Section header --}}
    <div class="px-6 py-5 border-b border-gray-100">
        <h2 class="text-xl font-bold text-gray-900">{{ __('You\'re getting this gift') }}</h2>
    </div>

    {{-- Gift preview --}}
    <div class="p-6">
        <div class="flex gap-5 items-start">
            {{-- Gift image --}}
            <div class="flex-shrink-0 w-24 h-24 sm:w-32 sm:h-32 rounded-xl bg-cream-50 border border-cream-200 overflow-hidden">
                @if($gift->image_url)
                    <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-cream-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Gift info --}}
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-900 line-clamp-2">{{ $gift->title ?: __('Untitled gift') }}</h3>
                @if($gift->hasPrice())
                    <p class="text-xl font-bold text-coral-600 mt-1">{{ $gift->formatPrice() }}</p>
                @endif
                @if($gift->description)
                    <p class="text-gray-500 text-sm mt-2 line-clamp-2">{{ $gift->description }}</p>
                @endif
                @if($gift->url)
                    <a
                        href="{{ $gift->url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium text-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        @if($siteName)
                            {{ __('Buy on :site', ['site' => $siteName]) }}
                        @else
                            {{ __('Buy this gift') }}
                        @endif
                    </a>
                @endif
            </div>
        </div>

        {{-- Reminder note --}}
        <div class="mt-6 flex items-start gap-3 p-4 bg-sunny-50 rounded-xl border border-sunny-200">
            <div class="w-8 h-8 bg-sunny-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-sunny-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-sunny-800">{{ __('Don\'t forget to buy the gift!') }}</p>
                <p class="text-sm text-sunny-700 mt-0.5">{{ __('The list owner is counting on you to follow through.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- CTA Section --}}
<div class="mt-10 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iNCIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

    <div class="relative px-6 sm:px-10 py-10 sm:py-12">
        <div class="max-w-2xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white/90 text-sm font-medium mb-4">
                <span>&#127873;</span>
                {{ __('Create Your Own') }}
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ __('Want your own wishlist?') }}</h2>
            <p class="text-coral-100 text-lg mb-6">{{ __('Create a free wishlist and share it with friends and family. All affiliate revenue goes to charity!') }}</p>

            @guest
                <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <span>{{ __('Create Your Wishlist') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <p class="mt-3 text-coral-200 text-sm">{{ __('Free forever. Share with anyone.') }}</p>
            @else
                <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <span>{{ __('Go to My Wishlists') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @endguest
        </div>
    </div>
</div>
@endsection
