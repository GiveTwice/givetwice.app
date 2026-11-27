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
<div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm">
    <header class="flex flex-col gap-4 border-b border-gray-100 px-4 pt-5 pb-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between sm:px-6">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 wrap-anywhere sm:text-3xl">
                <x-ribbon-underline class="{{ $colour['underline'] }}"><span class="text-gray-900">{{ $list->name }}</span></x-ribbon-underline>
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                <span>{{ __('Created by :name', ['name' => $list->creator->name]) }}</span>
                @if($list->occasion_date)
                    <span class="inline-flex flex-wrap gap-1 rounded-full bg-sunny-100 px-3 py-1 text-xs font-medium text-sunny-800">
                        {{ $occasionLabels[$list->occasion] ?? __('Occasion') }} · {{ $list->occasion_date->translatedFormat('j F') }}
                        @unless($isOwner)
                            · {{ $list->occasion_date->isToday() ? __('Today') : ($list->occasion_date->isFuture() ? trans_choice(':count day to go|:count days to go', (int) now()->startOfDay()->diffInDays($list->occasion_date), ['count' => (int) now()->startOfDay()->diffInDays($list->occasion_date)]) : __('Past occasion')) }}
                        @endunless
                    </span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($isOwner)
                <a class="btn-secondary" href="{{ route('list.edit', ['locale' => app()->getLocale(), 'list' => $list]) }}"><x-icons.edit class="w-5 h-5" />{{ __('Edit List') }}</a>
                <x-share-modal :list="$list" :show-label="true" />
                <a class="btn-primary" href="{{ route('gifts.create', ['locale' => app()->getLocale(), 'list' => $list->id]) }}"><x-icons.plus class="w-5 h-5" />{{ __('Add Gift') }}</a>
            @else
                @auth
                    <div x-data="followButton({ following: @js($isFollowing), slug: @js($list->slug), locale: @js(app()->getLocale()) })">
                        <button type="button" x-on:click="toggle()" :disabled="loading" class="btn-secondary" x-text="following ? @js(__('Unfollow')) : @js(__('Follow'))"></button>
                    </div>
                @endauth
                <a href="{{ route('reservations.index', ['locale' => app()->getLocale()]) }}" class="btn-secondary">{{ __('All my reservations') }}</a>
            @endif
        </div>
    </header>

    <div class="flex flex-col gap-3 border-b border-gray-100 bg-cream-50 px-4 py-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:px-6">
        @if($showReservations)
            <div data-reservation-progress class="min-w-0 flex-1 text-sm text-gray-700 sm:max-w-md">
                <div class="flex flex-wrap justify-between gap-x-3 gap-y-1">
                    <span class="font-semibold text-gray-900">{{ __(':free of :total still free', ['free' => $availableGifts, 'total' => $totalGifts]) }}</span>
                    @unless($isOwner)<span class="text-gray-600">{{ __(':name cannot see who buys what', ['name' => $list->creator->name]) }}</span>@endunless
                </div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-cream-300"><div class="h-full rounded-full bg-teal-700" style="width: {{ $totalGifts ? 100 * $availableGifts / $totalGifts : 0 }}%"></div></div>
            </div>
            <nav aria-label="{{ __('Filter gifts') }}" class="flex flex-wrap gap-2 text-sm">
                @foreach(['all' => __('All'), 'free' => __('Still free'), 'under20' => __('Under €20')] as $value => $label)
                    <a href="{{ $list->getPublicUrl() . ($value === 'all' ? '' : '?filter=' . $value) }}" @if($filter === $value) aria-current="true" @endif class="rounded-full border px-3.5 py-1.5 font-medium transition-colors duration-150 {{ $filter === $value ? 'border-coral-500 bg-coral-500 text-white' : 'border-stone-300 bg-white text-gray-700 hover:border-coral-300 hover:bg-coral-50' }}">{{ $label }}</a>
                @endforeach
            </nav>
        @else
            <p class="text-sm text-gray-600">{{ __('Surprise protected. You cannot see what is reserved.') }}</p>
        @endif
    </div>

    <div class="p-4 sm:p-6">
    @foreach($sections as $section)
        @php $sortable = $isOwner && $section->count() > 1; @endphp
        <x-sortable-gift-grid :list="$list" :sortable="$sortable" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5" data-gift-grid>
            @foreach($section as $gift)
                <div class="relative" data-sort-id="{{ $gift->id }}" @if($sortable) x-sort:item="{{ $gift->id }}" @endif>
                    <x-shared-gift-tile :gift="$gift" :is-owner="$isOwner" :is-reserved="$reserved($gift)" :is-mine="$myReservationGiftIds->contains($gift->id)" />
                    @if($sortable)<x-gift-sort-handle :gift="$gift" />@endif
                </div>
            @endforeach
        </x-sortable-gift-grid>
    @endforeach
    @if($gifts->isEmpty())<p class="py-12 text-center text-gray-600" data-empty-state>{{ __('No gifts yet') }}</p>@endif
    {{ $gifts->links() }}
    </div>
</div>
    @if($isOwner)
        @foreach($gifts as $gift)<x-gift-modal :gift="$gift" :is-owner="$isOwner" :show-reservations="$showReservations" />@endforeach
    @else
        <div x-data="reservationSheet" x-on:reserve-gift.window="open($event.detail.url)" x-on:reservation-submit="send($event.detail.form)">
            <dialog x-ref="dialog" x-on:cancel="close()" x-on:click="if ($event.target === $el) close()" x-on:close="closed()" class="reservation-dialog fixed inset-x-0 overflow-hidden bottom-0 top-auto m-0 mx-auto w-full max-w-md rounded-t-2xl bg-white p-0 text-gray-900 shadow-xl sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 sm:rounded-2xl">
                <div class="overflow-x-hidden overflow-y-auto overscroll-contain p-4" style="max-height: inherit" x-on:touchstart.passive="touchY = $event.touches[0].clientY" x-on:touchend.passive="if ($event.changedTouches[0].clientY - touchY > 90 && $el.scrollTop === 0) close()">
                    <div class="mb-2 flex items-center justify-between"><span></span><span class="h-1 w-10 rounded-full bg-cream-300"></span><button type="button" x-on:click="close()" class="px-2 text-2xl" aria-label="{{ __('Close') }}">×</button></div>
                    <p x-show="error" x-text="error" role="alert" class="text-sm text-red-700"></p>
                    <div x-ref="content" class="min-h-48"></div>
                </div>
                @include('claims.busy-overlay')
            </dialog>
        </div>
    @endif
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
