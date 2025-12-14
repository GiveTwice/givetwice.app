@props(['showCta' => true])

<div id="how-it-works" class="bg-cream-100 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">{{ __('How It Works') }}</h2>
        <p class="text-center text-gray-600 mb-12">{{ __('Three simple steps to perfect gift-giving') }}</p>

        <div class="grid md:grid-cols-3 gap-8 relative">

            <div class="text-center relative">
                <div class="w-16 h-16 bg-coral-100 text-coral-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 hiw-step-1-icon">
                    &#127873;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">1. {{ __('Create') }}</h3>
                <p class="text-gray-600">{{ __('Add gifts from any online store by pasting a product URL. We\'ll fetch the details automatically.') }}</p>

                <div class="hidden md:block absolute top-8 -right-4 translate-x-1/2 hiw-arrow-1-2">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </div>

            <div class="text-center relative">
                <div class="w-16 h-16 bg-sunny-200 text-sunny-700 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 hiw-step-2-icon">
                    &#128140;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">2. {{ __('Share') }}</h3>
                <p class="text-gray-600">{{ __('Send your wishlist link to friends and family via email, chat, or social media.') }}</p>

                <div class="hidden md:block absolute top-8 -right-4 translate-x-1/2 hiw-arrow-2-3">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 hiw-step-3-icon">
                    &#10003;
                </div>
                <h3 class="font-bold text-xl mb-2 text-gray-900">3. {{ __('Receive') }}</h3>
                <p class="text-gray-600">{{ __('Others secretly claim gifts so you don\'t get duplicates. Everyone\'s happy!') }}</p>
            </div>
        </div>

        @if($showCta)
            <div class="text-center mt-12">
                @guest
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                        {{ __('Get Started - It\'s Free!') }}
                    </a>
                @else
                    <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                        {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
                    </a>
                @endguest
            </div>
        @endif
    </div>
</div>
