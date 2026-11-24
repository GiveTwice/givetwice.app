@props(['gift'])

{{-- touch-none stops a phone from scrolling the page when a drag starts on the handle. --}}
<button
    type="button"
    x-sort:handle
    x-on:keydown.arrow-up.prevent="move($el, -1)"
    x-on:keydown.arrow-left.prevent="move($el, -1)"
    x-on:keydown.arrow-down.prevent="move($el, 1)"
    x-on:keydown.arrow-right.prevent="move($el, 1)"
    title="{{ __('Drag to change the order') }}"
    aria-label="{{ __('Change the order of :gift. Drag it, or use the arrow keys.', ['gift' => $gift->title ?: __('Untitled gift')]) }}"
    class="absolute left-2 top-2 z-10 flex h-9 w-9 cursor-grab touch-none items-center justify-center rounded-lg border border-stone-200 bg-white/95 text-gray-500 shadow-sm transition-colors hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-coral-500 active:cursor-grabbing"
>
    <x-icons.grip class="h-4 w-4" />
</button>
