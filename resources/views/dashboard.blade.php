@extends('layouts.app')

@section('title', __('Dashboard'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $currentLocale = $shell['currentLocale'];
    $emptyDashboard = $lists->isNotEmpty() && $lists->sum('gifts_count') === 0;
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
    <x-dynamic-component
        :component="$lists->sum('gifts_count') === 0 ? 'gift-flow-shell' : 'app-shell'"
        :title="__('Your GiveTwice HQ')"
        :sidebar-items="$shell['sidebarItems']"
        :stats="$shell['sidebarStats']"
    >
        <section>
            <x-app-content
                :title="$emptyDashboard ? __('Add your first wish') : __('Welcome back, :name!', ['name' => auth()->user()->name])"
                :description="$emptyDashboard ? __('Paste a shop link, or just type your wish.') : __('Your wishlists, gift groups, and settings are all here.')"
            >
                @unless($emptyDashboard)
                <x-slot:actions>
                    <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-primary whitespace-nowrap">
                        <x-icons.plus class="w-4 h-4" />
                        {{ __('Create List') }}
                    </a>
                </x-slot:actions>
                @endunless

                @php $highlightedSlug = request('list-added'); @endphp

                @if($lists->count() > 1)
                    <form method="GET" action="{{ route('dashboard.locale', ['locale' => $currentLocale]) }}" class="mb-6 max-w-lg flex flex-col gap-2">
                        <label for="selected-list" class="form-label mb-0">{{ __('Choose a list') }}</label>
                        <div class="flex items-stretch gap-2">
                            <select id="selected-list" name="list" class="form-select min-h-11 min-w-0 flex-1" onchange="this.form.requestSubmit()">
                                @foreach($lists as $option)
                                    <option value="{{ $option->id }}" @selected($selectedListId === $option->id)>{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn-secondary min-h-11">{{ __('View') }}</button>
                        </div>
                    </form>
                @endif
                <div class="space-y-0 divide-y divide-gray-100">
                    @forelse($lists->where('id', $selectedListId) as $list)
                        @php $isHighlighted = $highlightedSlug === $list->slug; @endphp
                        <section
                            id="list-{{ $list->slug }}"
                            class="list-section {{ ! $loop->first || $isHighlighted ? 'pt-8' : '' }} {{ ! $loop->last || $isHighlighted ? 'pb-8' : '' }}{{ $isHighlighted ? ' px-4 -mx-4 rounded-2xl highlight-sunny-pulse' : '' }}"
                            @if($isHighlighted)
                                x-data
                                x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            @endif
                        >
                            @if($list->gifts->isEmpty())
                                <div class="max-w-lg">
                                    @unless($emptyDashboard)
                                        <h2 class="text-xl font-semibold">{{ __('Add your first wish') }}</h2>
                                        <p class="mt-2 text-gray-600">{{ __('Paste a shop link, or just type your wish.') }}</p>
                                    @endunless
                                    <form method="POST" action="{{ route('gifts.store', ['locale' => $currentLocale]) }}" class="flex flex-col gap-3">
                                        @csrf
                                        <input type="hidden" name="list_id" value="{{ $list->id }}">
                                        <label class="sr-only" for="wish-{{ $list->id }}">{{ __('Link or wish') }}</label>
                                        <input id="wish-{{ $list->id }}" name="input" required maxlength="2048" value="{{ old('input') }}" placeholder="{{ __('Link or wish') }}" class="form-input">
                                        @error('input')<p class="form-error">{{ $message }}</p>@enderror
                                        <button class="btn-primary justify-center">{{ __('Add Gift') }}</button>
                                    </form>
                                    <details class="mt-4 text-sm text-gray-600">
                                        <summary class="cursor-pointer py-2">{{ __('Try these too') }}</summary>
                                        <x-gift-examples />
                                    </details>
                                </div>
                            @endif
                            <div class="my-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-lg font-bold text-gray-900">{{ $list->name }}</h2>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                                    </p>
                                </div>
                                <div class="{{ $list->gifts->isEmpty() ? 'grid grid-cols-2 gap-x-2 gap-y-1' : 'flex flex-wrap items-center gap-2' }}">
                                    <a href="{{ url('/' . $currentLocale . '/list/' . $list->slug . '/edit') }}" class="{{ $list->gifts->isEmpty() ? 'inline-flex min-h-11 items-center gap-2 py-2 text-sm text-gray-600 hover:text-gray-900' : 'btn-secondary' }}" title="{{ __('Edit') }}">
                                        <x-icons.edit class="w-5 h-5" />
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    <x-collaborators-button :list="$list" :show-label="true" :quiet="$list->gifts->isEmpty()" />
                                    <x-share-modal :list="$list" :show-label="true" :quiet="$list->gifts->isEmpty()" />
                                    <a href="{{ url('/' . $currentLocale . '/gifts/create') }}?list={{ $list->id }}" class="{{ $list->gifts->isEmpty() ? 'inline-flex min-h-11 items-center gap-2 py-2 text-sm text-gray-600 hover:text-gray-900' : 'btn-primary' }}">
                                        <x-icons.plus class="w-5 h-5" />
                                        {{ __('Drop a hint') }}
                                    </a>
                                </div>
                            </div>

                            @unless($list->spoiler_mode)
                                <p class="mb-4 text-sm text-gray-500">{{ __('Surprise protected. You cannot see what is reserved.') }}</p>
                            @endunless
                            @if($list->gifts->isNotEmpty())
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                    @foreach($list->gifts as $gift)
                                        <x-gift-card :gift="$gift" :editable="true" :show-reservations="$list->spoiler_mode" />
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
                @if($emptyDashboard)
                    <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-link mt-6 text-sm">{{ __('Create List') }}</a>
                @endif
            </x-app-content>
        </section>
    </x-dynamic-component>
</div>
@endsection
