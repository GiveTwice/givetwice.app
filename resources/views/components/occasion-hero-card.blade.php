@props([
    'title' => 'Wishlist',
    'gifts' => [
        ['emoji' => '🎁', 'name' => 'Gift idea', 'price' => 50, 'gradient' => 'from-blue-100 to-blue-200'],
        ['emoji' => '🎁', 'name' => 'Gift idea', 'price' => 35, 'gradient' => 'from-amber-100 to-orange-200'],
        ['emoji' => '🎁', 'name' => 'Gift idea', 'price' => 25, 'gradient' => 'from-emerald-100 to-teal-200'],
    ]
])

<div class="relative hidden lg:block">
    <div class="relative">
        <div class="absolute top-0 right-0 w-80 h-80 bg-sunny-200 rounded-full opacity-60 -z-10 transform translate-x-10"></div>

        <div class="relative bg-sunny-100 rounded-[2rem] p-6 transform rotate-2 shadow-lg">
            <div class="bg-white rounded-2xl p-5 shadow-sm transform -rotate-2">

                <div class="flex items-center gap-3 mb-4">
                    <div class="text-3xl">&#127873;</div>
                    <div>
                        <h3 class="font-bold text-gray-900">{{ $title }}</h3>
                        <p class="text-xs text-gray-500">{{ trans_choice(':count gift idea|:count gift ideas', count($gifts), ['count' => count($gifts)]) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    {{-- First gift (static) --}}
                    <div class="hero-card group">
                        <div class="relative aspect-square bg-gradient-to-br {{ $gifts[0]['gradient'] }} rounded-xl overflow-hidden mb-2">
                            <div class="absolute inset-0 flex items-center justify-center text-3xl">{{ $gifts[0]['emoji'] }}</div>
                            <div class="absolute top-1.5 right-1.5">
                                <span class="text-[10px] leading-none bg-teal-500 text-white px-2 py-1 rounded-full font-medium shadow-sm flex items-center justify-center">{{ __('Available') }}</span>
                            </div>
                        </div>
                        <p class="text-xs font-medium text-gray-800 leading-tight line-clamp-2">{{ __($gifts[0]['name']) }}</p>
                        <p class="text-xs font-bold text-coral-500 mt-0.5">&euro; {{ $gifts[0]['price'] }}</p>
                    </div>

                    {{-- Second gift (animated - cozy) --}}
                    <div class="hero-card hero-card-cozy group relative">
                        <div class="relative aspect-square bg-gradient-to-br {{ $gifts[1]['gradient'] }} rounded-xl overflow-hidden mb-2">
                            <div class="absolute inset-0 flex items-center justify-center text-3xl hero-card-emoji">{{ $gifts[1]['emoji'] }}</div>
                            <div class="absolute top-1.5 right-1.5">
                                <span class="hero-badge-available text-[10px] leading-none bg-teal-500 text-white px-2 py-1 rounded-full font-medium shadow-sm flex items-center justify-center">{{ __('Available') }}</span>
                                <span class="hero-badge-claimed text-[10px] leading-none bg-sunny-500 text-white px-2 py-1 rounded-full font-medium shadow-sm absolute inset-0 flex items-center justify-center">{{ __('Claimed') }}</span>
                            </div>

                            <div class="hero-confetti-cozy absolute inset-0 pointer-events-none overflow-visible rounded-xl">
                                <div class="hero-confetti-flash"></div>
                                <span class="confetti-piece confetti-1">&#127881;</span>
                                <span class="confetti-piece confetti-2">&#10024;</span>
                                <span class="confetti-piece confetti-3">&#128171;</span>
                                <span class="confetti-piece confetti-4">&#127882;</span>
                                <span class="confetti-piece confetti-5">&#10084;&#65039;</span>
                                <span class="confetti-piece confetti-6">&#11088;</span>
                                <span class="confetti-dot confetti-dot-coral confetti-7"></span>
                                <span class="confetti-dot confetti-dot-sunny confetti-8"></span>
                                <span class="confetti-dot confetti-dot-teal confetti-9"></span>
                                <span class="confetti-dot confetti-dot-coral confetti-10"></span>
                                <span class="confetti-dot confetti-dot-sunny confetti-11"></span>
                                <span class="confetti-dot confetti-dot-teal confetti-12"></span>
                            </div>
                        </div>
                        <p class="text-xs font-medium text-gray-800 leading-tight line-clamp-2">{{ __($gifts[1]['name']) }}</p>
                        <p class="text-xs font-bold text-coral-500 mt-0.5">&euro; {{ $gifts[1]['price'] }}</p>
                        <div class="hero-card-highlight absolute -inset-1 rounded-2xl pointer-events-none"></div>
                    </div>

                    {{-- Third gift (animated - book) --}}
                    <div class="hero-card hero-card-book group relative">
                        <div class="relative aspect-square bg-gradient-to-br {{ $gifts[2]['gradient'] }} rounded-xl overflow-hidden mb-2">
                            <div class="absolute inset-0 flex items-center justify-center text-3xl hero-card-emoji-book">{{ $gifts[2]['emoji'] }}</div>
                            <div class="absolute top-1.5 right-1.5">
                                <span class="hero-badge-available-book text-[10px] leading-none bg-teal-500 text-white px-2 py-1 rounded-full font-medium shadow-sm flex items-center justify-center">{{ __('Available') }}</span>
                                <span class="hero-badge-claimed-book text-[10px] leading-none bg-sunny-500 text-white px-2 py-1 rounded-full font-medium shadow-sm absolute inset-0 flex items-center justify-center">{{ __('Claimed') }}</span>
                            </div>

                            <div class="hero-confetti-book absolute inset-0 pointer-events-none overflow-visible rounded-xl">
                                <div class="hero-confetti-flash"></div>
                                <span class="confetti-piece confetti-1">&#127881;</span>
                                <span class="confetti-piece confetti-2">&#10024;</span>
                                <span class="confetti-piece confetti-3">&#128171;</span>
                                <span class="confetti-piece confetti-4">&#127882;</span>
                                <span class="confetti-piece confetti-5">&#10084;&#65039;</span>
                                <span class="confetti-piece confetti-6">&#11088;</span>
                                <span class="confetti-dot confetti-dot-coral confetti-7"></span>
                                <span class="confetti-dot confetti-dot-sunny confetti-8"></span>
                                <span class="confetti-dot confetti-dot-teal confetti-9"></span>
                                <span class="confetti-dot confetti-dot-coral confetti-10"></span>
                                <span class="confetti-dot confetti-dot-sunny confetti-11"></span>
                                <span class="confetti-dot confetti-dot-teal confetti-12"></span>
                            </div>
                        </div>
                        <p class="text-xs font-medium text-gray-800 leading-tight line-clamp-2">{{ __($gifts[2]['name']) }}</p>
                        <p class="text-xs font-bold text-coral-500 mt-0.5">&euro; {{ $gifts[2]['price'] }}</p>
                        <div class="hero-card-highlight-book absolute -inset-1 rounded-2xl pointer-events-none"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notification cards --}}
        <div class="hero-notification-sarah absolute -bottom-4 -left-8 bg-white rounded-xl shadow-lg p-3 transform -rotate-3 border border-cream-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 bg-gradient-to-br from-coral-100 to-coral-200 rounded-full flex items-center justify-center shadow-sm">
                    <span class="text-coral-500 text-sm">&#10084;&#65039;</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ __('Sarah claimed a gift!') }}</p>
                    <p class="text-[10px] text-gray-400 font-medium">{{ __('Just now') }}</p>
                </div>
            </div>
        </div>

        <div class="hero-notification-nick absolute -bottom-4 left-4 bg-white rounded-xl shadow-lg p-3 transform rotate-2 border border-cream-100">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 bg-gradient-to-br from-teal-100 to-teal-200 rounded-full flex items-center justify-center shadow-sm">
                    <span class="text-teal-500 text-sm">&#128154;</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ __('Nick claimed a gift!') }}</p>
                    <p class="text-[10px] text-gray-400 font-medium">{{ __('Just now') }}</p>
                </div>
            </div>
        </div>

        {{-- Sparkle decoration --}}
        <div class="absolute -top-4 right-20 text-coral-400 hero-sparkle">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="currentColor">
                <path d="M20 0l2.5 17.5L40 20l-17.5 2.5L20 40l-2.5-17.5L0 20l17.5-2.5z"/>
            </svg>
        </div>
    </div>
</div>
