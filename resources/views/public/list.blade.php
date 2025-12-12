@extends('layouts.app')

@section('title', $list->name . ' - ' . $list->user->name)

@php
    $isOwner = auth()->check() && auth()->id() === $list->user_id;
    $availableGifts = $gifts->filter(fn($gift) => $gift->claims->isEmpty())->count();
    $claimedGifts = $gifts->total() - $availableGifts;
@endphp

@section('content')
{{-- Unified Hero Card --}}
<div class="mb-6 bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">
    {{-- Main content area --}}
    <div class="p-5 sm:p-6">
        <div class="flex items-start gap-4 sm:gap-5">
            {{-- Gift emoji --}}
            <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-coral-50 to-sunny-50 border border-coral-100/50 flex items-center justify-center">
                <span class="text-2xl sm:text-3xl">&#127873;</span>
            </div>

            {{-- List info --}}
            <div class="flex-1 min-w-0">
                <p class="text-coral-500 text-xs sm:text-sm tracking-wide uppercase font-medium">
                    {{ __('Wishlist from') }}
                </p>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                    {{ $list->user->name }}
                </h1>
                <p class="text-gray-500 text-sm truncate">{{ $list->name }}</p>
                @if($list->description)
                    <p class="text-gray-400 text-sm mt-0.5 line-clamp-1 hidden sm:block">{{ $list->description }}</p>
                @endif
            </div>

            {{-- Stats --}}
            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 rounded-full">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    <span class="text-sm font-bold text-white">{{ $availableGifts }}</span>
                    <span class="text-xs sm:text-sm text-teal-100">{{ __('available') }}</span>
                </div>
                @if($claimedGifts > 0)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-cream-100 rounded-full">
                        <span class="w-1.5 h-1.5 bg-sunny-500 rounded-full"></span>
                        <span class="text-xs font-semibold text-gray-600">{{ $claimedGifts }}</span>
                        <span class="text-xs text-gray-500">{{ __('claimed') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Integrated explainer footer - only for non-owners --}}
    @unless($isOwner)
        <div class="px-5 sm:px-6 py-3 bg-cream-50/50 border-t border-cream-100">
            <div class="flex items-center justify-between gap-4">
                <p class="text-gray-500 text-sm truncate">
                    {{ __('Claim gifts to avoid duplicates — :name won\'t see who.', ['name' => $list->user->name]) }}
                </p>
                <div class="flex items-center gap-2 text-sm flex-shrink-0">
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <span class="text-gray-500 hidden sm:inline">{{ __('Browse') }}</span>
                    </span>
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <span class="text-gray-500 hidden sm:inline">{{ __('Claim') }}</span>
                    </span>
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 bg-sunny-200 text-sunny-700 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <span class="text-gray-500 hidden sm:inline">{{ __('Gift') }}</span>
                    </span>
                </div>
            </div>
        </div>
    @endunless

    {{-- Owner preview notice --}}
    @if($isOwner)
        <div class="px-5 sm:px-6 py-3 bg-sunny-50/80 border-t border-sunny-100">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-sunny-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="text-sunny-800 text-sm font-medium">{{ __('Preview Mode') }}</span>
                    <span class="text-sunny-700 text-sm hidden sm:inline">— {{ __('This is how others will see your wishlist.') }}</span>
                </div>
                <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="text-sm font-medium text-sunny-700 hover:text-sunny-900 underline underline-offset-2">
                    {{ __('Edit list') }}
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Gifts Section --}}
<div class="bg-white rounded-2xl shadow-sm border border-cream-200/60 overflow-hidden">
    {{-- Section header --}}
    <div class="px-6 py-5 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">{{ __('Gift Ideas') }}</h2>
            @unless($isOwner)
                <p class="text-sm text-gray-500">{{ __('Click a gift for details') }}</p>
            @endunless
        </div>
    </div>

    {{-- Gifts Grid --}}
    <div class="p-6">
        @if($gifts->isEmpty())
            <div class="py-12 text-center">
                <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">&#127873;</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No gifts yet') }}</h3>
                <p class="text-gray-500">{{ __(':name hasn\'t added any gifts to this list yet.', ['name' => $list->user->name]) }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($gifts as $gift)
                    <x-gift-card :gift="$gift" :showClaimActions="true" :isOwner="$isOwner" :openModal="true" />
                @endforeach
            </div>

            {{-- Gift detail modals --}}
            @foreach($gifts as $gift)
                <x-gift-modal :gift="$gift" :isOwner="$isOwner" />
            @endforeach

            @if($gifts->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-100">
                    {{ $gifts->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Charity Mission & CTA Section --}}
<div class="mt-10 relative overflow-hidden">
    {{-- Background with layered effect --}}
    <div class="absolute inset-0 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iNCIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

    <div class="relative px-6 sm:px-10 py-10 sm:py-12">
        <div class="max-w-3xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                {{-- Mission content --}}
                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white/90 text-sm font-medium mb-4">
                        <span>&#10084;&#65039;</span>
                        {{ __('Gifting That Gives Back') }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ __('Every gift makes a difference') }}</h2>
                    <p class="text-coral-100 text-lg">{{ __('When you buy gifts through our links, we donate the affiliate commission to charity. No extra cost to you, just extra good in the world.') }}</p>
                </div>

                {{-- CTA --}}
                <div class="flex-shrink-0 text-center lg:text-left">
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
    </div>
</div>

{{-- Trust indicators --}}
<div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-500">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span>{{ __('Privacy protected') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ __('100% free to use') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-coral-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        <span>{{ __('Supports charity') }}</span>
    </div>
</div>
@endsection
