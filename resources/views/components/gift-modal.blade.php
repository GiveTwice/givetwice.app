@props([
    'gift',
    'isOwner' => false
])

@php
    $isClaimed = $gift->claims_count > 0 || $gift->claims->isNotEmpty();
    $isClaimedByMe = auth()->check() && $gift->claims->where('user_id', auth()->id())->isNotEmpty();

    // Extract domain name from URL (without www prefix)
    $siteName = '';
    if ($gift->url) {
        $parsedUrl = parse_url($gift->url);
        $host = $parsedUrl['host'] ?? '';
        $siteName = preg_replace('/^www\./', '', $host);
    }

    // Format "added ago" text
    $addedAgo = $gift->created_at->diffForHumans();
@endphp

<div
    x-data="{ open: false }"
    x-on:open-gift-modal-{{ $gift->id }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-cloak
>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"
        x-on:click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none"
    >
        <div
            class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden pointer-events-auto"
            x-on:click.stop
        >

            <button
                x-on:click="open = false"
                class="absolute top-3 right-3 z-10 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
                aria-label="{{ __('Close') }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="flex flex-col md:flex-row">

                <div class="md:w-1/2 bg-cream-50 relative">
                    <div class="aspect-square md:aspect-auto md:h-full flex items-center justify-center p-8">
                        @if($gift->hasImage())
                            <img
                                src="{{ $gift->getImageUrl('large') }}"
                                alt="{{ $gift->title }}"
                                class="max-w-full max-h-[400px] md:max-h-full object-contain rounded-lg"
                            >
                        @else
                            <div class="w-full h-64 md:h-full flex flex-col items-center justify-center text-cream-400">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="mt-4 text-lg font-medium">{{ __('No image') }}</span>
                            </div>
                        @endif
                    </div>

                    <button class="absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 hover:bg-white rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-all opacity-0 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 hover:bg-white rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-all opacity-0 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="md:w-1/2 p-6 md:p-8 flex flex-col overflow-y-auto max-h-[50vh] md:max-h-[90vh]">

                    <h2 class="text-2xl md:text-3xl font-bold text-coral-600 leading-tight mb-3">
                        {{ $gift->title ?: __('Untitled gift') }}
                    </h2>

                    <div class="flex items-center gap-3 text-gray-500 mb-6">
                        @if($gift->hasPrice())
                            <span class="text-xl font-bold text-gray-900">
                                {{ $gift->formatPrice() }}
                            </span>
                            <span class="text-gray-300">&middot;</span>
                        @endif
                        <span class="text-sm">{{ __('added') }} {{ $addedAgo }}</span>
                    </div>

                    @if($gift->description)
                        <div class="mb-6 text-gray-600 leading-relaxed prose prose-sm max-w-none">
                            <p class="whitespace-pre-line">{{ $gift->description }}</p>
                        </div>

                        @if(strlen($gift->description) > 300)
                            <button class="text-coral-600 hover:text-coral-700 text-sm font-medium mb-6 self-start">
                                ...{{ __('more') }}
                            </button>
                        @endif
                    @else
                        <div class="mb-6 text-gray-400 italic">
                            {{ __('No description available') }}
                        </div>
                    @endif

                    <div class="flex-1"></div>

                    @unless($isOwner)
                        <div class="space-y-3 mt-4">

                            @if($isClaimedByMe)
                                <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-sunny-200 text-sunny-800 px-5 py-3 rounded-xl hover:bg-sunny-300 transition-colors font-medium">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ __('Claimed - Click to unclaim') }}
                                    </button>
                                </form>
                            @elseif($isClaimed)
                                <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center gap-2 bg-cream-200 text-cream-500 px-5 py-3 rounded-xl cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('Already claimed by someone') }}
                                </button>
                            @else
                                @auth
                                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-sunny-200 text-sunny-800 px-5 py-3 rounded-xl hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ __('Claim gift') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                                       class="w-full inline-flex items-center justify-center gap-2 bg-sunny-200 text-sunny-800 px-5 py-3 rounded-xl hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ __('Claim gift') }}
                                    </a>
                                @endauth
                            @endif

                            @if($gift->url && $siteName)
                                <a href="{{ $gift->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-full inline-flex items-center justify-center gap-2 bg-teal-500 text-white px-5 py-3 rounded-xl hover:bg-teal-600 transition-colors font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('View on :site', ['site' => $siteName]) }}
                                </a>
                            @endif
                        </div>

                        @unless($isClaimed)
                            <p class="mt-4 text-sm text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('Giving this gift?') }}
                                <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" class="text-coral-600 hover:text-coral-700 hover:underline font-medium">{{ __('Claim it') }}</a>
                                {{ __('to prevent duplicates.') }}
                            </p>
                        @endunless
                    @else

                        <div class="space-y-3 mt-4">
                            <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') }}"
                               class="w-full inline-flex items-center justify-center gap-2 bg-cream-100 text-gray-700 px-5 py-3 rounded-xl hover:bg-cream-200 transition-colors font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('Edit gift') }}
                            </a>

                            @if($gift->url && $siteName)
                                <a href="{{ $gift->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-full inline-flex items-center justify-center gap-2 bg-teal-500 text-white px-5 py-3 rounded-xl hover:bg-teal-600 transition-colors font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('View on :site', ['site' => $siteName]) }}
                                </a>
                            @endif
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
