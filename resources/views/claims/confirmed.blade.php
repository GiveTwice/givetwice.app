@extends('layouts.app')

@section('title', __('Claim Confirmed'))

@section('robots', 'noindex, nofollow')

@push('styles')
    @vite('resources/css/confetti.css')
@endpush

@push('scripts')
    @vite('resources/js/confetti.js')
@endpush

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Buyer's high: You just gave twice --}}
    <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-2xl p-6 sm:p-8 mb-8 border border-teal-200 text-center">
        <div class="text-4xl mb-3">✨</div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">{{ __('You just gave twice.') }}</h1>
        <p class="text-gray-600 text-lg mb-6">{{ __('One gift. Two smiles.') }}</p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-md mx-auto mb-6">
            <div class="flex-1 bg-white rounded-xl p-4 shadow-sm">
                <div class="text-2xl mb-1">🎁</div>
                <p class="font-semibold text-gray-900 text-sm line-clamp-1">{{ $gift->title ?: __('A gift') }}</p>
                <p class="text-gray-500 text-xs">{{ __('For someone special') }}</p>
            </div>
            <div class="flex-1 bg-white rounded-xl p-4 shadow-sm">
                <div class="text-2xl mb-1">❤️</div>
                <p class="font-semibold text-teal-600">~€2-5</p>
                <p class="text-gray-500 text-xs">{{ __('Donated to charity') }}</p>
            </div>
        </div>

        <p class="text-gray-500 text-sm mb-4">{{ __('You didn\'t pay a cent extra. The store\'s commission? We donated all of it. Cape not included.') }}</p>

        <div class="flex flex-wrap gap-2 justify-center">
            <a href="https://wa.me/?text={{ urlencode(__('I just gave twice on GiveTwice! One gift for a friend, one donation to charity.') . ' ' . config('app.url')) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 text-sm font-medium border border-gray-200 transition-colors">
                📱 {{ __('WhatsApp') }}
            </a>
            <button onclick="navigator.clipboard.writeText('{{ config('app.url') }}?utm_source=givetwice&utm_medium=share&utm_campaign=gave-twice')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 text-sm font-medium border border-gray-200 transition-colors">
                📋 {{ __('Copy link') }}
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-cream-400 overflow-hidden highlight-sunny-pulse">

        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-start">

                <div class="flex-shrink-0 w-32 h-32 sm:w-40 sm:h-40 rounded-2xl bg-cream-50 border border-cream-200 overflow-hidden">
                    @if($gift->hasImage())
                        <img src="{{ $gift->getImageUrl('thumb') }}" alt="{{ $gift->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-cream-400">
                            <x-icons.image-placeholder class="w-12 h-12" />
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0 text-center sm:text-left">
                    <h2 class="text-xl font-bold text-gray-900 line-clamp-2">{{ $gift->title ?: __('Untitled gift') }}</h2>
                    @if($gift->hasPrice())
                        <p class="text-2xl font-bold text-coral-600 mt-1">{{ $gift->formatPrice() }}</p>
                    @endif
                    @if($gift->description)
                        <p class="text-gray-500 text-sm mt-3 line-clamp-3">{{ $gift->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-5 bg-sunny-50 border-t border-sunny-200">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-sunny-200 rounded-full flex items-center justify-center flex-shrink-0">
                    <x-icons.shopping-cart class="w-5 h-5 text-sunny-700" />
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sunny-900">{{ __('Time to buy the gift!') }}</p>
                    <p class="text-sm text-sunny-700 mt-0.5">
                        {{ __('Now head to the store and buy it. We handle the charity part.') }}
                    </p>
                </div>
            </div>
        </div>

        @if($gift->buyUrl($list->id ?? null))
            <div class="p-6 sm:p-8 border-t border-cream-100">
                <a
                    href="{{ $gift->buyUrl($list->id ?? null) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-semibold text-lg shadow-sm hover:shadow"
                >
                    <x-icons.shopping-cart class="w-5 h-5" />
                    @if($gift->siteName())
                        {{ __('Buy on :site', ['site' => $gift->siteName()]) }}
                    @else
                        {{ __('Buy this gift') }}
                    @endif
                    <x-icons.external-link class="w-4 h-4 ml-1" />
                </a>
            </div>
        @endif
    </div>

    <div class="mt-6 flex items-center justify-center gap-4">
        @if($list ?? null)
            <a href="{{ $list->getPublicUrl() }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 font-medium transition-colors">
                <x-icons.arrow-left class="w-4 h-4" />
                {{ __('Back to wishlist') }}
            </a>
        @endif
    </div>

    <div class="mt-10 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iNCIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

        <div class="relative px-6 sm:px-10 py-10 sm:py-12">
            <div class="max-w-2xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white/90 text-sm font-medium mb-4">
                    <span>&#127873;</span>
                    {{ __('Create Your Own') }}
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ __('Want your own wishlist?') }}</h2>
                <p class="text-coral-100 text-lg mb-6">{{ __('Make a wishlist. Share it with your people. Every gift donates to charity.') }}</p>

                @guest
                    <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        <span>{{ __('Make your own wishlist') }}</span>
                        <x-icons.arrow-right class="w-5 h-5" />
                    </a>
                    <p class="mt-3 text-coral-200 text-sm">{{ __('Free. No ads. All profits go to charity.') }}</p>
                @else
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        <span>{{ __('Go to My Wishlists') }}</span>
                        <x-icons.arrow-right class="w-5 h-5" />
                    </a>
                @endguest
            </div>
        </div>
    </div>
</div>
@endsection
