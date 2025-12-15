@props(['list'])

@php
    $publicUrl = route('public.list', ['locale' => app()->getLocale(), 'list' => $list]);
    $shareMessage = __('You can view my wish list here:') . ' ' . $publicUrl;
@endphp

<div x-data="{
    open: false,
    copiedUrl: false,
    copiedMessage: false,
    showToast: false,
    copyToClipboard(text, type) {
        navigator.clipboard.writeText(text).then(() => {
            if (type === 'url') {
                this.copiedUrl = true;
                setTimeout(() => this.copiedUrl = false, 2000);
            } else {
                this.copiedMessage = true;
                setTimeout(() => this.copiedMessage = false, 2000);
            }
            this.showToast = true;
            setTimeout(() => this.showToast = false, 2500);
        });
    }
}">
    <!-- Share Button -->
    <button @click="open = true" class="btn-share">
        <x-icons.share class="w-5 h-5" />
        {{ __('Share') }}
    </button>

    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-40"
        @click="open = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="open = false"
    >
        <div class="relative bg-white rounded-xl shadow-xl max-w-xl w-full p-6" @click.stop>
            <!-- Header -->
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-bold text-gray-900">{{ __('Share Your Wishlist') }}</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <x-icons.close class="w-6 h-6" />
                </button>
            </div>

            <!-- Steps -->
            <div class="space-y-4 mb-6">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-7 h-7 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Copy the link below') }}</p>
                        <p class="text-sm text-gray-500">{{ __('This is the public link to your wishlist') }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-7 h-7 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Send it to friends and family') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Via email, WhatsApp, Messenger, or any other way') }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-7 h-7 bg-sunny-200 text-sunny-700 rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('They can claim gifts') }}</p>
                        <p class="text-sm text-gray-500">{{ __("You won't see who claimed what - it's a surprise!") }}</p>
                    </div>
                </div>
            </div>

            <!-- Public URL -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Public Link') }}</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        value="{{ $publicUrl }}"
                        readonly
                        class="flex-1 min-w-0 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 focus:outline-none"
                    >
                    <a
                        href="{{ $publicUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 bg-sunny-200 text-sunny-800 rounded-lg hover:bg-sunny-300 transition-colors flex items-center gap-1.5 whitespace-nowrap font-medium"
                    >
                        {{ __('View') }}
                        <x-icons.external-link class="w-3.5 h-3.5" />
                    </a>
                    <button
                        @click="copyToClipboard('{{ $publicUrl }}', 'url')"
                        class="w-[88px] px-4 py-2 bg-coral-500 text-white rounded-lg hover:bg-coral-600 transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap"
                    >
                        <template x-if="!copiedUrl">
                            <span class="flex items-center gap-1.5">
                                <x-icons.copy class="w-4 h-4" />
                                {{ __('Copy') }}
                            </span>
                        </template>
                        <template x-if="copiedUrl">
                            <span class="flex items-center gap-1.5 text-white">
                                <x-icons.checkmark class="w-4 h-4" />
                                {{ __('Copied!') }}
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Ready-to-send message -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Ready-to-send message') }}</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        value="{{ $shareMessage }}"
                        readonly
                        class="flex-1 min-w-0 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 focus:outline-none"
                    >
                    <button
                        @click="copyToClipboard(`{{ $shareMessage }}`, 'message')"
                        class="w-[88px] px-4 py-2 bg-coral-500 text-white rounded-lg hover:bg-coral-600 transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap"
                    >
                        <template x-if="!copiedMessage">
                            <span class="flex items-center gap-1.5">
                                <x-icons.copy class="w-4 h-4" />
                                {{ __('Copy') }}
                            </span>
                        </template>
                        <template x-if="copiedMessage">
                            <span class="flex items-center gap-1.5 text-white">
                                <x-icons.checkmark class="w-4 h-4" />
                                {{ __('Copied!') }}
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Close button -->
            <button
                @click="open = false"
                class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
                {{ __('Done') }}
            </button>

        </div>

        <!-- Toast notification - positioned at top of viewport -->
        <div
            x-show="showToast"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="fixed top-6 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 bg-teal-500 text-white rounded-xl shadow-xl flex items-center gap-2"
        >
            <x-icons.check-circle class="w-5 h-5 flex-shrink-0" />
            <span class="font-medium whitespace-nowrap">{{ __('Copied to clipboard!') }}</span>
        </div>
    </div>
</div>
