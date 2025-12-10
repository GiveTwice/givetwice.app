@props(['gift', 'editable' => true])

<a href="{{ $editable ? url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/edit') : '#' }}" class="block bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow {{ $editable ? 'cursor-pointer' : '' }}">
    <div class="aspect-square bg-gray-100 flex items-center justify-center">
        @if($gift->image_url)
            <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-full h-full object-cover">
        @else
            <div class="text-gray-400 text-center p-4">
                @if($gift->isPending() || $gift->isFetching())
                    <svg class="w-12 h-12 mx-auto mb-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm">{{ __('Loading...') }}</span>
                @else
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm">{{ __('No image') }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="p-3">
        <h3 class="font-medium text-gray-900 truncate" title="{{ $gift->title }}">
            {{ $gift->title ?: __('Untitled gift') }}
        </h3>

        <div class="mt-1 flex items-center justify-between">
            @if($gift->price)
                <span class="text-sm font-semibold text-gray-700">
                    {{ $gift->currency }} {{ number_format($gift->price, 2) }}
                </span>
            @else
                <span class="text-sm text-gray-400">{{ __('No price') }}</span>
            @endif

            @if($gift->isClaimed())
                <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">{{ __('Claimed') }}</span>
            @elseif($gift->isPending() || $gift->isFetching())
                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">{{ __('Fetching') }}</span>
            @elseif($gift->isFetchFailed())
                <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded">{{ __('Failed') }}</span>
            @endif
        </div>
    </div>
</a>
