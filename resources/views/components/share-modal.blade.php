@props(['list', 'showLabel' => false, 'quiet' => false])
<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" @click="open = true" class="{{ $quiet ? 'inline-flex min-h-11 items-center gap-2 py-2 text-sm text-gray-600 hover:text-gray-900' : 'btn-share' }}" title="{{ __('Share') }}">
        <x-icons.share class="w-5 h-5" />
        <span class="{{ $showLabel ? '' : 'hidden sm:inline' }}">{{ __('Share') }}</span>
    </button>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="open = false">
        <section role="dialog" aria-modal="true" aria-label="{{ __('Share your wishlist') }}" class="w-full max-w-lg max-h-[90dvh] overflow-y-auto rounded-xl bg-white p-5 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 mb-5">
                <h2 class="text-xl font-semibold">{{ __('Share your wishlist') }}</h2>
                <button type="button" @click="open = false" class="inline-flex min-h-11 min-w-11 items-center justify-center" aria-label="{{ __('Close') }}"><x-icons.close class="size-5" /></button>
            </div>
            <p class="mb-5 break-words font-medium">{{ $list->name }}</p>
            <x-list-share-actions :list="$list" />
            <p class="mt-5 text-sm text-gray-500">{{ __('Anyone with the link can view the list.') }}</p>
        </section>
    </div>
</div>
