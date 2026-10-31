@extends('layouts.app')

@section('title', $list->name . ' - ' . $list->creator->name)

@section('robots', 'noindex, nofollow')

@section('description', trans_choice('og.description', $gifts->total(), ['name' => $list->creator->name, 'count' => $gifts->total()]))
@section('dynamic_og_image', true)
<x-og-image view="public.list-og-image" :data="['list' => $list, 'gifts' => $gifts]" />

@section('content')
@php
    $reserved = fn ($gift) => $showReservations && ! $gift->allow_multiple_claims && $gift->claims->isNotEmpty();
    $sections = [$gifts->reject($reserved), $gifts->filter($reserved)];
    $occasionLabels = ['sinterklaas' => __('Sinterklaas'), 'christmas' => __('Christmas'), 'birthday' => __('Birthday'), 'other' => __('Other')];
@endphp
<div x-data="publicList({ slug: @js($list->slug), showReservations: @js($showReservations) })">
    <header class="mb-3 flex flex-col gap-1.5">
        <h1 class="text-xl font-bold text-gray-900 wrap-anywhere sm:text-3xl">{{ $list->name }}</h1>
        <p class="text-xs text-gray-500">{{ __('Created by :name', ['name' => $list->creator->name]) }}</p>
        @if($list->occasion_date)
            <p class="text-xs text-gray-600">
                <span class="inline-flex flex-wrap gap-1 rounded-full bg-sunny-100 px-3 py-1">
                    {{ $occasionLabels[$list->occasion] ?? __('Occasion') }} · {{ $list->occasion_date->translatedFormat('j F') }}
                    @unless($isOwner)
                        · {{ $list->occasion_date->isToday() ? __('Today') : ($list->occasion_date->isFuture() ? trans_choice(':count day to go|:count days to go', (int) now()->startOfDay()->diffInDays($list->occasion_date), ['count' => (int) now()->startOfDay()->diffInDays($list->occasion_date)]) : __('Past occasion')) }}
                    @endunless
                </span>
            </p>
        @endif
        @if($showReservations)
            <div data-reservation-progress class="text-xs text-gray-600">
                <div class="flex flex-wrap justify-between gap-1">
                    <span>{{ __(':free of :total still free', ['free' => $availableGifts, 'total' => $totalGifts]) }}</span>
                    @unless($isOwner)<span>{{ __(':name cannot see who buys what', ['name' => $list->creator->name]) }}</span>@endunless
                </div>
                <div class="mt-1 h-1 overflow-hidden rounded-full bg-cream-200"><div class="h-full bg-teal-500" style="width: {{ $totalGifts ? 100 * $availableGifts / $totalGifts : 0 }}%"></div></div>
            </div>
            <nav aria-label="{{ __('Filter gifts') }}" class="flex flex-wrap gap-2 text-xs">
                @foreach(['all' => __('All'), 'free' => __('Still free'), 'under20' => __('Under €20')] as $value => $label)
                    <a href="{{ $list->getPublicUrl() . ($value === 'all' ? '' : '?filter=' . $value) }}" @if($filter === $value) aria-current="true" @endif class="rounded-full border px-3 py-1.5 {{ $filter === $value ? 'border-coral-500 bg-coral-50 text-coral-700' : 'border-cream-300 text-gray-600' }}">{{ $label }}</a>
                @endforeach
            </nav>
        @else
            <p class="rounded-lg bg-white px-3 py-2 text-xs text-gray-600">{{ __('Surprise protected. You cannot see what is reserved.') }}</p>
        @endif
        @if($isOwner)
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a class="btn-primary" href="{{ route('gifts.create', ['locale' => app()->getLocale(), 'list' => $list->id]) }}">{{ __('Add Gift') }}</a>
                <a class="text-coral-600" href="{{ route('list.edit', ['locale' => app()->getLocale(), 'list' => $list]) }}">{{ __('Edit List') }}</a>
                <x-share-modal :list="$list" />
            </div>
        @endif
    </header>
    @foreach($sections as $section)
        @if($loop->last && $section->isNotEmpty())
            <h2 class="mb-3 mt-6 text-sm font-semibold text-gray-500" data-reserved-heading>{{ __('Already reserved (:count)', ['count' => $section->count()]) }}</h2>
        @endif
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5" data-gift-grid>
            @foreach($section as $gift)
                <x-shared-gift-tile :gift="$gift" :is-owner="$isOwner" :is-reserved="$reserved($gift)" />
            @endforeach
        </div>
    @endforeach
    @if($gifts->isEmpty())<p class="py-8 text-gray-500" data-empty-state>{{ __('No gifts yet') }}</p>@endif
    @foreach($gifts as $gift)
        <x-gift-modal :gift="$gift" :is-owner="$isOwner" :show-reservations="$showReservations" />
    @endforeach
    {{ $gifts->links() }}
    @auth
        @unless($isOwner)
            <div class="mt-6" x-data="followButton({ following: @js($isFollowing), slug: @js($list->slug), locale: @js(app()->getLocale()) })">
                <button type="button" x-on:click="toggle()" :disabled="loading" class="btn-secondary" x-text="following ? @js(__('Unfollow')) : @js(__('Follow'))"></button>
            </div>
        @endunless
    @endauth
<div class="mt-16 relative overflow-hidden">

    <div class="absolute inset-0 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iNCIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>

    <div class="relative px-6 sm:px-10 py-10 sm:py-12">
        <div class="max-w-3xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">

                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white/90 text-sm font-medium mb-4">
                        <span>&#10084;&#65039;</span>
                        {{ __('Free. No ads.') }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ __('Want your own wishlist?') }}</h2>
                    <p class="text-coral-100 text-lg">{{ __('If you later buy through our shop links, all our commission will go to charity. We are setting that up now.') }} <a href="{{ route('transparency', ['locale' => app()->getLocale()]) }}" class="underline underline-offset-2">{{ __('Transparency') }}</a></p>
                </div>

                <div class="flex-shrink-0 text-center lg:text-left">
                    @guest
                        @if(config('app.allow_registration'))
                            <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <span>{{ __('Make your own wishlist') }}</span>
                                <x-icons.arrow-right class="w-5 h-5" />
                            </a>
                            <p class="mt-3 text-coral-200 text-sm">{{ __('Free. No ads.') }}</p>
                        @else
                            <a href="{{ url('/' . app()->getLocale() . '/contact') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-coral-600 rounded-xl hover:bg-coral-50 font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <span>{{ __('Get in touch') }}</span>
                                <x-icons.arrow-right class="w-5 h-5" />
                            </a>
                            <p class="mt-3 text-coral-200 text-sm">{{ __('Coming soon') }}</p>
                        @endif
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
</div>

<div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-500">
    <div class="flex items-center gap-2">
        <x-icons.shield-check class="w-5 h-5 text-teal-500" />
        <span>{{ __('Privacy protected') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <x-icons.dollar-circle class="w-5 h-5 text-teal-500" />
        <span>{{ __('100% free to use') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <x-icons.heart class="w-5 h-5 text-coral-500" />
        <span>{{ __('Our charity plan') }}</span>
    </div>
</div>

</div>
@endsection
