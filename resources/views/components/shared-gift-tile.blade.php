@props(['gift', 'isOwner' => false, 'isReserved' => false, 'isMine' => false])
<article id="gift-{{ $gift->id }}" data-gift-id="{{ $gift->id }}" class="min-w-0">
    <a href="{{ route('claim.anonymous.form', ['locale' => app()->getLocale(), 'gift' => $gift, 'list' => $gift->pivot?->list_id]) }}" x-data x-on:click.prevent="{{ $isOwner ? "\$dispatch('open-gift-modal-{$gift->id}')" : "\$dispatch('reserve-gift', {url: \$el.href})" }}" class="block rounded-xl focus-visible:outline-2 focus-visible:outline-coral-500">
        <div class="relative aspect-square overflow-hidden rounded-xl bg-cream-100" data-gift-image>
            @if($gift->hasImage())
                <img src="{{ $gift->getImageUrl('card') }}" alt="" class="h-full w-full object-cover {{ $isReserved ? 'opacity-50' : '' }}" loading="lazy">
            @else
                <div class="flex h-full items-center justify-center text-sm text-gray-600">{{ __('No image') }}</div>
            @endif
            @if($gift->most_wanted)<span class="absolute left-2 top-2 rounded-full bg-sunny-100 px-2 py-1 text-xs text-gray-800">★ {{ __('Really wants this') }}</span>@endif
            @if($isMine)<span class="absolute bottom-2 left-2 rounded-full bg-teal-100 px-2 py-1 text-sm font-medium text-teal-900">{{ __('Reserved by you') }}</span>
            @elseif($isReserved)<span class="absolute bottom-2 left-2 rounded-full bg-white px-2 py-1 text-xs text-gray-900">{{ __('Reserved') }}</span>@endif
        </div>
        <h2 class="mt-2 min-h-10 line-clamp-2 text-sm font-semibold leading-5 text-gray-900 wrap-anywhere">{{ $gift->title ?: __('Untitled gift') }}</h2>
        <p class="text-sm text-gray-600">{{ $gift->formatPrice() ?? __('No price') }}</p>
    </a>
    @if($isOwner)
        <form method="POST" action="{{ route('gifts.update', ['locale' => app()->getLocale(), 'gift' => $gift, 'list' => $gift->pivot?->list_id]) }}">
            @csrf @method('PUT')
            <input type="hidden" name="most_wanted" value="{{ $gift->most_wanted ? 0 : 1 }}">
            <button class="py-2 text-sm text-coral-600" aria-pressed="{{ $gift->most_wanted ? 'true' : 'false' }}" aria-label="{{ __('Most wanted') }}">{{ $gift->most_wanted ? '★' : '☆' }} {{ __('Most wanted') }}</button>
        </form>
    @endif
</article>
