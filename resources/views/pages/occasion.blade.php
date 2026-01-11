@extends('layouts.app')

@section('title', __($data['page_title']))

@section('description', __('meta.occasion.' . $occasion))

@section('content')

@php
    $hero = $data['hero'];
    $heroGifts = $data['hero_gifts'];
    $givetwice = $data['givetwice'];
    $similar = $data['similar'];
    $finalCta = $data['final_cta'];
    $locale = app()->getLocale();
@endphp

<div class="relative overflow-hidden">
    <div class="grid lg:grid-cols-2 gap-8 items-center py-12 lg:py-20">

        <div class="text-left">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-[1.15]">
                <span class="block">{{ __('Create your') }}</span>
                <span class="block text-coral-500">{{ __($hero['h1_subtitle']) }}</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-lg">
                {{ __($hero['description']) }}
            </p>

            <ul class="space-y-3 mb-8">
                @foreach($hero['bullets'] as $bullet)
                    <li class="flex items-center text-gray-700">
                        <span class="text-teal-500 mr-3">&#10003;</span>
                        {{ __($bullet) }}
                    </li>
                @endforeach
            </ul>

            <div>
                @guest
                    @if(config('app.allow_registration'))
                        <a href="{{ route('register', ['locale' => $locale, 'occasion' => $occasion]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                            {{ __($hero['cta_text']) }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('dashboard.locale', ['locale' => $locale]) }}" class="inline-flex items-center justify-center px-8 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold text-lg transition-colors shadow-md hover:shadow-lg">
                        {{ __('Go to my wishlists') }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
                    </a>
                @endguest
            </div>
        </div>

        <x-occasion-hero-card
            :title="__($data['page_title'])"
            :gifts="$heroGifts"
        />
    </div>
</div>

@if(isset($data['why']))
    @php $why = $data['why']; @endphp
    <div class="py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-3 text-center">{{ __($why['title']) }}</h2>
            <p class="text-lg text-gray-600 mb-10 text-center max-w-2xl mx-auto">{{ __($why['subtitle']) }}</p>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach($why['benefits'] as $benefit)
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-cream-200">
                        <div class="w-12 h-12 bg-{{ $benefit['bg'] }}-100 rounded-xl flex items-center justify-center text-2xl mb-4">{!! $benefit['emoji'] !!}</div>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ __($benefit['title']) }}</h3>
                        <p class="text-gray-600 text-sm">{{ __($benefit['description']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@elseif(isset($data['about']))
    @php $about = $data['about']; @endphp
    <div class="py-16 px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __($about['title']) }}</h2>
            <p class="text-lg text-gray-600 leading-relaxed">{{ __($about['text']) }}</p>
        </div>
    </div>
@endif

<x-how-it-works />

<div class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-gradient-to-br {{ $givetwice['gradient'] }} rounded-3xl p-8 lg:p-10 border {{ $givetwice['border'] }}">
            <div class="flex items-start gap-5">
                <div class="shrink-0 w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-3xl">
                    &#10084;&#65039;
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">{{ __($givetwice['title']) }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __($givetwice['description']) }}</p>
                    <a href="{{ route('about', ['locale' => $locale]) }}" class="inline-flex items-center text-{{ $givetwice['link_color'] }}-600 hover:text-{{ $givetwice['link_color'] }}-700 font-medium text-sm">
                        {{ __($givetwice['link_text']) }} <span class="ml-1">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($data['tips']) && isset($data['tips_title']))
    @php $tips = $data['tips']; @endphp
    <div class="bg-cream-50 py-16 px-4 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">{{ __($data['tips_title']) }}</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($tips as $index => $tip)
                    @php
                        $colors = ['coral', 'sunny', 'teal'];
                        $color = $colors[$index % 3];
                    @endphp
                    <div class="bg-white p-5 rounded-xl border border-cream-200">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-8 h-8 bg-{{ $color }}-100 text-{{ $color }}-{{ $color === 'sunny' ? '700' : '600' }} rounded-lg flex items-center justify-center font-bold text-sm">{{ $index + 1 }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ __($tip['title']) }}</h3>
                                <p class="text-gray-600 text-sm">{{ __($tip['description']) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="py-16 px-4">
    <x-similar-occasions
        :occasions="$similar"
        :currentOccasion="$occasion"
    />
</div>

<div class="py-16 px-4 text-center">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __($finalCta['title']) }}</h2>
    <p class="text-xl text-gray-600 mb-8 max-w-xl mx-auto">{{ __($finalCta['subtitle']) }}</p>
    @guest
        @if(config('app.allow_registration'))
            <a href="{{ route('register', ['locale' => $locale, 'occasion' => $occasion]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
                {{ __($finalCta['button_text']) }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
            </a>
        @endif
    @else
        <a href="{{ route('dashboard.locale', ['locale' => $locale]) }}" class="inline-flex items-center px-10 py-4 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-bold text-xl transition-colors shadow-lg hover:shadow-xl">
            {{ __('Go to my wishlists') }} <span class="ml-2">{!! $hero['cta_emoji'] !!}</span>
        </a>
    @endguest
</div>

<div class="text-center pb-8">
    <a href="{{ route('home', ['locale' => $locale]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
        <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
    </a>
</div>
@endsection
