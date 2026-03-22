@extends('layouts.app')

@section('title', __('Your draw'))

@section('content')
<div class="max-w-lg mx-auto py-8" x-data="revealAnimation()" x-init="startReveal()">

    <p class="text-center text-sm text-gray-500 mb-2">{{ $exchange->name }}</p>

    {{-- Pre-reveal wishlist prompt --}}
    @if(!$participantHasWishlist && !$participant->user_id)
    <div class="bg-sunny-50 rounded-2xl p-5 mb-6 border border-sunny-200 text-center" x-show="!revealed" x-transition>
        <p class="font-semibold text-gray-900 mb-1">{{ __('Quick thought before you peek') }}</p>
        <p class="text-gray-600 text-sm mb-3">{{ __('Want to help whoever drew you? Make a wishlist — takes about a minute.') }}</p>
        <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="btn-primary-sm">{{ __('Make a wishlist') }}</a>
        <p class="text-xs text-gray-400 mt-2">{{ __('Or just scroll down to see your person.') }}</p>
    </div>
    @endif

    {{-- The reveal card --}}
    <div class="bg-white rounded-3xl shadow-lg border border-cream-200 overflow-hidden">

        {{-- Unrevealed state --}}
        <div x-show="!revealed" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="p-8 text-center">
            <div class="text-5xl mb-4">🎲</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Your draw is ready!') }}</h1>
            <p class="text-gray-600 mb-6">{{ __('We shook the hat really well. Promise.') }}</p>
            <button @click="reveal()" class="btn-primary text-lg px-8 py-3">
                {{ __('Show me') }} ✨
            </button>
        </div>

        {{-- Revealed state --}}
        <div x-show="revealed" x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-cloak>
            <div class="bg-gradient-to-br from-sunny-50 to-sunny-100 p-8 text-center border-b border-sunny-200">
                <div class="text-5xl mb-3" x-ref="confettiAnchor">🎉</div>
                <p class="text-gray-600 mb-1">{{ __('You\'re buying for...') }}</p>
                <p class="text-3xl sm:text-4xl font-bold text-coral-500" x-show="nameRevealed" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    {{ $assignedTo->name }}
                </p>
                <div class="flex justify-center gap-4 mt-3 text-sm text-gray-500">
                    @if($exchange->formatBudget())
                        <span>{{ __('Budget') }}: {{ $exchange->formatBudget() }}</span>
                    @endif
                    <span>{{ $exchange->event_date->format('M j') }}</span>
                </div>
            </div>

            <div class="p-6 space-y-4">
                {{-- Wishlist link --}}
                @if($wishlist && $wishlistGiftCount > 0)
                <div class="bg-cream-50 rounded-xl p-4">
                    <p class="font-semibold text-gray-900 text-sm mb-1">
                        {{ $assignedTo->name }} {{ __('has a wishlist on GiveTwice') }}
                    </p>
                    <p class="text-xs text-gray-500 mb-3">
                        {{ $wishlistGiftCount }} {{ trans_choice('item|items', $wishlistGiftCount) }}
                    </p>
                    <a href="{{ $wishlist->getPublicUrl(app()->getLocale()) }}" class="btn-claim w-full text-center">
                        {{ __('See wishlist') }} →
                    </a>
                </div>
                @else
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <p class="text-gray-600 text-sm">
                        {{ $assignedTo->name }} {{ __('hasn\'t made a wishlist yet.') }}
                    </p>
                    <p class="text-gray-400 text-xs mt-1">{{ __('You could nudge them — or just wing it.') }}</p>
                </div>
                @endif

                {{-- Charity note --}}
                <div class="bg-teal-50 rounded-xl p-4 border-l-4 border-teal-400">
                    <p class="text-gray-700 text-sm">
                        {{ __('When you buy from a wishlist here, we donate our commission to charity. You pay nothing extra.') }}
                    </p>
                </div>

                {{-- Create your own wishlist CTA --}}
                @if(!$participantHasWishlist)
                <div class="text-center pt-2">
                    <p class="text-gray-600 text-sm mb-2">{{ __('Want your own wishlist?') }}</p>
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}?utm_source=givetwice&utm_medium=exchange&utm_campaign=reveal" class="btn-secondary-sm">
                        {{ __('Create a wishlist — it\'s free') }}
                    </a>
                </div>
                @endif

                <p class="text-center text-xs text-gray-400 pt-2">
                    {{ __('Don\'t worry — they won\'t know it\'s you.') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/confetti-reveal.js')
<script>
function revealAnimation() {
    return {
        revealed: false,
        nameRevealed: false,

        startReveal() {
            @if($participant->hasViewed())
                this.revealed = true;
                this.nameRevealed = true;
            @endif
        },

        reveal() {
            this.revealed = true;

            setTimeout(() => {
                this.nameRevealed = true;
                this.fireConfetti();
            }, 400);
        },

        fireConfetti() {
            if (typeof confetti === 'undefined') return;

            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.4 },
                colors: ['#f07060', '#f5d680', '#2d9f93', '#fefdfb'],
            });

            setTimeout(() => {
                confetti({
                    particleCount: 50,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                    colors: ['#f07060', '#f5d680'],
                });
                confetti({
                    particleCount: 50,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                    colors: ['#2d9f93', '#fefdfb'],
                });
            }, 300);
        }
    }
}
</script>
@endpush
@endsection
