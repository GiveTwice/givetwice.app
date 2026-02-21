<div class="md:hidden flex items-center">
    <button
        type="button"
        @click="mobileOpen = !mobileOpen"
        :aria-expanded="mobileOpen.toString()"
        class="relative w-11 h-11 flex items-center justify-center text-gray-600 hover:text-gray-900 rounded-lg transition-colors"
        aria-label="{{ __('Toggle navigation') }}"
    >
        <x-icons.menu
            class="h-6 w-6 absolute transition-all duration-200"
            x-bind:class="mobileOpen ? 'opacity-0 rotate-90 scale-75' : 'opacity-100 rotate-0 scale-100'"
            aria-hidden="true"
        />
        <x-icons.close
            class="h-6 w-6 absolute transition-all duration-200"
            x-bind:class="mobileOpen ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-75'"
            aria-hidden="true"
        />
    </button>
</div>
