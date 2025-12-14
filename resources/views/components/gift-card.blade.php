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
    // Determine if this is a "claimed by others" state on public page (not by me)
    $isClaimedByOthers = $showClaimActions && $isClaimed && !$isClaimedByMe && !$isOwner;
@endphp

<div
    class="group relative bg-white rounded-2xl overflow-hidden border shadow-sm transition-all duration-300
        {{ $isClaimedByOthers
            ? 'border-sunny-200/80 bg-sunny-50/30'
            : 'border-gray-200 hover:shadow-lg hover:border-gray-300' }}
        {{ $openModal && !$isClaimedByOthers ? 'cursor-pointer' : '' }}
        {{ $isClaimedByOthers ? 'cursor-default' : '' }}"
    data-gift-id="{{ $gift->id }}"
    @if($openModal && !$isClaimedByOthers)
        x-data
        x-on:click="$dispatch('open-gift-modal-{{ $gift->id }}')"
    @endif
>
    {{-- Image Container - Square aspect ratio for product images --}}
    <div class="relative aspect-square bg-cream-100 overflow-hidden" data-gift-image>
        @if($gift->hasImage())
            {{--
                Image handling for various aspect ratios:
                - object-cover: fills container, crops excess (best for products)
                - object-position: center ensures the important part is visible
                - The image scales smoothly on hover for visual feedback
                - Claimed items get a warm desaturation effect
                - Uses 'card' conversion (600x600) for optimal grid display
            --}}
            <img
                src="{{ $gift->getImageUrl('card') }}"
                alt="{{ $gift->title }}"
                class="w-full h-full object-cover object-center transition-all duration-500
                    {{ $isClaimedByOthers
                        ? 'grayscale-[0.5] brightness-[1.05] sepia-[0.2] saturate-[0.7]'
                        : 'group-hover:scale-105' }}"
                loading="lazy"
            >
            {{-- Warm gradient overlay for claimed items --}}
            @if($isClaimedByOthers)
                <div class="absolute inset-0 bg-gradient-to-t from-sunny-200/50 via-sunny-100/25 to-sunny-50/10 pointer-events-none"></div>
            @endif
        @else
            {{-- Placeholder for missing images --}}
            <div class="w-full h-full flex flex-col items-center justify-center text-cream-400" data-gift-placeholder>
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
            {{-- For public view: Someone else claimed - elegant fulfilled indicator --}}
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1.5 pl-2 pr-2.5 py-1 bg-white/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm border border-sunny-200/50">
                    <span class="w-4 h-4 bg-sunny-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    {{ __('Claimed') }}
                </span>
            </div>
        @elseif($isPending)
            <div class="absolute top-3 right-3" data-gift-badge>
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

    {{-- Card content - compact --}}
    <div class="px-3 py-2.5 {{ $isClaimedByOthers ? 'bg-sunny-50/40' : '' }}">
        {{-- Title --}}
        <h3 class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.25rem] {{ $isClaimedByOthers ? 'text-gray-500' : 'text-gray-900' }}" title="{{ $gift->title }}" data-gift-title>
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        {{-- Price --}}
        <div class="mt-1.5 flex items-center justify-between" data-gift-price>
            @if($gift->hasPrice())
                <span class="text-base font-bold {{ $isClaimedByOthers ? 'text-gray-400' : 'text-coral-600' }}">
                    {{ $gift->formatPrice() }}
                </span>
            @else
                <span class="text-xs text-gray-400 italic">{{ __('No price') }}</span>
            @endif
        </div>

        {{-- Claim actions for public view --}}
        @if($showClaimActions && !$isOwner)
            <div class="mt-2.5 space-y-1.5" @if($openModal) x-on:click.stop @endif>
                {{-- View product link --}}
                @if($gift->url)
                    <a href="{{ $gift->url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="block w-full text-center text-xs px-3 py-2 rounded-lg transition-colors font-medium
                           {{ $isClaimedByOthers
                               ? 'bg-sunny-50/60 text-sunny-600/80 hover:bg-sunny-100/60 border border-sunny-200/40'
                               : 'bg-cream-100 text-gray-700 hover:bg-cream-200' }}">
                        {{ __('View Product') }}
                    </a>
                @endif

                {{-- Claim/unclaim buttons --}}
                @if($isClaimedByMe)
                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-xs bg-coral-100 text-coral-700 px-3 py-2 rounded-lg hover:bg-coral-200 transition-colors font-medium">
                            {{ __('Unclaim') }}
                        </button>
                    </form>
                @elseif($isClaimed)
                    <button type="button" disabled
                        class="w-full text-xs bg-sunny-100/80 text-sunny-600/70 px-3 py-2 rounded-lg cursor-not-allowed border border-sunny-200/50 flex items-center justify-center gap-1.5">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('Already claimed') }}
                    </button>
                @else
                    @auth
                        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-xs bg-teal-500 text-white px-3 py-2 rounded-lg hover:bg-teal-600 transition-colors font-medium shadow-sm hover:shadow">
                                {{ __("I'll get this!") }}
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                           class="block w-full text-center text-xs bg-teal-500 text-white px-3 py-2 rounded-lg hover:bg-teal-600 transition-colors font-medium shadow-sm hover:shadow">
                            {{ __("I'll get this!") }}
                        </a>
                    @endauth
                @endif
            </div>
        @endif
    </div>
</div>
