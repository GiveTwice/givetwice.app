@extends('layouts.app')

@section('title', $list->name . ' - ' . $list->creator->name)

@section('robots', 'noindex, nofollow')

@php
    $availableGifts = (int) $gifts->filter(fn($gift) => $gift->claims->isEmpty() || $gift->allow_multiple_claims)->count();
    $claimedGifts = (int) max(0, $gifts->total() - $availableGifts);
    $listOwner = $list->creator;
    $ownerHasAvatar = $listOwner->hasProfileImage();
    $ownerAvatarUrl = $listOwner->getProfileImageUrl('medium');
@endphp

@section('content')

<div
    x-data="publicList({
        slug: '{{ $list->slug }}',
        locale: '{{ app()->getLocale() }}',
        availableCount: {{ $availableGifts }},
        claimedCount: {{ $claimedGifts }},
        translations: {
            untitledGift: '{{ __('Untitled gift') }}',
            claimed: '{{ __('Claimed') }}',
            alreadyClaimed: '{{ __('Already claimed') }}'
        }
    })"
>

<div class="mb-6 bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">

    <div class="p-5 sm:p-6">
        <div class="flex items-start gap-4 sm:gap-5">

            @if($ownerHasAvatar)
                <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden ring-2 ring-coral-200 ring-offset-2 ring-offset-white shadow-md">
                    <img
                        src="{{ $ownerAvatarUrl }}"
                        alt="{{ $listOwner->name }}"
                        class="w-full h-full object-cover"
                    >
                </div>
            @else
                <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-coral-50 to-sunny-50 border border-coral-100/50 flex items-center justify-center">
                    <span class="text-3xl sm:text-4xl">&#127873;</span>
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <p class="text-coral-500 text-xs sm:text-sm tracking-wide uppercase font-medium">
                    {{ __('Wishlist from') }}
                </p>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                    {{ $list->creator->name }}
                </h1>
                <p class="text-gray-500 text-sm truncate">{{ $list->name }}</p>
                @if($list->description)
                    <p class="text-gray-400 text-sm mt-0.5 line-clamp-1 hidden sm:block">{{ $list->description }}</p>
                @endif
            </div>

            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 rounded-full">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    <span class="text-sm font-bold text-white" x-text="availableCount">{{ $availableGifts }}</span>
                    <span class="text-xs sm:text-sm text-teal-100">{{ __('available') }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-cream-100 rounded-full" x-show="claimedCount > 0" x-cloak>
                    <span class="w-1.5 h-1.5 bg-sunny-500 rounded-full"></span>
                    <span class="text-xs font-semibold text-gray-600" x-text="claimedCount">{{ $claimedGifts }}</span>
                    <span class="text-xs text-gray-500">{{ __('claimed') }}</span>
                </div>
                @auth
                    @unless($isOwner)
                        <div x-data="followButton({ following: {{ $isFollowing ? 'true' : 'false' }}, slug: '{{ $list->slug }}', locale: '{{ app()->getLocale() }}' })">
                            <button
                                type="button"
                                x-on:click="toggle()"
                                :disabled="loading"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                                :class="following
                                    ? 'bg-coral-100 text-coral-700 hover:bg-coral-200'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            >
                                <x-icons.bell class="w-4 h-4" />
                                <span x-text="following ? '{{ __('Unfollow') }}' : '{{ __('Follow') }}'"></span>
                            </button>
                        </div>
                    @endunless
                @endauth
            </div>
        </div>
    </div>

    @unless($isOwner)
        <div class="px-5 sm:px-6 py-4 bg-cream-50/50 border-t border-cream-100">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">

                <div class="flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-1.5">
                        <span class="w-6 h-6 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center text-xs font-bold shadow-sm">1</span>
                        <span class="text-gray-600 font-medium">{{ __('Browse') }}</span>
                    </span>
                    <x-icons.chevron-right class="w-4 h-4 text-gray-300" />
                    <span class="flex items-center gap-1.5">
                        <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold shadow-sm">2</span>
                        <span class="text-gray-600 font-medium">{{ __('Claim') }}</span>
                    </span>
                    <x-icons.chevron-right class="w-4 h-4 text-gray-300" />
                    <span class="flex items-center gap-1.5">
                        <span class="w-6 h-6 bg-sunny-200 text-sunny-700 rounded-full flex items-center justify-center text-xs font-bold shadow-sm">3</span>
                        <span class="text-gray-600 font-medium">{{ __('Gift') }}</span>
                    </span>
                </div>

                <div class="hidden sm:block w-px h-4 bg-gray-200"></div>

                <p class="text-gray-400 text-sm">
                    {{ __(':name won\'t see who claimed what.', ['name' => $list->creator->name]) }}
                </p>
            </div>
        </div>
    @endunless

    @if($isOwner)
        <div class="px-5 sm:px-6 py-3 bg-sunny-50/80 border-t border-sunny-100">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-icons.eye class="w-4 h-4 text-sunny-600" />
                    <span class="text-sunny-800 text-sm font-medium">{{ __('Preview Mode') }}</span>
                    <span class="text-sunny-700 text-sm hidden sm:inline">— {{ __('This is how others will see your wishlist.') }}</span>
                </div>
                <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="text-sm font-medium text-sunny-700 hover:text-sunny-900 underline underline-offset-2">
                    {{ __('Back to dashboard') }}
                </a>
            </div>
        </div>
    @endif
</div>

<div class="bg-white rounded-2xl shadow-sm border border-cream-200/60 overflow-hidden">

    <div class="px-6 py-5 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">{{ __('Gift Ideas') }}</h2>
            @unless($isOwner)
                <p class="text-sm text-gray-500">{{ __('Click a gift for details') }}</p>
            @endunless
        </div>
    </div>

    <div class="p-6">
        @if($gifts->isEmpty())
            <div class="py-12 text-center" data-empty-state>
                <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">&#127873;</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No gifts yet') }}</h3>
                <p class="text-gray-500">{{ __(':name hasn\'t added any gifts to this list yet.', ['name' => $list->creator->name]) }}</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 hidden" data-gift-grid></div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" data-gift-grid>
                @foreach($gifts as $gift)
                    <x-gift-card :gift="$gift" :showClaimActions="true" :isOwner="$isOwner" :openModal="true" />
                @endforeach
            </div>

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

<div class="mt-16 relative overflow-hidden">

    <div class="absolute inset-0 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iNCIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

    <div class="relative px-6 sm:px-10 py-10 sm:py-12">
        <div class="max-w-3xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">

                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white/90 text-sm font-medium mb-4">
                        <span>&#10084;&#65039;</span>
                        {{ __('Gifting That Gives Back') }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ __('Every gift makes a difference') }}</h2>
                    <p class="text-coral-100 text-lg">{{ __('When you buy gifts through our links, we donate 100% of our affiliate profits to charity. No extra cost to you, just extra good in the world.') }}</p>
                </div>

                <div class="flex-shrink-0 text-center lg:text-left">
                    @guest
                        @if(config('app.allow_registration'))
                            <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <span>{{ __('Create Your Wishlist') }}</span>
                                <x-icons.arrow-right class="w-5 h-5" />
                            </a>
                            <p class="mt-3 text-coral-200 text-sm">{{ __('Free forever. Share with anyone.') }}</p>
                        @else
                            <a href="{{ url('/' . app()->getLocale() . '/contact') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <span>{{ __('Get in touch') }}</span>
                                <x-icons.arrow-right class="w-5 h-5" />
                            </a>
                            <p class="mt-3 text-coral-200 text-sm">{{ __('Launching soon') }}</p>
                        @endif
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <span>{{ __('Go to My Wishlists') }}</span>
                            <x-icons.arrow-right class="w-5 h-5" />
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-500">
    <div class="flex items-center gap-2">
        <x-icons.shield-check class="w-5 h-5 text-teal-500" />
        <span>{{ __('Privacy protected') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <x-icons.dollar-circle class="w-5 h-5 text-teal-500" />
        <span>{{ __('100% free to use') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <x-icons.heart class="w-5 h-5 text-coral-500" />
        <span>{{ __('Supports charity') }}</span>
    </div>
</div>

</div>
@endsection
