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
    $isClaimedByOthers = $showClaimActions && $isClaimed && !$isClaimedByMe && !$isOwner;
    $isUnavailable = $isClaimedByOthers || ($showClaimActions && $isClaimedByMe);
@endphp

<div
    class="gift-card group
        {{ $isClaimedByOthers ? 'gift-card-claimed-others' : '' }}
        {{ $showClaimActions && $isClaimedByMe ? 'gift-card-claimed-mine' : '' }}
        {{ !$isUnavailable ? 'gift-card-default' : '' }}
        {{ ($openModal && !$isUnavailable) || $isClaimedByMe ? 'cursor-pointer' : '' }}"
    data-gift-id="{{ $gift->id }}"
    @if($openModal && !$isUnavailable)
        x-data
        x-on:click="$dispatch('open-gift-modal-{{ $gift->id }}')"
    @elseif($isClaimedByMe)
        x-data
        x-on:click="window.location.href = '{{ route('claim.confirmed', ['locale' => app()->getLocale(), 'gift' => $gift]) }}'"
    @endif
>

    <div class="relative aspect-square bg-cream-100 overflow-hidden" data-gift-image>
        @if($gift->hasImage())
            <img
                src="{{ $gift->getImageUrl('card') }}"
                alt="{{ $gift->title }}"
                class="w-full h-full object-cover object-center transition-all duration-500
                    {{ $isUnavailable ? 'gift-card-image-unavailable' : 'group-hover:scale-105' }}"
                loading="lazy"
            >

            @if($isClaimedByOthers)
                <div class="gift-card-overlay-sunny"></div>
            @elseif($showClaimActions && $isClaimedByMe)
                <div class="gift-card-overlay-teal"></div>
            @endif
        @else

            <div class="w-full h-full flex flex-col items-center justify-center text-cream-400" data-gift-placeholder>
                @if($isPending)
                    <div class="relative">
                        <x-icons.image-placeholder class="w-12 h-12 animate-pulse" />
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-sunny-400 rounded-full animate-bounce"></div>
                    </div>
                    <span class="mt-2 text-sm font-medium">{{ __('Loading...') }}</span>
                @elseif($isFailed)
                    <x-icons.warning class="w-12 h-12 text-coral-300" />
                    <span class="mt-2 text-sm font-medium text-coral-400">{{ __('Failed') }}</span>
                @else
                    <x-icons.image-placeholder class="w-12 h-12" />
                    <span class="mt-2 text-sm font-medium">{{ __('No image') }}</span>
                @endif
            </div>
        @endif

        @if($isClaimed && !$showClaimActions)

            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm">
                    <x-icons.check-circle-filled class="w-3 h-3" />
                    {{ __('Claimed') }}
                </span>
            </div>
        @elseif($showClaimActions && $isClaimedByMe)

            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-teal-100/95 backdrop-blur-sm text-teal-700 text-xs font-semibold rounded-full shadow-sm">
                    <x-icons.checkmark class="w-3 h-3" />
                    {{ __("You're getting this") }}
                </span>
            </div>
        @elseif($showClaimActions && $isClaimed && !$isOwner)

            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1.5 pl-2 pr-2.5 py-1 bg-white/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm border border-sunny-200/50">
                    <span class="w-4 h-4 bg-sunny-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <x-icons.checkmark class="w-2.5 h-2.5 text-white" stroke-width="3" />
                    </span>
                    {{ __('Claimed') }}
                </span>
            </div>
        @elseif($isPending)
            <div class="absolute top-3 right-3" data-gift-badge>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm">
                    <x-icons.spinner class="w-3 h-3 animate-spin" />
                    {{ __('Fetching') }}
                </span>
            </div>
        @endif

        @if($editable)
            <div class="gift-card-hover-overlay gift-card-hover-overlay-gray flex-col gap-2">
                <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') }}"
                   class="gift-card-hover-label text-gray-700">
                    {{ __('Edit') }}
                </a>
                @if($gift->url)
                    <a href="{{ $gift->url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="gift-card-hover-label text-gray-700">
                        {{ __('View gift') }}
                    </a>
                @endif
            </div>
        @elseif($isClaimedByMe)
            <a href="{{ route('claim.confirmed', ['locale' => app()->getLocale(), 'gift' => $gift]) }}"
               class="gift-card-hover-overlay gift-card-hover-overlay-teal">
                <span class="gift-card-hover-label text-teal-700">
                    {{ __("You're getting this") }}
                </span>
            </a>
        @endif
    </div>

    <div class="px-3 py-2.5 {{ $isClaimedByOthers ? 'gift-card-content-sunny' : '' }} {{ $showClaimActions && $isClaimedByMe ? 'gift-card-content-teal' : '' }}">

        <h3 class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.25rem] {{ $isUnavailable ? 'gift-card-title-muted' : 'text-gray-900' }}" title="{{ $gift->title }}" data-gift-title>
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        <div class="mt-1.5 flex items-center justify-between" data-gift-price>
            @if($gift->hasPrice())
                <span class="text-base font-bold {{ $isUnavailable ? 'gift-card-price-muted' : 'text-coral-600' }}">
                    {{ $gift->formatPrice() }}
                </span>
            @else
                <span class="text-xs text-gray-400 italic">{{ __('No price') }}</span>
            @endif
        </div>

        @if($showClaimActions && !$isOwner)
            <div class="mt-2.5 space-y-1.5" @if($openModal || $isClaimedByMe) x-on:click.stop @endif>

                @if($isClaimedByMe)
                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-xs bg-sunny-200 text-sunny-800 px-3 py-2 rounded-lg hover:bg-sunny-300 transition-colors font-medium flex items-center justify-center gap-1.5">
                            <x-icons.checkmark class="w-3 h-3" />
                            {{ __('Unclaim') }}
                        </button>
                    </form>
                @elseif($isClaimed)
                    <button type="button" disabled
                        class="w-full text-xs bg-sunny-100/80 text-sunny-600/70 px-3 py-2 rounded-lg cursor-not-allowed border border-sunny-200/50 flex items-center justify-center gap-1.5">
                        <x-icons.check-circle-filled class="w-3 h-3" />
                        {{ __('Already claimed') }}
                    </button>
                @else
                    @auth
                        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-xs bg-sunny-200 text-sunny-800 px-3 py-2 rounded-lg hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow flex items-center justify-center gap-1.5">
                                <x-icons.checkmark class="w-3 h-3" />
                                {{ __('Claim gift') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                           class="w-full text-center text-xs bg-sunny-200 text-sunny-800 px-3 py-2 rounded-lg hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow flex items-center justify-center gap-1.5">
                            <x-icons.checkmark class="w-3 h-3" />
                            {{ __('Claim gift') }}
                        </a>
                    @endauth
                @endif

                @if($gift->siteName())
                    <a href="{{ $gift->url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-full text-center text-xs px-3 py-2 rounded-lg transition-colors font-medium flex items-center justify-center gap-1.5
                           {{ $isUnavailable
                               ? 'bg-teal-400/60 text-white/80 hover:bg-teal-500/60'
                               : 'bg-teal-500 text-white hover:bg-teal-600' }}">
                        <x-icons.shopping-cart class="w-3 h-3" />
                        {{ __('View on :site', ['site' => $gift->siteName()]) }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
