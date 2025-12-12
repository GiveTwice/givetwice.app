@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('content')
<x-app-content
    :title="__('Edit Gift')"
    :description="__('Update the details of your gift.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Edit Gift')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- Form Section --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
                @csrf
                @method('PUT')

                {{-- Product URL --}}
                <div class="mb-6">
                    <label for="url" class="form-label">
                        {{ __('Product URL') }}
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url', $gift->url) }}"
                        placeholder="https://www.bol.com/nl/p/..."
                        class="form-input @error('url') border-red-500 @enderror"
                    >
                    @error('url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div class="mb-6">
                    <label for="title" class="form-label">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $gift->title) }}"
                        placeholder="{{ __('Product name') }}"
                        class="form-input @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label for="description" class="form-label">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="{{ __('Brief description of the gift') }}"
                        class="form-textarea @error('description') border-red-500 @enderror"
                    >{{ old('description', $gift->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price --}}
                <div class="mb-6">
                    <label for="price" class="form-label">
                        {{ __('Price') }}
                    </label>
                    <div class="flex">
                        {{-- Currency selector --}}
                        <div class="relative">
                            <select
                                id="currency"
                                name="currency"
                                aria-label="{{ __('Currency') }}"
                                class="h-full pl-4 pr-8 py-3 border border-r-0 border-gray-200 rounded-l-xl bg-gray-50 text-gray-700 font-medium focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 focus:z-10 appearance-none cursor-pointer transition-colors hover:bg-gray-100"
                            >
                                <option value="EUR" {{ old('currency', $gift->currency ?? 'EUR') === 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                <option value="USD" {{ old('currency', $gift->currency ?? 'EUR') === 'USD' ? 'selected' : '' }}>$ USD</option>
                            </select>
                            <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        {{-- Price input --}}
                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="{{ old('price', $gift->getPriceAsDecimal()) }}"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="flex-1 min-w-0 px-4 py-3 border border-gray-200 rounded-r-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
                        >
                    </div>
                    @error('price')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    @error('currency')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons - aligned right --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-cancel">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Section with real-time updates --}}
        <div
            class="lg:col-span-2"
            x-data="{
                gift: {
                    id: {{ $gift->id }},
                    title: @js($gift->title),
                    image_url: @js($gift->image_url),
                    fetch_status: @js($gift->fetch_status),
                    fetched_at: @js($gift->fetched_at?->diffForHumans())
                },
                refreshing: false,
                async refresh() {
                    this.refreshing = true;
                    try {
                        const response = await fetch('{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/refresh') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        if (response.ok) {
                            this.gift.fetch_status = 'pending';
                        }
                    } finally {
                        this.refreshing = false;
                    }
                },
                init() {
                    if (window.Echo) {
                        window.Echo.private('user.{{ auth()->id() }}')
                            .listen('.gift.fetch.completed', (e) => {
                                if (e.gift.id === this.gift.id) {
                                    this.gift.title = e.gift.title;
                                    this.gift.image_url = e.gift.image_url;
                                    this.gift.fetch_status = e.gift.fetch_status;
                                    this.gift.fetched_at = '{{ __('Just now') }}';
                                }
                            });
                    }
                }
            }"
        >
            {{-- Current Image --}}
            <div class="bg-cream-50 rounded-xl p-6 mb-6" x-show="gift.image_url" x-cloak>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Image') }}</h2>
                <img :src="gift.image_url" :alt="gift.title" class="w-full h-48 object-cover rounded-xl">
            </div>

            {{-- Fetch Status --}}
            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Fetch Status') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('We automatically fetch the product image, description, and price from the URL in the background.') }}</p>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Status') }}</span>
                        <div class="flex items-center gap-2">
                            {{-- Pending --}}
                            <span x-show="gift.fetch_status === 'pending'" class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full"></span>
                                {{ __('Pending') }}
                            </span>
                            {{-- Fetching --}}
                            <span x-show="gift.fetch_status === 'fetching'" class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full animate-pulse"></span>
                                {{ __('Fetching') }}
                            </span>
                            {{-- Completed --}}
                            <span x-show="gift.fetch_status === 'completed'" x-cloak class="badge badge-success">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Completed') }}
                            </span>
                            {{-- Failed --}}
                            <span x-show="gift.fetch_status === 'failed'" x-cloak class="badge badge-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('Failed') }}
                            </span>

                            @if(auth()->user()->is_admin)
                                <button
                                    type="button"
                                    x-on:click="refresh()"
                                    :disabled="refreshing"
                                    title="{{ __('Re-fetch details') }}"
                                    class="p-1.5 text-gray-400 hover:text-coral-600 hover:bg-white rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div x-show="gift.fetched_at" class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Last fetched') }}</span>
                        <span class="text-gray-900 font-medium" x-text="gift.fetched_at"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-content>

{{-- Danger Zone - outside the main canvas --}}
<div class="mt-8 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-200/60 p-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-red-600">{{ __('Danger Zone') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('Once you delete a gift, there is no going back.') }}</p>
        </div>
        <button
            type="button"
            x-on:click="$dispatch('open-confirm-delete-gift')"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium whitespace-nowrap"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ __('Delete Gift') }}
        </button>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<x-confirm-modal
    id="delete-gift"
    :title="__('Delete Gift')"
    :message="__('Are you sure you want to delete this gift? This action cannot be undone.')"
    :confirmText="__('Delete Gift')"
>
    <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ __('Delete Gift') }}
        </button>
    </form>
</x-confirm-modal>
@endsection
