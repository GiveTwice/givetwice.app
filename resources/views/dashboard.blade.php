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

        <div class="space-y-0 divide-y divide-gray-100">
        @forelse($lists as $list)
            <section id="list-{{ $list->id }}" class="list-section {{ !$loop->first ? 'pt-8' : '' }} {{ !$loop->last ? 'pb-8' : '' }}">

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
                            {{ __('Add a Gift') }}
                        </a>
                    </div>
                </div>

                @if($list->gifts->isEmpty())
                    <div class="bg-cream-50 rounded-xl p-8 text-center">
                        <p class="text-gray-500 mb-4">{{ __('No gifts in this list yet.') }}</p>
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-link">
                            <x-icons.plus class="w-4 h-4" />
                            {{ __('Add your first gift') }}
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
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('You don\'t have any lists yet.') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Create your first wishlist to get started.') }}</p>
                    <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-primary">
                        {{ __('Create your first list') }}
                    </a>
                </div>
            </div>
        @endforelse
        </div>
    </x-app-content>

    @if($lists->isNotEmpty())
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-cream-200/60 p-6">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Need another list?') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Create lists for different occasions or recipients.') }}</p>
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
