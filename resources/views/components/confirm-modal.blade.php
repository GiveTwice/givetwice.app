@props([
    'id',
    'title' => __('Are you sure?'),
    'message' => __('This action cannot be undone.'),
    'confirmText' => __('Delete'),
    'cancelText' => __('Cancel'),
    'destructive' => true,
    'customButtons' => false,
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
                            <x-icons.warning class="w-6 h-6 text-red-600" />
                        @else
                            <x-icons.help-circle class="w-6 h-6 text-sunny-600" />
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

            <div class="p-6 @if(!$customButtons) flex items-center justify-end gap-3 @endif">
                @unless($customButtons)
                    <button
                        type="button"
                        x-on:click="open = false"
                        class="px-4 py-2.5 text-gray-700 font-medium hover:bg-gray-100 rounded-xl transition-colors"
                    >
                        {{ $cancelText }}
                    </button>
                @endunless

                {{ $slot }}
            </div>
        </div>
    </div>
</div>
