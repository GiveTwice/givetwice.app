@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
{{-- Dashboard with real-time gift updates --}}
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
            if (imgContainer && gift.image_url) {
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
                img.src = gift.image_url;
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

@if($isSingleListMode && $defaultList)
    {{-- SINGLE LIST MODE (90% of users) --}}
    <x-app-content
        :title="__('Welcome back, :name!', ['name' => auth()->user()->name])"
    >
        <x-slot:actions>
            <x-share-modal :list="$defaultList" />
            <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $defaultList->id }}" class="btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add a Gift') }}
            </a>
        </x-slot:actions>

        {{-- Subheader with list info --}}
        <div class="flex items-center gap-3 mb-6 -mt-2">
            <h2 class="text-lg font-semibold text-gray-700">{{ __('Your Gifts') }}</h2>
            <span class="text-sm text-gray-500 bg-cream-100 px-2.5 py-0.5 rounded-full">
                {{ trans_choice(':count gift|:count gifts', $defaultList->gifts_count, ['count' => $defaultList->gifts_count]) }}
            </span>
        </div>

        {{-- Gift grid or empty state --}}
        @if($defaultList->gifts->isEmpty())
            <div class="py-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">&#127873;</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No gifts yet') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Start building your wishlist by adding gifts from any online store.') }}</p>
                    <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $defaultList->id }}" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add Your First Gift') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($defaultList->gifts as $gift)
                    <x-gift-card :gift="$gift" :editable="true" />
                @endforeach
            </div>
        @endif
    </x-app-content>

    {{-- Create additional list CTA - outside the main canvas --}}
    <div class="mt-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-cream-200/60 p-6">
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Want more than one list?') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('You can create additional lists for different occasions or recipients.') }}</p>
            </div>
            <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Another List') }}
            </a>
        </div>
    </div>

@else
    {{-- MULTI-LIST MODE --}}
    <x-app-content
        :title="__('Welcome back, :name!', ['name' => auth()->user()->name])"
    >
        {{-- Lists with their gifts --}}
        <div class="space-y-0 divide-y divide-gray-100">
        @forelse($lists as $list)
            <section id="list-{{ $list->id }}" class="list-section {{ !$loop->first ? 'pt-8' : '' }} {{ !$loop->last ? 'pb-8' : '' }}">
                {{-- List header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}"
                               class="text-lg font-bold text-gray-900 hover:text-coral-600 transition-colors">
                                {{ $list->name }}
                            </a>
                            @if($list->is_default)
                                <span class="badge badge-success text-xs">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-share-modal :list="$list" />
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add a Gift') }}
                        </a>
                    </div>
                </div>

                {{-- Gift grid --}}
                @if($list->gifts->isEmpty())
                    <div class="bg-cream-50 rounded-xl p-8 text-center">
                        <p class="text-gray-500 mb-4">{{ __('No gifts in this list yet.') }}</p>
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add your first gift') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($list->gifts->take(10) as $gift)
                            <x-gift-card :gift="$gift" :editable="true" />
                        @endforeach
                    </div>

                    @if($list->gifts_count > 10)
                        <div class="mt-6 text-center">
                            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}"
                               class="inline-flex items-center gap-2 text-coral-600 hover:text-coral-700 font-medium">
                                {{ __('View all :count gifts', ['count' => $list->gifts_count]) }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endif
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

    {{-- Create new list CTA - outside the main canvas --}}
    @if($lists->isNotEmpty())
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-cream-200/60 p-6">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Need another list?') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Create lists for different occasions or recipients.') }}</p>
                </div>
                <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-secondary whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Create List') }}
                </a>
            </div>
        </div>
    @endif
@endif

</div>{{-- End real-time updates wrapper --}}
@endsection
