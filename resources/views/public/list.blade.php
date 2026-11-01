@extends('layouts.app')

@section('title', $list->name . ' - ' . $list->creator->name)

@section('robots', 'noindex, nofollow')

@section('description', trans_choice('og.description', $totalGifts, ['name' => $list->creator->name, 'count' => $totalGifts]))
@section('dynamic_og_image', true)
<x-og-image view="public.list-og-image" :data="['list' => $list, 'gifts' => $gifts]" />

@section('content')
@php
    $reserved = fn ($gift) => $showReservations && ! $gift->allow_multiple_claims && $gift->claims->isNotEmpty();
    $sections = [$gifts];
    $occasionLabels = ['sinterklaas' => __('Sinterklaas'), 'christmas' => __('Christmas'), 'birthday' => __('Birthday'), 'other' => __('Other')];
@endphp
<div x-data="publicList({ slug: @js($list->slug), showReservations: @js($showReservations) })">
    <header class="mb-3 flex flex-col gap-1.5">
        <h1 class="text-xl font-bold text-gray-900 wrap-anywhere sm:text-3xl">{{ $list->name }}</h1>
        <p class="text-xs text-gray-700">{{ __('Created by :name', ['name' => $list->creator->name]) }}</p>
        @if($list->occasion_date)
            <p class="text-xs text-gray-700">
                <span class="inline-flex flex-wrap gap-1 rounded-full bg-sunny-100 px-3 py-1">
                    {{ $occasionLabels[$list->occasion] ?? __('Occasion') }} · {{ $list->occasion_date->translatedFormat('j F') }}
                    @unless($isOwner)
                        · {{ $list->occasion_date->isToday() ? __('Today') : ($list->occasion_date->isFuture() ? trans_choice(':count day to go|:count days to go', (int) now()->startOfDay()->diffInDays($list->occasion_date), ['count' => (int) now()->startOfDay()->diffInDays($list->occasion_date)]) : __('Past occasion')) }}
                    @endunless
                </span>
            </p>
        @endif
        @if($showReservations)
            <div data-reservation-progress class="text-xs text-gray-700">
                <div class="flex flex-wrap justify-between gap-1">
                    <span>{{ __(':free of :total still free', ['free' => $availableGifts, 'total' => $totalGifts]) }}</span>
                    @unless($isOwner)<span>{{ __(':name cannot see who buys what', ['name' => $list->creator->name]) }}</span>@endunless
                </div>
                <div class="mt-1 h-1 overflow-hidden rounded-full bg-cream-200"><div class="h-full bg-teal-700" style="width: {{ $totalGifts ? 100 * $availableGifts / $totalGifts : 0 }}%"></div></div>
            </div>
            <nav aria-label="{{ __('Filter gifts') }}" class="flex flex-wrap gap-2 text-xs">
                @foreach(['all' => __('All'), 'free' => __('Still free'), 'under20' => __('Under €20')] as $value => $label)
                    <a href="{{ $list->getPublicUrl() . ($value === 'all' ? '' : '?filter=' . $value) }}" @if($filter === $value) aria-current="true" @endif class="rounded-full border px-3 py-1.5 {{ $filter === $value ? 'border-coral-500 bg-coral-50 text-coral-700' : 'border-gray-500 bg-white text-gray-700' }}">{{ $label }}</a>
                @endforeach
            </nav>
        @else
            <p class="rounded-lg bg-white px-3 py-2 text-xs text-gray-700">{{ __('Surprise protected. You cannot see what is reserved.') }}</p>
        @endif
        @if($isOwner)
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a class="btn-primary" href="{{ route('gifts.create', ['locale' => app()->getLocale(), 'list' => $list->id]) }}">{{ __('Add Gift') }}</a>
                <a class="text-coral-600" href="{{ route('list.edit', ['locale' => app()->getLocale(), 'list' => $list]) }}">{{ __('Edit List') }}</a>
                <x-share-modal :list="$list" />
            </div>
        @endif
        @unless($isOwner)
            <a href="{{ route('reservations.index', ['locale' => app()->getLocale()]) }}" class="btn-link min-h-11 py-2 text-sm">{{ __('All my reservations') }}</a>
        @endunless
    </header>
    @foreach($sections as $section)
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5" data-gift-grid>
            @foreach($section as $gift)
                <div>
                    <x-shared-gift-tile :gift="$gift" :is-owner="$isOwner" :is-reserved="$reserved($gift)" :is-mine="$myReservationGiftIds->contains($gift->id)" />
                    @if($isOwner)<x-gift-order-controls :list="$list" :gift="$gift" :first="$loop->first && $gifts->onFirstPage()" :last="$loop->last && ! $gifts->hasMorePages()" />@endif
                </div>
            @endforeach
        </div>
    @endforeach
    @if($gifts->isEmpty())<p class="py-8 text-gray-700" data-empty-state>{{ __('No gifts yet') }}</p>@endif
    @if($isOwner)
        @foreach($gifts as $gift)<x-gift-modal :gift="$gift" :is-owner="$isOwner" :show-reservations="$showReservations" />@endforeach
    @else
        <div x-data="reservationSheet" x-on:reserve-gift.window="open($event.detail.url)" x-on:reservation-submit="send($event.detail.form)">
            <dialog x-ref="dialog" x-on:cancel="close()" x-on:click="if ($event.target === $el) close()" x-on:close="closed()" class="reservation-dialog fixed inset-x-0 bottom-0 top-auto m-0 mx-auto w-full max-w-md rounded-t-2xl bg-white p-0 text-gray-900 shadow-xl sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 sm:rounded-2xl">
                <div class="overflow-y-auto overscroll-contain p-4" style="max-height: inherit" x-on:touchstart.passive="touchY = $event.touches[0].clientY" x-on:touchend.passive="if ($event.changedTouches[0].clientY - touchY > 90 && $el.scrollTop === 0) close()">
                    <div class="mb-2 flex items-center justify-between"><span></span><span class="h-1 w-10 rounded-full bg-cream-300"></span><button type="button" x-on:click="close()" class="px-2 text-2xl" aria-label="{{ __('Close') }}">×</button></div>
                    <p x-show="busy" role="status">{{ __('Loading...') }}</p>
                    <p x-show="error" x-text="error" role="alert" class="text-sm text-red-700"></p>
                    <div x-ref="content"></div>
                </div>
            </dialog>
        </div>
    @endif
    {{ $gifts->links() }}
    @auth
        @unless($isOwner)
            <div class="mt-6" x-data="followButton({ following: @js($isFollowing), slug: @js($list->slug), locale: @js(app()->getLocale()) })">
                <button type="button" x-on:click="toggle()" :disabled="loading" class="btn-secondary" x-text="following ? @js(__('Unfollow')) : @js(__('Follow'))"></button>
            </div>
        @endunless
    @endauth
<div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-700">
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
