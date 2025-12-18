@extends('layouts.app')

@section('title', __('FAQ'))

@section('description', __('meta.faq'))

@section('content')

<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-sunny-100 text-sunny-600 rounded-2xl text-3xl mb-6 transform -rotate-3">
        &#10067;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('Questions & answers') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('The short version of how GiveTwice works') }}</p>
</div>

<div class="max-w-3xl mx-auto pb-16">
    <div class="space-y-4">

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    1
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('What is :app?', ['app' => config('app.name')]) }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('A wishlist app where 100% of our profits go to charity. You create lists, share them with friends and family, and when people buy gifts through our links, stores pay us a commission. We donate all of it after covering basic costs (servers, that sort of thing). Free to use, no tracking, no ads.', ['app' => config('app.name')]) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    2
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('How do I create a wishlist?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Sign up for a free account, then paste product URLs from any online store. We\'ll grab the product image, title, and price automatically.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center font-bold">
                    3
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Can others see who claimed a gift?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Nope. When someone claims a gift, you just see "someone is getting this" - no names, no hints. The surprise stays a surprise.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    4
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Which charities do you support?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('We\'re still figuring this out. We\'re launching in early 2026, and we\'ll announce our charity partners before then. We plan to rotate partners and publish exactly how much we donate to each one.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    5
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Is it really free?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Yes. Always will be. We make money through affiliate commissions when people buy gifts - the store pays us a small referral fee. After we cover hosting and essential costs, we donate the rest. You never pay anything.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-sunny-100 text-sunny-600 rounded-xl flex items-center justify-center font-bold">
                    6
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Do I need an account to claim a gift?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Nope. Just enter your email and we\'ll send you a confirmation link. That\'s it.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-coral-100 text-coral-500 rounded-xl flex items-center justify-center font-bold">
                    7
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('Can I add items from any store?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Pretty much any online store works. Paste the product URL, we\'ll fetch the details. If it doesn\'t work for a specific store, let us know.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-cream-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center font-bold">
                    8
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ __('How do I share my wishlist?') }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ __('Each wishlist has its own link. Copy it and send it however you want - email, WhatsApp, text message, carrier pigeon. Whatever works.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-12 bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-8 text-center border border-coral-100">
        <div class="text-3xl mb-4">&#128172;</div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Something else?') }}</h2>
        <p class="text-gray-600 mb-6">{{ __('Questions? Email us. We typically respond within a day.') }}</p>
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
            {{ __('Get in touch') }}
        </a>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "{{ __('What is :app?', ['app' => config('app.name')]) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('A wishlist app where 100% of our profits go to charity. You create lists, share them with friends and family, and when people buy gifts through our links, stores pay us a commission. We donate all of it after covering basic costs (servers, that sort of thing). Free to use, no tracking, no ads.', ['app' => config('app.name')]) }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('How do I create a wishlist?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('Sign up for a free account, then paste product URLs from any online store. We\'ll grab the product image, title, and price automatically.') }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('Can others see who claimed a gift?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": {!! json_encode(__('Nope. When someone claims a gift, you just see "someone is getting this" - no names, no hints. The surprise stays a surprise.'), JSON_UNESCAPED_UNICODE) !!}
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('Which charities do you support?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('We\'re still figuring this out. We\'re launching in early 2026, and we\'ll announce our charity partners before then. We plan to rotate partners and publish exactly how much we donate to each one.') }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('Is it really free?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('Yes. Always will be. We make money through affiliate commissions when people buy gifts - the store pays us a small referral fee. After we cover hosting and essential costs, we donate the rest. You never pay anything.') }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('Do I need an account to claim a gift?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('Nope. Just enter your email and we\'ll send you a confirmation link. That\'s it.') }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('Can I add items from any store?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('Pretty much any online store works. Paste the product URL, we\'ll fetch the details. If it doesn\'t work for a specific store, let us know.') }}"
            }
        },
        {
            "@@type": "Question",
            "name": "{{ __('How do I share my wishlist?') }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ __('Each wishlist has its own link. Copy it and send it however you want - email, WhatsApp, text message, carrier pigeon. Whatever works.') }}"
            }
        }
    ]
}
</script>
@endpush
