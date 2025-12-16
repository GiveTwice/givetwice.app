@extends('layouts.app')

@section('title', __('About Us'))

@section('content')

<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-coral-100 text-coral-500 rounded-2xl text-3xl mb-6 transform rotate-3">
        &#10084;&#65039;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('About GiveTwice') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('A wishlist platform where every gift makes a double impact.') }}</p>
</div>

<div class="max-w-4xl mx-auto">

    {{-- The "Twice" Explainer --}}
    <div class="bg-gradient-to-br from-coral-50 to-sunny-50 rounded-3xl p-8 lg:p-12 mb-16 border border-coral-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Why "Twice"?') }}</h2>

        <div class="space-y-4 text-lg text-gray-700 leading-relaxed mb-8">
            <p>{{ __('When someone buys a gift from your wishlist, the store pays us a small affiliate commission. We donate 100% of the profits to charity.') }}</p>
            <p>{{ __('That means every gift gives twice: once to your loved one, and once to those in need.') }}</p>
        </div>

        <div class="bg-white/60 rounded-2xl p-6 border border-coral-100/50">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-teal-500 text-xl">&#10003;</span>
                <span class="font-semibold text-gray-900">{{ __('No extra cost. Ever.') }}</span>
            </div>
            <p class="text-gray-600 ml-8">{{ __('The gift-giver pays the same price they would anywhere else. The wishlist creator pays nothing. The charity donation comes entirely from the store\'s commission — money that would otherwise go to us.') }}</p>
        </div>
    </div>

    {{-- How the money works --}}
    <div class="bg-white rounded-2xl border border-cream-200 p-8 lg:p-10 mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('How the money works') }}</h2>

        <div class="space-y-4 text-gray-700 leading-relaxed mb-8">
            <p>{{ __('We want to be completely upfront: "100% of profits" means we first cover our operating costs — servers, essential software subscriptions, and any legal expenses. What remains goes entirely to charity.') }}</p>
            <p>{{ __('We run as lean as possible. No fancy offices, no bloated team, no unnecessary tools. We keep overhead to an absolute minimum so more money reaches the causes that matter.') }}</p>
            <p>{{ __('The team behind GiveTwice donates their time freely. We built this because we believe in it, not because we\'re looking to make money from it.') }}</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div class="bg-cream-50 rounded-xl p-4 text-center">
                <div class="text-2xl mb-2">&#128187;</div>
                <p class="text-sm text-gray-600">{{ __('Minimal infrastructure costs') }}</p>
            </div>
            <div class="bg-cream-50 rounded-xl p-4 text-center">
                <div class="text-2xl mb-2">&#128588;</div>
                <p class="text-sm text-gray-600">{{ __('Volunteer-built and maintained') }}</p>
            </div>
            <div class="bg-cream-50 rounded-xl p-4 text-center">
                <div class="text-2xl mb-2">&#10084;&#65039;</div>
                <p class="text-sm text-gray-600">{{ __('All profits to charity') }}</p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-cream-100">
            <a href="{{ route('transparency', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
                {{ __('See our transparency commitment') }} <span class="ml-1">&rarr;</span>
            </a>
        </div>
    </div>

    {{-- Why We Built This --}}
    <div class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">{{ __('Why We Built This') }}</h2>

        <div class="grid lg:grid-cols-2 gap-8">
            {{-- Reason 1: Transparency --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 to-teal-600 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl">&#128274;</span>
                        </div>
                        <h3 class="font-bold text-white text-lg">{{ __('Transparency first') }}</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Most wishlist services are "free" — but that usually means you\'re the product. Your data gets sold, your browsing habits tracked, your gift preferences monetized.') }}</p>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('We do things differently. We\'re completely transparent about how we make money: affiliate commissions, with 100% of profits going to charity.') }}</p>
                    <p class="text-gray-600 text-sm">{{ __('Our privacy policy is written in plain language, not legal jargon. We collect only what\'s necessary and never sell your data.') }}</p>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4">
                        <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
                            {{ __('Read our privacy policy') }} <span class="ml-1">&rarr;</span>
                        </a>
                        <a href="{{ route('transparency', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
                            {{ __('Transparency') }} <span class="ml-1">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Reason 2: Design --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-coral-500 to-coral-600 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl">&#10024;</span>
                        </div>
                        <h3 class="font-bold text-white text-lg">{{ __('Design that delights') }}</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Let\'s be honest: most wishlist apps look like they were designed in 2005 and never updated. Clunky interfaces, confusing navigation, walls of ads.') }}</p>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('We believe tools you use should be a joy to use. GiveTwice is built with care — clean, modern, and thoughtfully designed down to the smallest detail.') }}</p>
                    <p class="text-gray-600 text-sm">{{ __('Because sharing your wishlist should feel as good as receiving the gift.') }}</p>
                </div>
            </div>
        </div>
    </div>

</div>

<x-how-it-works :showCta="false" />

<div class="max-w-4xl mx-auto pb-16">

    {{-- Our Values --}}
    <div class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">{{ __('What we stand for') }}</h2>
        <div class="space-y-4">
            <div class="flex items-start gap-5 bg-white rounded-2xl border border-cream-200 p-5 transition-all hover:border-coral-200 hover:shadow-sm">
                <div class="shrink-0 w-12 h-12 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center text-xl">
                    &#10084;
                </div>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Generosity') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('100% of affiliate profits go to charity. We cover costs, nothing more.') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-5 bg-white rounded-2xl border border-cream-200 p-5 transition-all hover:border-teal-200 hover:shadow-sm">
                <div class="shrink-0 w-12 h-12 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center text-xl">
                    &#128065;
                </div>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Honesty') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Clear about our business model. No hidden tracking. No data selling.') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-5 bg-white rounded-2xl border border-cream-200 p-5 transition-all hover:border-sunny-200 hover:shadow-sm">
                <div class="shrink-0 w-12 h-12 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center text-xl">
                    &#9734;
                </div>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-gray-900 mb-1">{{ __('Simplicity') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('No clutter, no ads, no distractions. Just beautiful wishlists that work.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- The Promise --}}
    <div class="bg-cream-100 rounded-3xl p-8 lg:p-10 mb-16">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Our promise') }}</h2>
            <p class="text-gray-700 leading-relaxed mb-6">{{ __('GiveTwice will always be free to use. We\'ll never sell your data. After covering minimal operating costs, every cent of profit goes to charity. And we\'ll keep making this product better — on our own time — because we genuinely believe gift-giving should be simple, beautiful, and meaningful.') }}</p>
            <div class="inline-flex items-center px-5 py-2.5 bg-white rounded-full shadow-sm border border-cream-200">
                <span class="text-coral-500 mr-2">&#10084;</span>
                <span class="text-gray-700 font-medium text-sm">{{ __('One gift. Two smiles. Zero catch.') }}</span>
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Ready to give twice?') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Create your first wishlist in seconds. It\'s free, forever.') }}</p>
        @guest
            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                {{ __('Create Your Wishlist') }} <span class="ml-2">&#127873;</span>
            </a>
        @else
            <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
                {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
            </a>
        @endguest
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
