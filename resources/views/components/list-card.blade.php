@props(['list', 'position' => 0])

@php
    $wrapping = \App\Support\ListColour::forPosition($position);

    // With more gifts than tiles, the last tile says how many more there are instead of showing a fourth photo.
    $hasMore = $list->gifts_count > 4;
    $photos = $list->gifts->take($hasMore ? 3 : 4);
    $tilt = ['-rotate-3', 'rotate-2', '-rotate-1', 'rotate-3'];
@endphp

<a
    href="{{ route('list.show', ['locale' => app()->getLocale(), 'list' => $list]) }}"
    class="group flex flex-col gap-5 rounded-2xl p-5 transition-transform duration-150 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-coral-500 {{ $wrapping['card'] }}"
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="break-words text-lg font-bold leading-6">{{ $list->name }}</h3>
            <p class="text-sm {{ $wrapping['muted'] }}">{{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}</p>
        </div>
        @if($list->users->count() > 1)
            {{-- The 20px avatars sit 2px down so they centre on the title's 24px first line. --}}
            <x-avatar-stack :users="$list->users" class="mt-0.5 shrink-0" />
        @endif
    </div>

    {{-- Pushed to the bottom, so the photos of cards in one row line up even when a long name wraps. --}}
    <div class="mt-auto grid grid-cols-4 gap-2.5">
        @for($tile = 0; $tile < 4; $tile++)
            @php $gift = $photos->get($tile); @endphp
            @if($gift)
                <div class="aspect-square rounded-md bg-[#fff] p-1 shadow-md {{ $tilt[$tile] }}">
                    @if($gift->hasImage())
                        <img src="{{ $gift->getImageUrl('card') }}" alt="{{ $gift->title }}" class="h-full w-full rounded-sm object-cover" loading="lazy">
                    @else
                        <div class="flex h-full w-full items-center justify-center rounded-sm bg-[#faf6ef] text-[#9ca3af]">
                            <x-icons.image-placeholder class="h-5 w-5" />
                        </div>
                    @endif
                </div>
            @elseif($hasMore)
                <div class="flex aspect-square flex-col items-center justify-center rounded-md ring-2 {{ $tilt[$tile] }} {{ $wrapping['more'] }}" data-more-gifts>
                    <span class="text-lg font-bold leading-none">+{{ $list->gifts_count - 3 }}</span>
                </div>
            @else
                <div class="aspect-square rounded-md border-2 border-dashed {{ $tilt[$tile] }} {{ $wrapping['empty'] }}"></div>
            @endif
        @endfor
    </div>
</a>
