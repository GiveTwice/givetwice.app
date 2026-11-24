@props(['list', 'sortable' => false])

{{-- Each gift in the slot needs data-sort-id and x-sort:item, and an <x-gift-sort-handle> to drag it by. --}}
@if($sortable)
    <div x-data="giftSort(@js([
        'url' => route('gifts.order', ['locale' => app()->getLocale(), 'list' => $list]),
        'saved' => __('Order saved.'),
        'failed' => __("Couldn't save the order. Refresh and try again."),
    ]))">
        <div x-ref="grid" x-sort="save()" {{ $attributes }}>
            {{ $slot }}
        </div>
        <div role="status" aria-live="polite" class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4">
            <p
                x-show="status"
                x-text="status"
                x-transition.opacity
                x-cloak
                x-bind:class="failed ? 'bg-coral-500 shadow-coral-500/25' : 'bg-teal-500 shadow-teal-500/25'"
                class="rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
            ></p>
        </div>
    </div>
@else
    <div {{ $attributes }}>
        {{ $slot }}
    </div>
@endif
