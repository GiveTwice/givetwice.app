@props(['user'])

@php
    $profileImageUrl = $user->getProfileImageUrl('thumb');
    $hasProfileImage = $user->hasProfileImage();
    $initials = $user->getInitials();
@endphp

<div
    class="relative"
    x-data="{
        open: false,
        hasImage: @js($hasProfileImage),
        imageUrl: @js($profileImageUrl),
        initials: @js($initials),
        init() {
            if (window.Echo) {
                window.Echo.private('user.{{ $user->id }}')
                    .listen('.profile.image.updated', (e) => {
                        this.hasImage = e.user.has_profile_image;
                        this.imageUrl = e.user.profile_image_thumb;
                        this.initials = e.user.initials;

                        // Dispatch window event for other components (mobile menu)
                        window.dispatchEvent(new CustomEvent('profile-image-updated', {
                            detail: {
                                hasImage: e.user.has_profile_image,
                                imageUrl: e.user.profile_image_thumb,
                                initials: e.user.initials
                            }
                        }));
                    });
            }
        }
    }"
    @click.outside="open = false"
>
    {{-- Profile trigger button --}}
    <button
        @click="open = !open"
        type="button"
        class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-cream-200 hover:border-coral-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-coral-300 focus:ring-offset-2 overflow-hidden group"
        :class="hasImage ? '' : 'bg-gradient-to-br from-coral-400 to-coral-500'"
        aria-expanded="false"
        aria-haspopup="true"
        aria-label="{{ __('User menu') }}"
    >
        <template x-if="hasImage">
            <img
                :src="imageUrl"
                alt="{{ $user->name }}"
                class="w-full h-full object-cover"
            >
        </template>
        <template x-if="!hasImage">
            <span class="text-white font-bold text-sm tracking-tight" x-text="initials"></span>
        </template>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 mt-3 w-72 origin-top-right z-50"
        x-cloak
    >
        {{-- Arrow notch --}}
        <div class="absolute -top-2 right-4 w-4 h-4 bg-white border-l border-t border-cream-200 transform rotate-45"></div>

        <div class="relative bg-white rounded-2xl shadow-xl shadow-gray-900/10 border border-cream-200 overflow-hidden">
            {{-- User info header --}}
            <div class="px-5 py-5 bg-gradient-to-br from-cream-50 to-white border-b border-cream-100">
                <div class="flex items-center gap-4">
                    <div
                        class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center overflow-hidden"
                        :class="hasImage ? 'ring-2 ring-cream-200' : 'bg-gradient-to-br from-coral-400 to-coral-500'"
                    >
                        <template x-if="hasImage">
                            <img
                                :src="imageUrl"
                                alt="{{ $user->name }}"
                                class="w-full h-full object-cover"
                            >
                        </template>
                        <template x-if="!hasImage">
                            <span class="text-white font-bold text-lg tracking-tight" x-text="initials"></span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Menu items --}}
            <div class="py-3 px-2">
                <a
                    href="{{ url('/' . app()->getLocale() . '/settings') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-cream-50 hover:text-gray-900 transition-all duration-150 group"
                    @click="open = false"
                >
                    <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-gray-100 group-hover:bg-coral-100 flex items-center justify-center transition-all duration-150">
                        <x-icons.settings class="w-[18px] h-[18px] text-gray-500 group-hover:text-coral-600 transition-colors duration-150" />
                    </span>
                    <span class="font-medium">{{ __('Settings') }}</span>
                </a>

                <a
                    href="{{ url('/' . app()->getLocale() . '/friends') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-cream-50 hover:text-gray-900 transition-all duration-150 group"
                    @click="open = false"
                >
                    <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-gray-100 group-hover:bg-coral-100 flex items-center justify-center transition-all duration-150">
                        <x-icons.users class="w-[18px] h-[18px] text-gray-500 group-hover:text-coral-600 transition-colors duration-150" />
                    </span>
                    <span class="font-medium">{{ __('Friends') }}</span>
                </a>

                <div class="my-2 mx-3 border-t border-cream-100"></div>

                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex items-center gap-4 w-full px-4 py-3 rounded-xl text-gray-700 hover:bg-red-50 transition-all duration-150 group"
                    >
                        <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-gray-100 group-hover:bg-red-100 flex items-center justify-center transition-all duration-150">
                            <x-icons.logout class="w-[18px] h-[18px] text-gray-500 group-hover:text-red-600 transition-colors duration-150" />
                        </span>
                        <span class="font-medium group-hover:text-red-700 transition-colors duration-150">{{ __('Log out') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
