@extends('layouts.app')

@section('title', __('Dashboard'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $currentLocale = $shell['currentLocale'];
@endphp

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

            const imgContainer = card.querySelector('[data-gift-image]');
            if (imgContainer && gift.image_url_card) {
                const placeholder = imgContainer.querySelector('[data-gift-placeholder]');
                if (placeholder) {
                    placeholder.remove();
                }

                const existingImg = imgContainer.querySelector('img');
                if (existingImg) {
                    existingImg.remove();
                }

                const img = document.createElement('img');
                img.src = gift.image_url_card;
                img.alt = gift.title || '';
                img.className = 'w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500';
                img.loading = 'lazy';
                imgContainer.insertBefore(img, imgContainer.firstChild);
            }

            const titleEl = card.querySelector('[data-gift-title]');
            if (titleEl) {
                titleEl.textContent = gift.title || '{{ __('Untitled gift') }}';
                titleEl.title = gift.title || '';
            }

            const priceEl = card.querySelector('[data-gift-price]');
            if (priceEl && gift.price_formatted) {
                priceEl.textContent = '';
                const priceSpan = document.createElement('span');
                priceSpan.className = 'text-lg font-bold text-coral-600';
                priceSpan.textContent = gift.price_formatted;
                priceEl.appendChild(priceSpan);
            }

            const badgeEl = card.querySelector('[data-gift-badge]');
            if (badgeEl && gift.fetch_status === 'completed') {
                badgeEl.remove();
            }
        }
    }"
>
    <x-app-shell
        :title="__('Your GiveTwice HQ')"
        :sidebar-items="$shell['sidebarItems']"
        :stats="$shell['sidebarStats']"
    >
        <section>
            <x-app-content
                :title="__('Welcome back, :name!', ['name' => auth()->user()->name])"
                :description="__('Your wishlists, gift groups, and settings are all here.')"
            >
                <x-slot:actions>
                    <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-primary whitespace-nowrap">
                        <x-icons.plus class="w-4 h-4" />
                        {{ __('Create List') }}
                    </a>
                </x-slot:actions>

                @php $highlightedSlug = request('list-added'); @endphp

                <div class="space-y-0 divide-y divide-gray-100">
                    @forelse($lists as $list)
                        @php $isHighlighted = $highlightedSlug === $list->slug; @endphp
                        <section
                            id="list-{{ $list->slug }}"
                            class="list-section {{ ! $loop->first || $isHighlighted ? 'pt-8' : '' }} {{ ! $loop->last || $isHighlighted ? 'pb-8' : '' }}{{ $isHighlighted ? ' px-4 -mx-4 rounded-2xl scroll-mt-12 highlight-sunny-pulse' : '' }}"
                            @if($isHighlighted)
                                x-data
                                x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            @endif
                        >
                            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-lg font-bold text-gray-900">{{ $list->name }}</h2>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ url('/' . $currentLocale . '/list/' . $list->slug . '/edit') }}" class="btn-secondary" title="{{ __('Edit') }}">
                                        <x-icons.edit class="w-5 h-5" />
                                        <span class="hidden sm:inline">{{ __('Edit') }}</span>
                                    </a>
                                    <x-collaborators-button :list="$list" />
                                    <x-share-modal :list="$list" />
                                    <a href="{{ url('/' . $currentLocale . '/gifts/create') }}?list={{ $list->id }}" class="btn-primary">
                                        <x-icons.plus class="w-5 h-5" />
                                        {{ __('Drop a hint') }}
                                    </a>
                                </div>
                            </div>

                            @if($list->gifts->isEmpty())
                                <div class="rounded-xl bg-cream-50 p-8 text-center">
                                    <p class="mb-4 text-gray-500">{{ __('This list is wide open. Time to fill it.') }}</p>
                                    <a href="{{ url('/' . $currentLocale . '/gifts/create') }}?list={{ $list->id }}" class="btn-link">
                                        <x-icons.plus class="w-4 h-4" />
                                        {{ __('Drop your first hint') }}
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                    @foreach($list->gifts as $gift)
                                        <x-gift-card :gift="$gift" :editable="true" />
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @empty
                        <div class="py-16 text-center">
                            <div class="mx-auto max-w-md">
                                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-coral-100">
                                    <span class="text-4xl">&#127873;</span>
                                </div>
                                <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ __('No wishlists yet. Not even one?') }}</h3>
                                <p class="mb-6 text-gray-500">{{ __('Every great gift starts with a great hint.') }}</p>
                                <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-primary">
                                    {{ __('Create your first wishlist') }}
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </x-app-content>
        </section>
    </x-app-shell>
</div>
@endsection
