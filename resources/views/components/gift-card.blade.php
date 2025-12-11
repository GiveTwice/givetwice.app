@props([
    'gift',
    'editable' => false,
    'showClaimActions' => false,
    'isOwner' => false,
    'openModal' => false
])

@php
    $isClaimed = $gift->claims_count > 0 || $gift->claims->isNotEmpty();
    $isClaimedByMe = auth()->check() && $gift->claims->where('user_id', auth()->id())->isNotEmpty();
    $isPending = $gift->isPending() || $gift->isFetching();
    $isFailed = $gift->isFetchFailed();
@endphp

<div
    class="group relative bg-white rounded-2xl border border-cream-200 overflow-hidden shadow-sm hover:shadow-lg hover:border-cream-300 transition-all duration-300 {{ $openModal ? 'cursor-pointer' : '' }}"
    @if($openModal)
        x-data
        x-on:click="$dispatch('open-gift-modal-{{ $gift->id }}')"
    @endif
>
    {{-- Image Container - Fixed 4:3 aspect ratio for consistent heights --}}
    <div class="relative aspect-[4/3] bg-cream-100 overflow-hidden">
        @if($gift->image_url)
            {{--
                Image handling for various aspect ratios:
                - object-cover: fills container, crops excess (best for products)
                - object-position: center ensures the important part is visible
                - The image scales smoothly on hover for visual feedback
            --}}
            <img
                src="{{ $gift->image_url }}"
                alt="{{ $gift->title }}"
                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            >
        @else
            {{-- Placeholder for missing images --}}
            <div class="w-full h-full flex flex-col items-center justify-center text-cream-400">
                @if($isPending)
                    <div class="relative">
                        <svg class="w-12 h-12 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-sunny-400 rounded-full animate-bounce"></div>
                    </div>
                    <span class="mt-2 text-sm font-medium">{{ __('Loading...') }}</span>
                @elseif($isFailed)
                    <svg class="w-12 h-12 text-coral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium text-coral-400">{{ __('Failed') }}</span>
                @else
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium">{{ __('No image') }}</span>
                @endif
            </div>
        @endif

        {{-- Status badge overlay --}}
        @if($isClaimed && !$showClaimActions)
            {{-- For owner view: show claimed status --}}
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('Claimed') }}
                </span>
            </div>
        @elseif($showClaimActions && $isClaimedByMe)
            {{-- For public view: "You're getting this" badge --}}
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-teal-100/95 backdrop-blur-sm text-teal-700 text-xs font-semibold rounded-full shadow-sm">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ __("You're getting this") }}
                </span>
            </div>
        @elseif($showClaimActions && $isClaimed && !$isOwner)
            {{-- For public view: Someone else claimed --}}
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm">
                    {{ __('Claimed') }}
                </span>
            </div>
        @elseif($isPending)
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Fetching') }}
                </span>
            </div>
        @endif

        {{-- Edit overlay for editable cards --}}
        @if($editable)
            <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') }}"
               class="absolute inset-0 bg-gray-900/0 group-hover:bg-gray-900/10 transition-colors duration-300 flex items-center justify-center">
                <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/95 backdrop-blur-sm text-gray-700 px-4 py-2 rounded-full text-sm font-medium shadow-lg">
                    {{ __('Edit') }}
                </span>
            </a>
        @endif
    </div>

    {{-- Card content --}}
    <div class="p-4">
        {{-- Title --}}
        <h3 class="font-semibold text-gray-900 leading-tight line-clamp-2 min-h-[2.5rem]" title="{{ $gift->title }}">
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        {{-- Price --}}
        <div class="mt-2 flex items-center justify-between">
            @if($gift->price)
                <span class="text-lg font-bold text-coral-600">
                    {{ $gift->currency ?? 'EUR' }} {{ number_format($gift->price, 2) }}
                </span>
            @else
                <span class="text-sm text-gray-400 italic">{{ __('No price') }}</span>
            @endif
        </div>

        {{-- Claim actions for public view --}}
        @if($showClaimActions && !$isOwner)
            <div class="mt-4 space-y-2" @if($openModal) x-on:click.stop @endif>
                {{-- View product link --}}
                @if($gift->url)
                    <a href="{{ $gift->url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="block w-full text-center text-sm bg-cream-100 text-gray-700 px-4 py-2.5 rounded-xl hover:bg-cream-200 transition-colors font-medium">
                        {{ __('View Product') }}
                    </a>
                @endif

                {{-- Claim/unclaim buttons --}}
                @if($isClaimedByMe)
                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-sm bg-coral-100 text-coral-700 px-4 py-2.5 rounded-xl hover:bg-coral-200 transition-colors font-medium">
                            {{ __('Unclaim') }}
                        </button>
                    </form>
                @elseif($isClaimed)
                    <button type="button" disabled
                        class="w-full text-sm bg-cream-200 text-cream-500 px-4 py-2.5 rounded-xl cursor-not-allowed">
                        {{ __('Already claimed') }}
                    </button>
                @else
                    @auth
                        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-sm bg-teal-500 text-white px-4 py-2.5 rounded-xl hover:bg-teal-600 transition-colors font-medium shadow-sm hover:shadow">
                                {{ __("I'll get this!") }}
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                           class="block w-full text-center text-sm bg-teal-500 text-white px-4 py-2.5 rounded-xl hover:bg-teal-600 transition-colors font-medium shadow-sm hover:shadow">
                            {{ __("I'll get this!") }}
                        </a>
                    @endauth
                @endif
            </div>
        @endif
    </div>
</div>
