@extends('layouts.app')

@section('title', $data['page_title'])

@section('description', __('meta.exchange-landing.' . $key))

@section('content')

@php
    $hero       = $data['hero'];
    $heroGifts  = $data['hero_gifts'];
    $why        = $data['why'];
    $givetwice  = $data['givetwice'];
    $tips       = $data['tips'];
    $tipsTitle  = $data['tips_title'];
    $faqs       = $data['faqs'];
    $finalCta   = $data['final_cta'];
    $locale     = $data['locale'];
    $exchangeType = \App\Helpers\ExchangeHelper::exchangeTypeForLocale($locale);
@endphp

{{-- ── HERO ──────────────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden">
    <div class="grid lg:grid-cols-2 gap-8 items-center py-12 lg:py-20">

        <div class="text-left">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-[1.15]">
                <span class="block">{{ $hero['h1_title'] }}</span>
                <span class="block text-coral-500">{{ $hero['h1_subtitle'] }}</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-lg">
                {{ $hero['description'] }}
            </p>

            <ul class="space-y-3 mb-8">
                @foreach($hero['bullets'] as $bullet)
                    <li class="flex items-center text-gray-700">
                        <span class="text-teal-500 mr-3">&#10003;</span>
                        {{ $bullet }}
                    </li>
                @endforeach
            </ul>

            @if(auth()->check() || config('app.allow_registration'))
                <div>
                    <a href="{{ route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType]) }}"
                       class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                        {{ $hero['cta_text'] }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
                    </a>
                </div>
            @endif
        </div>

        <x-occasion-hero-card
            :title="$data['page_title']"
            :gifts="$heroGifts"
        />
    </div>
</div>

{{-- ── WHY SECTION ──────────────────────────────────────────────────────── --}}
<div class="py-16 px-4">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-3 text-center">{{ $why['title'] }}</h2>
        <p class="text-lg text-gray-600 mb-10 text-center max-w-2xl mx-auto">{{ $why['subtitle'] }}</p>

        <div class="grid md:grid-cols-3 gap-6">
            @foreach($why['benefits'] as $benefit)
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                    <div class="w-12 h-12 bg-{{ $benefit['bg'] }}-100 rounded-xl flex items-center justify-center text-2xl mb-4">{!! $benefit['emoji'] !!}</div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $benefit['title'] }}</h3>
                    <p class="text-gray-600 text-sm">{{ $benefit['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── HOW IT WORKS ─────────────────────────────────────────────────────── --}}
<x-how-it-works />

{{-- ── GIVETWICE CHARITY BLOCK ──────────────────────────────────────────── --}}
<div class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-gradient-to-br from-coral-50 to-sunny-50 rounded-3xl p-8 lg:p-10 border border-coral-100">
            <div class="flex items-start gap-5">
                <div class="shrink-0 w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-3xl">
                    &#10084;&#65039;
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">{{ $givetwice['title'] }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ $givetwice['description'] }}</p>
                    <a href="{{ route('about', ['locale' => $locale]) }}"
                       class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium text-sm">
                        {{ $givetwice['link_text'] }} <span class="ml-1">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── TIPS ─────────────────────────────────────────────────────────────── --}}
<div class="bg-cream-50 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">{{ $tipsTitle }}</h2>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($tips as $index => $tip)
                @php
                    $colors = ['coral', 'sunny', 'teal'];
                    $color  = $colors[$index % 3];
                @endphp
                <div class="bg-white p-5 rounded-xl border border-cream-200">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-8 h-8 bg-{{ $color }}-100 text-{{ $color }}-{{ $color === 'sunny' ? '700' : '600' }} rounded-lg flex items-center justify-center font-bold text-sm">{{ $index + 1 }}</div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ $tip['title'] }}</h3>
                            <p class="text-gray-600 text-sm">{{ $tip['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── FAQ ──────────────────────────────────────────────────────────────── --}}
<div class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">{{ __('Frequently asked questions') }}</h2>

        <div class="space-y-4">
            @foreach($faqs as $faq)
                <details class="bg-white border border-cream-200 rounded-xl p-5 group">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center">
                        {{ $faq['question'] }}
                        <span class="text-gray-400 group-open:rotate-180 transition-transform shrink-0 ml-4">&#9660;</span>
                    </summary>
                    <p class="mt-3 text-gray-600 text-sm leading-relaxed">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</div>

{{-- ── FINAL CTA ─────────────────────────────────────────────────────────── --}}
<div class="py-16 px-4 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $finalCta['title'] }}</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-xl mx-auto">{{ $finalCta['subtitle'] }}</p>
    <a href="{{ route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType]) }}"
       class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
        {{ $finalCta['button_text'] }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
    </a>
</div>

<div class="text-center pb-8">
    <a href="{{ route('home', ['locale' => $locale]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
        <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
    </a>
</div>

@endsection

@push('scripts')
<x-breadcrumb-schema :items="[
    ['name' => $data['page_title']]
]" />
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($faqs as $faq)
        {
            "@@type": "Question",
            "name": {{ Js::from($faq['question']) }},
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": {{ Js::from($faq['answer']) }}
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "{{ $data['page_title'] }}",
    "description": "{{ __('meta.exchange-landing.' . $key) }}",
    "provider": {
        "@@type": "Organization",
        "name": "GiveTwice",
        "url": "{{ config('app.url') }}"
    },
    "serviceType": "Gift Exchange Organizer",
    "areaServed": "Worldwide",
    "availableChannel": {
        "@@type": "ServiceChannel",
        "serviceUrl": "{{ url()->current() }}",
        "availableLanguage": ["en", "nl", "fr"]
    }
}
</script>
@endpush
