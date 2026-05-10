@extends('layouts.app')

@section('title', __('Dashboard'))

@section('robots', 'noindex, nofollow')

@section('content')

<div
    x-data="{
        init() {
            if (window.Echo) {
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('.gift.fetch.completed', (e) => {
                        this.updateGiftCard(e.gift);
                    });
            }
        },
        updateGiftCard(gift) {
            const card = document.querySelector(`[data-gift-id='${gift.id}']`);
            if (!card) return;

            // Update image - remove placeholder or existing img, add new img
            const imgContainer = card.querySelector('[data-gift-image]');
            if (imgContainer && gift.image_url_card) {
                // Remove existing placeholder if present
                const placeholder = imgContainer.querySelector('[data-gift-placeholder]');
                if (placeholder) {
                    placeholder.remove();
                }
                // Remove existing img if present (in case of re-fetch)
                const existingImg = imgContainer.querySelector('img');
                if (existingImg) {
                    existingImg.remove();
                }
                // Create and insert new image as first child
                const img = document.createElement('img');
                img.src = gift.image_url_card;
                img.alt = gift.title || '';
                img.className = 'w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500';
                img.loading = 'lazy';
                imgContainer.insertBefore(img, imgContainer.firstChild);
            }

            // Update title
            const titleEl = card.querySelector('[data-gift-title]');
            if (titleEl) {
                titleEl.textContent = gift.title || '{{ __('Untitled gift') }}';
                titleEl.title = gift.title || '';
            }

            // Update price
            const priceEl = card.querySelector('[data-gift-price]');
            if (priceEl && gift.price_formatted) {
                priceEl.textContent = '';
                const priceSpan = document.createElement('span');
                priceSpan.className = 'text-lg font-bold text-coral-600';
                priceSpan.textContent = gift.price_formatted;
                priceEl.appendChild(priceSpan);
            }

            // Remove fetching badge on completion
            const badgeEl = card.querySelector('[data-gift-badge]');
            if (badgeEl && gift.fetch_status === 'completed') {
                badgeEl.remove();
            }
        }
    }"
>

    <x-app-content
        :title="__('Welcome back, :name!', ['name' => auth()->user()->name])"
    >

        @php $highlightedSlug = request('list-added'); @endphp
        <div class="space-y-0 divide-y divide-gray-100">
        @forelse($lists as $list)
            @php $isHighlighted = $highlightedSlug === $list->slug; @endphp
            <section
                id="list-{{ $list->slug }}"
                class="list-section {{ !$loop->first || $isHighlighted ? 'pt-8' : '' }} {{ !$loop->last || $isHighlighted ? 'pb-8' : '' }}{{ $isHighlighted ? ' px-4 -mx-4 rounded-2xl scroll-mt-12 highlight-sunny-pulse' : '' }}"
                @if($isHighlighted)
                    x-data
                    x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                @endif
            >

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-900">{{ $list->name }}</h2>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                        </p>
                    </div>
                    <div class="flex items-center flex-wrap gap-2">
                        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit') }}" class="btn-secondary" title="{{ __('Edit') }}">
                            <x-icons.edit class="w-5 h-5" />
                            <span class="hidden sm:inline">{{ __('Edit') }}</span>
                        </a>
                        <x-collaborators-button :list="$list" />
                        <x-share-modal :list="$list" />
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-primary">
                            <x-icons.plus class="w-5 h-5" />
                            {{ __('Drop a hint') }}
                        </a>
                    </div>
                </div>

                @if($list->gifts->isEmpty())
                    <div class="bg-cream-50 rounded-xl p-8 text-center">
                        <p class="text-gray-500 mb-4">{{ __('This list is wide open. Time to fill it.') }}</p>
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-link">
                            <x-icons.plus class="w-4 h-4" />
                            {{ __('Drop your first hint') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($list->gifts as $gift)
                            <x-gift-card :gift="$gift" :editable="true" />
                        @endforeach
                    </div>
                @endif
            </section>
        @empty
            <div class="py-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-coral-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">&#127873;</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No wishlists yet. Not even one?') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Every great gift starts with a great hint.') }}</p>
                    <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-primary">
                        {{ __('Create your first wishlist') }}
                    </a>
                </div>
            </div>
        @endforelse
        </div>
    </x-app-content>

    {{-- Gift exchanges section --}}
    @php
        $exchangeSlugs = ['en' => 'secret-santa', 'nl' => 'lootjes-trekken', 'fr' => 'tirage-au-sort'];
        $exchangeSlug = $exchangeSlugs[app()->getLocale()] ?? 'secret-santa';
        $exchangeLabels = ['en' => __('Secret Santa'), 'nl' => __('Lootjes trekken'), 'fr' => __('Tirage au sort')];
        $exchangeLabel = $exchangeLabels[app()->getLocale()] ?? __('Secret Santa');
    @endphp

    <div class="mt-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">🎲 {{ $exchangeLabel }}</h2>
            <a href="{{ route('exchanges.landing', ['locale' => app()->getLocale(), 'exchangeType' => $exchangeSlug]) }}" class="btn-primary-sm">
                <x-icons.plus class="w-4 h-4" />
                {{ __('New group') }}
            </a>
        </div>

        @if($exchanges->isEmpty())
            <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-6 border border-coral-100">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="text-4xl">🎲</div>
                    <div class="flex-1 text-center sm:text-left">
                        <p class="font-semibold text-gray-900">{{ $exchangeLabel }}</p>
                        <p class="text-gray-600 text-sm">{{ __('Draw names. Buy gifts. Donate to charity. All without anyone knowing who got whom.') }}</p>
                    </div>
                    <a href="{{ route('exchanges.landing', ['locale' => app()->getLocale(), 'exchangeType' => $exchangeSlug]) }}" class="btn-primary whitespace-nowrap">
                        {{ __('Start a group') }}
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($exchanges as $exchange)
                <a href="{{ route('exchanges.status', ['locale' => app()->getLocale(), 'exchange' => $exchange->slug]) }}" class="block bg-white rounded-xl border border-cream-200 p-4 hover:border-coral-200 hover:shadow-sm transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $exchange->name }}</p>
                            <div class="flex gap-3 mt-1 text-sm text-gray-500">
                                @if($exchange->event_date)
                                    <span>📅 {{ $exchange->event_date->format('M j, Y') }}</span>
                                @endif
                                <span>👥 {{ $exchange->participants_count }}</span>
                                @if($exchange->formatBudget())
                                    <span>💰 {{ $exchange->formatBudget() }}</span>
                                @endif
                            </div>
                        </div>
                        @if($exchange->isDrawn())
                            <span class="badge badge-success">{{ __('Names drawn') }}</span>
                        @else
                            <span class="badge badge-warning">{{ __('Draft') }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($lists->isNotEmpty())
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-cream-200/60 p-6">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Want another wishlist?') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __("Birthdays, holidays, 'just because' — make a list for each.") }}</p>
                </div>
                <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-secondary whitespace-nowrap">
                    <x-icons.plus class="w-4 h-4" />
                    {{ __('Create List') }}
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
