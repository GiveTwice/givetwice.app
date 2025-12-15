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
                <x-icons.close class="w-6 h-6" />
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
                                <x-icons.image-placeholder class="w-24 h-24" />
                                <span class="mt-4 text-lg font-medium">{{ __('No image') }}</span>
                            </div>
                        @endif
                    </div>

                    <button class="absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 hover:bg-white rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-all opacity-0 pointer-events-none">
                        <x-icons.chevron-left class="w-5 h-5" />
                    </button>
                    <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-white/80 hover:bg-white rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-all opacity-0 pointer-events-none">
                        <x-icons.chevron-right class="w-5 h-5" />
                    </button>
                </div>

                <div class="md:w-1/2 p-6 md:p-8 flex flex-col overflow-y-auto max-h-[50vh] md:max-h-[90vh]">

                    <h2 class="text-2xl md:text-3xl font-bold text-coral-600 leading-tight mb-3">
                        {{ $gift->title ?: __('Untitled gift') }}
                    </h2>

                    {{-- Price and Rating Row --}}
                    <div class="flex flex-wrap items-center gap-3 text-gray-500 mb-2">
                        @if($gift->hasPrice())
                            <span class="text-xl font-bold text-gray-900">
                                {{ $gift->formatPrice() }}
                            </span>
                        @endif

                        @if($gift->rating)
                            @if($gift->hasPrice())
                                <span class="text-gray-300">|</span>
                            @endif

                            {{-- Star Rating Display --}}
                            <div class="flex items-center gap-1.5 translate-y-[1px]">
                                <div class="flex items-center" title="{{ $gift->rating }} {{ __('out of 5') }}">
                                    @php
                                        $fullStars = floor($gift->rating);
                                        $hasHalfStar = ($gift->rating - $fullStars) >= 0.3 && ($gift->rating - $fullStars) < 0.8;
                                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                        if (($gift->rating - $fullStars) >= 0.8) {
                                            $fullStars++;
                                            $emptyStars = 5 - $fullStars;
                                            $hasHalfStar = false;
                                        }
                                    @endphp

                                    {{-- Full stars --}}
                                    @for($i = 0; $i < $fullStars; $i++)
                                        <x-icons.star class="w-4 h-4 text-sunny-400" />
                                    @endfor

                                    {{-- Half star --}}
                                    @if($hasHalfStar)
                                        <svg class="w-4 h-4" viewBox="0 0 20 20">
                                            <defs>
                                                <linearGradient id="half-star-{{ $gift->id }}">
                                                    <stop offset="50%" stop-color="#facc15"/>
                                                    <stop offset="50%" stop-color="#d1d5db"/>
                                                </linearGradient>
                                            </defs>
                                            <path fill="url(#half-star-{{ $gift->id }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endif

                                    {{-- Empty stars --}}
                                    @for($i = 0; $i < $emptyStars; $i++)
                                        <x-icons.star class="w-4 h-4 text-gray-300" />
                                    @endfor
                                </div>

                                @if($gift->review_count)
                                    <a href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-teal-600 hover:text-teal-700 hover:underline">
                                        {{ __('See all :count reviews', ['count' => number_format($gift->review_count)]) }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Added ago - more muted, separate line --}}
                    <p class="text-sm text-gray-400 mb-6">{{ __('Added') }} {{ $addedAgo }}</p>

                    @if($gift->description)
                        <div x-data="{ expanded: false }" class="mb-6">
                            {{-- Collapsed state: max 6 lines with fade --}}
                            <div x-show="!expanded" class="relative">
                                <p class="text-gray-600 leading-relaxed whitespace-pre-line line-clamp-6">{{ $gift->description }}</p>
                                {{-- Fade overlay - only shown if text is long enough to be truncated --}}
                                @if(substr_count($gift->description, "\n") >= 5 || strlen($gift->description) > 350)
                                    <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-white via-white/90 to-transparent pointer-events-none"></div>
                                    <button
                                        @click="expanded = true"
                                        class="absolute bottom-0 left-0 right-0 h-12 flex items-end justify-center pb-0.5 text-coral-600 hover:text-coral-700 text-sm font-medium transition-colors cursor-pointer"
                                    >
                                        <span class="flex items-center gap-1 bg-white/80 px-2 py-0.5 rounded">
                                            {{ __('Show more') }}
                                            <x-icons.chevron-down class="w-4 h-4" />
                                        </span>
                                    </button>
                                @endif
                            </div>

                            {{-- Expanded state: scrollable container --}}
                            <div
                                x-show="expanded"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="relative"
                            >
                                <div class="max-h-48 overflow-y-auto pr-2 scrollbar-subtle">
                                    <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $gift->description }}</p>
                                </div>
                                <button
                                    @click="expanded = false"
                                    class="mt-2 text-coral-600 hover:text-coral-700 text-sm font-medium transition-colors flex items-center gap-1"
                                >
                                    {{ __('Show less') }}
                                    <x-icons.chevron-up class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
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
                                        <x-icons.checkmark class="w-5 h-5" />
                                        {{ __('Claimed - Click to unclaim') }}
                                    </button>
                                </form>
                            @elseif($isClaimed)
                                <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center gap-2 bg-cream-200 text-cream-500 px-5 py-3 rounded-xl cursor-not-allowed">
                                    <x-icons.check-circle-filled class="w-5 h-5" />
                                    {{ __('Already claimed by someone') }}
                                </button>
                            @else
                                @auth
                                    <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-sunny-200 text-sunny-800 px-5 py-3 rounded-xl hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow">
                                            <x-icons.checkmark class="w-5 h-5" />
                                            {{ __('Claim gift') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}"
                                       class="w-full inline-flex items-center justify-center gap-2 bg-sunny-200 text-sunny-800 px-5 py-3 rounded-xl hover:bg-sunny-300 transition-colors font-medium shadow-sm hover:shadow">
                                        <x-icons.checkmark class="w-5 h-5" />
                                        {{ __('Claim gift') }}
                                    </a>
                                @endauth
                            @endif

                            @if($gift->url && $siteName)
                                <a href="{{ $gift->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-full inline-flex items-center justify-center gap-2 bg-teal-500 text-white px-5 py-3 rounded-xl hover:bg-teal-600 transition-colors font-medium">
                                    <x-icons.shopping-cart class="w-5 h-5" />
                                    {{ __('View on :site', ['site' => $siteName]) }}
                                </a>
                            @endif
                        </div>

                        @unless($isClaimed)
                            <p class="mt-4 text-sm text-gray-500 flex items-center gap-2">
                                <x-icons.info-circle class="w-4 h-4 text-gray-400" />
                                {{ __('Giving this gift?') }}
                                <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim') }}" class="text-coral-600 hover:text-coral-700 hover:underline font-medium">{{ __('Claim it') }}</a>
                                {{ __('to prevent duplicates.') }}
                            </p>
                        @endunless
                    @else

                        <div class="space-y-3 mt-4">
                            <a href="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') }}"
                               class="w-full inline-flex items-center justify-center gap-2 bg-cream-100 text-gray-700 px-5 py-3 rounded-xl hover:bg-cream-200 transition-colors font-medium">
                                <x-icons.edit class="w-5 h-5" />
                                {{ __('Edit gift') }}
                            </a>

                            @if($gift->url && $siteName)
                                <a href="{{ $gift->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-full inline-flex items-center justify-center gap-2 bg-teal-500 text-white px-5 py-3 rounded-xl hover:bg-teal-600 transition-colors font-medium">
                                    <x-icons.shopping-cart class="w-5 h-5" />
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
