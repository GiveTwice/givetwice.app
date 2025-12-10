@props(['gift', 'isOwner' => false])

@php
    $isClaimed = $gift->claims_count > 0;
    $isClaimedByMe = auth()->check() && $gift->claims->where('user_id', auth()->id())->isNotEmpty();
@endphp

<div class="bg-white border rounded-lg overflow-hidden shadow-sm">
    <div class="aspect-square bg-gray-100 flex items-center justify-center">
        @if($gift->image_url)
            <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-full h-full object-cover">
        @else
            <div class="text-gray-400 text-center p-4">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm">{{ __('No image') }}</span>
            </div>
        @endif
    </div>

    <div class="p-3">
        <h3 class="font-medium text-gray-900 truncate" title="{{ $gift->title }}">
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        @if($gift->description)
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $gift->description }}</p>
        @endif

        <div class="mt-2 flex items-center justify-between">
            @if($gift->price)
                <span class="text-sm font-semibold text-gray-700">
                    {{ $gift->currency }} {{ number_format($gift->price, 2) }}
                </span>
            @else
                <span class="text-sm text-gray-400">{{ __('No price') }}</span>
            @endif

            @if($isClaimedByMe)
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                    {{ __("You're getting this") }}
                </span>
            @elseif($isClaimed)
                <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">
                    {{ __('Someone is getting this') }}
                </span>
            @endif
        </div>

        <div class="mt-3">
            @if($gift->url)
                <a href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer"
                   class="block w-full text-center text-sm bg-gray-100 text-gray-700 px-3 py-2 rounded hover:bg-gray-200 mb-2">
                    {{ __('View Product') }}
                </a>
            @endif

            @unless($isOwner)
                @if($isClaimedByMe)
                    {{-- User claimed this - show unclaim button --}}
                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-sm bg-red-100 text-red-700 px-3 py-2 rounded hover:bg-red-200">
                            {{ __('Unclaim') }}
                        </button>
                    </form>
                @elseif($isClaimed)
                    {{-- Someone else claimed this --}}
                    <button type="button" disabled
                        class="w-full text-sm bg-gray-200 text-gray-500 px-3 py-2 rounded cursor-not-allowed">
                        {{ __('Already claimed') }}
                    </button>
                @else
                    {{-- Not claimed yet --}}
                    @auth
                        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-sm bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
                                {{ __("I'll get this!") }}
                            </button>
                        </form>
                    @else
                        {{-- Guest user - link to anonymous claim form --}}
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                           class="block w-full text-center text-sm bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
                            {{ __("I'll get this!") }}
                        </a>
                    @endauth
                @endif
            @endunless
        </div>
    </div>
</div>
