@php
    use App\Helpers\OccasionHelper;
    $locale = app()->getLocale();
    $holidays = OccasionHelper::getByCategory($locale, 'holidays');
    $lifeEvents = OccasionHelper::getByCategory($locale, 'life-events');
@endphp

<footer class="bg-white border-t border-cream-200 mt-16 safe-area-bottom safe-area-x">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 md:gap-8 mb-8">
            <div>
                <x-logo />
                <p class="text-gray-500 text-sm mt-4">{{ __('Create and share wishlists. All affiliate profits go to charity.') }}</p>
            </div>

            <div>
                <p class="font-semibold text-gray-900 mb-3 md:mb-4">{{ __('Product') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('faq', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('How it works') }}</a></li>
                    <li><a href="{{ route('register', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Create Wishlist') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="font-semibold text-gray-900 mb-3 md:mb-4">{{ __('Holidays') }}</p>
                <ul class="space-y-2 text-sm">
                    @foreach(array_slice($holidays, 0, 6, true) as $key => $occasion)
                        <li><a href="{{ route("occasion.{$key}", ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __($occasion['page_title']) }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="font-semibold text-gray-900 mb-3 md:mb-4">{{ __('Life Events') }}</p>
                <ul class="space-y-2 text-sm">
                    @foreach(array_slice($lifeEvents, 0, 6, true) as $key => $occasion)
                        <li><a href="{{ route("occasion.{$key}", ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __($occasion['page_title']) }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="font-semibold text-gray-900 mb-3 md:mb-4">{{ __('Company & Legal') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('about', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('About') }}</a></li>
                    <li><a href="{{ route('contact', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Contact') }}</a></li>
                    <li><a href="{{ route('privacy', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="{{ route('terms', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Terms of Service') }}</a></li>
                    <li><a href="{{ route('transparency', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Transparency') }}</a></li>
                    <li><a href="{{ route('brand', ['locale' => $locale]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Brand Assets') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-cream-200 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} GiveTwice. {{ __('All rights reserved.') }}
            </p>
            <div class="flex items-center gap-4">
                <a href="https://github.com/GiveTwice" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="GitHub">
                    <x-icons.github class="w-5 h-5" />
                </a>
                <a href="https://x.com/GiveTwiceApp" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="X (Twitter)">
                    <x-icons.x-twitter class="w-5 h-5" />
                </a>
            </div>
            <div class="flex items-center space-x-2 text-sm">
                <span class="text-coral-500">&#10084;</span>
                <span class="text-gray-600">{{ __('All affiliate profits go to charity') }}</span>
            </div>
        </div>
    </div>
</footer>
