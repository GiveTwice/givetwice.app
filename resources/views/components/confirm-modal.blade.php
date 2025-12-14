@props([
    'id',
    'title' => __('Are you sure?'),
    'message' => __('This action cannot be undone.'),
    'confirmText' => __('Delete'),
    'cancelText' => __('Cancel'),
    'destructive' => true,
])

<div
    x-data="{ open: false }"
    x-on:open-confirm-{{ $id }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-cloak
>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"
        x-on:click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div
            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
            x-on:click.stop
        >

            <div class="p-6 pb-0">
                <div class="flex items-start gap-4">

                    <div class="flex-shrink-0 w-12 h-12 rounded-full {{ $destructive ? 'bg-red-100' : 'bg-sunny-100' }} flex items-center justify-center">
                        @if($destructive)
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-sunny-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $title }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $message }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2.5 text-gray-700 font-medium hover:bg-gray-100 rounded-xl transition-colors"
                >
                    {{ $cancelText }}
                </button>

                {{ $slot }}
            </div>
        </div>
    </div>
</div>
