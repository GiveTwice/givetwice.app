@props([
    'gift',
    'variant' => 'vertical', // 'vertical' or 'compact'
    'showDescription' => true,
    'showLink' => true,
])

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
                    <x-icons.image-placeholder class="w-8 h-8" />
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
            @if($showLink && $gift->siteName())
                <a
                    href="{{ $gift->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-sm text-teal-600 hover:text-teal-700 font-medium"
                >
                    <x-icons.external-link class="w-4 h-4" />
                    {{ __('View on :site', ['site' => $gift->siteName()]) }}
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
                    <x-icons.image-placeholder class="w-16 h-16" />
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

            @if($showLink && $gift->siteName())
                <a
                    href="{{ $gift->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-sm text-teal-600 hover:text-teal-700 font-medium"
                >
                    <x-icons.external-link class="w-4 h-4" />
                    {{ __('View on :site', ['site' => $gift->siteName()]) }}
                </a>
            @endif
        </div>
    </div>
@endif
