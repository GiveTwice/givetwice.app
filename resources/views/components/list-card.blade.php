@props(['list'])

<a
    href="{{ route('list.show', ['locale' => app()->getLocale(), 'list' => $list]) }}"
    class="group flex flex-col gap-4 rounded-xl border border-cream-200 bg-white p-4 transition-colors duration-150 hover:border-coral-200 hover:bg-cream-50/70"
>
    <div class="grid grid-cols-4 gap-2">
        @for($position = 0; $position < 4; $position++)
            @php $gift = $list->gifts->get($position); @endphp
            <div class="aspect-square overflow-hidden rounded-lg bg-cream-100">
                @if($gift?->hasImage())
                    <img src="{{ $gift->getImageUrl('card') }}" alt="{{ $gift->title }}" class="h-full w-full object-cover" loading="lazy">
                @elseif($gift)
                    <div class="flex h-full w-full items-center justify-center text-gray-400">
                        <x-icons.image-placeholder class="h-5 w-5" />
                    </div>
                @endif
            </div>
        @endfor
    </div>

    <div class="flex items-end justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate text-base font-semibold text-gray-900 group-hover:text-coral-600">{{ $list->name }}</h3>
            <p class="text-sm text-gray-500">{{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}</p>
        </div>
        @if($list->users->count() > 1)
            <x-avatar-stack :users="$list->users" />
        @endif
    </div>
</a>
