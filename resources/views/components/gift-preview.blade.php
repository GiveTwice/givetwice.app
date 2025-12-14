@props([
    'gift',
    'variant' => 'vertical', // 'vertical' or 'compact'
    'showDescription' => true,
    'showLink' => true,
])

@php
    $siteName = '';
    if ($gift->url) {
        $parsedUrl = parse_url($gift->url);
        $host = $parsedUrl['host'] ?? '';
        $siteName = preg_replace('/^www\./', '', $host);
    }
@endphp

@if($variant === 'compact')

    <div class="flex gap-4 items-start bg-cream-50 rounded-xl p-4 border border-cream-200">

        <div class="w-24 h-24 flex-shrink-0 bg-white rounded-lg overflow-hidden border border-cream-200">
            @if($gift->hasImage())
                <img
                    src="{{ $gift->getImageUrl('thumb') }}"
                    alt="{{ $gift->title }}"
                    class="w-full h-full object-cover"
                >
            @else
                <div class="w-full h-full flex items-center justify-center text-cream-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <h2 class="font-bold text-gray-900 mb-1">{{ $gift->title ?: __('Untitled gift') }}</h2>
            @if($gift->hasPrice())
                <p class="text-lg font-bold text-coral-600 mb-2">
                    {{ $gift->formatPrice() }}
                </p>
            @endif
            @if($showLink && $gift->url && $siteName)
                <a
                    href="{{ $gift->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-sm text-teal-600 hover:text-teal-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    {{ __('View on :site', ['site' => $siteName]) }}
                </a>
            @endif
        </div>
    </div>
@else

    <div class="bg-white rounded-2xl border border-cream-200 overflow-hidden">

        <div class="aspect-square bg-cream-50 relative">
            @if($gift->hasImage())
                <img
                    src="{{ $gift->getImageUrl('card') }}"
                    alt="{{ $gift->title }}"
                    class="w-full h-full object-cover"
                >
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-cream-400">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium">{{ __('No image') }}</span>
                </div>
            @endif
        </div>

        <div class="p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">{{ $gift->title ?: __('Untitled gift') }}</h2>

            @if($gift->hasPrice())
                <p class="text-xl font-bold text-coral-600 mb-3">
                    {{ $gift->formatPrice() }}
                </p>
            @endif

            @if($showDescription && $gift->description)
                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $gift->description }}</p>
            @endif

            @if($showLink && $gift->url && $siteName)
                <a
                    href="{{ $gift->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-sm text-teal-600 hover:text-teal-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    {{ __('View on :site', ['site' => $siteName]) }}
                </a>
            @endif
        </div>
    </div>
@endif
