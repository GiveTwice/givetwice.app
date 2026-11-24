@props(['gift', 'isOwner' => false, 'isReserved' => false, 'isMine' => false])
<article id="gift-{{ $gift->id }}" data-gift-id="{{ $gift->id }}" class="flex h-full min-w-0 flex-col overflow-hidden rounded-xl border border-stone-200/80 bg-white shadow-sm transition duration-150 hover:-translate-y-0.5 hover:shadow-md">
    <a href="{{ route('claim.anonymous.form', ['locale' => app()->getLocale(), 'gift' => $gift, 'list' => $gift->pivot?->list_id]) }}" x-data x-on:click.prevent="{{ $isOwner ? "\$dispatch('open-gift-modal-{$gift->id}')" : "\$dispatch('reserve-gift', {url: \$el.href})" }}" class="flex flex-1 flex-col focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-coral-500">
        <div class="relative aspect-square overflow-hidden bg-cream-100" data-gift-image>
            @if($gift->hasImage())
                <img src="{{ $gift->getImageUrl('card') }}" alt="" class="h-full w-full object-cover {{ $isReserved ? 'opacity-50' : '' }}" loading="lazy">
            @else
                <div class="flex h-full items-center justify-center text-sm text-gray-600">{{ __('No image') }}</div>
            @endif
            @if($gift->most_wanted)<span class="absolute {{ $isOwner ? 'left-13 top-3' : 'left-2 top-2' }} rounded-full bg-sunny-100 px-2 py-1 text-xs text-gray-800">★ {{ __('Really wants this') }}</span>@endif
            @if($isMine)<span class="absolute bottom-2 left-2 rounded-full bg-teal-100 px-2 py-1 text-sm font-medium text-teal-900">{{ __('Reserved by you') }}</span>
            @elseif($isReserved)<span class="absolute bottom-2 left-2 rounded-full bg-white px-2 py-1 text-xs text-gray-900">{{ __('Reserved') }}</span>@endif
        </div>
        <div class="flex flex-1 flex-col px-3 pt-2.5 pb-3">
        <h2 class="min-h-10 line-clamp-2 text-sm font-semibold leading-5 text-gray-900 wrap-anywhere">{{ $gift->title ?: __('Untitled gift') }}</h2>
        <p class="text-sm text-gray-600">{{ $gift->formatPrice() ?? __('No price') }}</p>
        {{-- Every tile ends with an action row of the same height, so tiles in one row line up. --}}
        @unless($isOwner)
            @if($isMine)
                <span class="mt-2 flex min-h-9 w-full items-center justify-center rounded-[10px] border border-teal-200 bg-white px-3 text-sm font-semibold text-teal-800">{{ __('View my reservation') }}</span>
            @elseif($isReserved)
                <span class="mt-2 flex min-h-9 w-full items-center justify-center rounded-[10px] border border-stone-200 bg-stone-50 px-3 text-sm font-semibold text-gray-500">{{ __('Already reserved') }}</span>
            @else
                <span class="mt-2 flex min-h-9 w-full items-center justify-center rounded-[10px] border border-coral-200 bg-white px-3 text-sm font-semibold text-coral-700 transition-colors hover:bg-coral-50">{{ __('Reserve') }}</span>
            @endif
        @endunless
        </div>
    </a>
    @if($isOwner)
        <form method="POST" action="{{ route('gifts.update', ['locale' => app()->getLocale(), 'gift' => $gift, 'list' => $gift->pivot?->list_id]) }}" class="border-t border-gray-100 px-3">
            @csrf @method('PUT')
            <input type="hidden" name="most_wanted" value="{{ $gift->most_wanted ? 0 : 1 }}">
            <button class="py-2 text-sm text-coral-600" aria-pressed="{{ $gift->most_wanted ? 'true' : 'false' }}" aria-label="{{ __('Most wanted') }}">{{ $gift->most_wanted ? '★' : '☆' }} {{ __('Most wanted') }}</button>
        </form>
    @endif
</article>
