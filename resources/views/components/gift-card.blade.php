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

    <div class="relative aspect-square bg-cream-100 overflow-hidden" data-gift-image>
        @if($gift->hasImage())
            <img
                src="{{ $gift->getImageUrl('card') }}"
                alt="{{ $gift->title }}"
                class="w-full h-full object-cover object-center transition-all duration-500
                    {{ $isClaimedByOthers
                        ? 'grayscale-[0.5] brightness-[1.05] sepia-[0.2] saturate-[0.7]'
                        : 'group-hover:scale-105' }}"
                loading="lazy"
            >

            @if($isClaimedByOthers)
                <div class="absolute inset-0 bg-gradient-to-t from-sunny-200/50 via-sunny-100/25 to-sunny-50/10 pointer-events-none"></div>
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
            <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') }}"
               class="absolute inset-0 bg-gray-900/0 group-hover:bg-gray-900/10 transition-colors duration-300 flex items-center justify-center">
                <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/95 backdrop-blur-sm text-gray-700 px-4 py-2 rounded-full text-sm font-medium shadow-lg">
                    {{ __('Edit') }}
                </span>
            </a>
        @endif
    </div>

    <div class="px-3 py-2.5 {{ $isClaimedByOthers ? 'bg-sunny-50/40' : '' }}">

        <h3 class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.25rem] {{ $isClaimedByOthers ? 'text-gray-500' : 'text-gray-900' }}" title="{{ $gift->title }}" data-gift-title>
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        <div class="mt-1.5 flex items-center justify-between" data-gift-price>
            @if($gift->hasPrice())
                <span class="text-base font-bold {{ $isClaimedByOthers ? 'text-gray-400' : 'text-coral-600' }}">
                    {{ $gift->formatPrice() }}
                </span>
            @else
                <span class="text-xs text-gray-400 italic">{{ __('No price') }}</span>
            @endif
        </div>

        @if($showClaimActions && !$isOwner)
            <div class="mt-2.5 space-y-1.5" @if($openModal) x-on:click.stop @endif>

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
                        <x-icons.check-circle-filled class="w-3 h-3" />
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
