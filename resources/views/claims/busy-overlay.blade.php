{{-- Covers the whole reservation sheet while a request runs, so the old screen cannot be clicked twice. --}}
<div x-show="busy" x-cloak x-transition.opacity.duration.150ms class="absolute inset-0 z-10 flex items-center justify-center rounded-[inherit] bg-white/85" role="status">
    <x-icons.spinner class="h-8 w-8 text-coral-500" />
    <span class="sr-only">{{ __('Loading...') }}</span>
</div>
