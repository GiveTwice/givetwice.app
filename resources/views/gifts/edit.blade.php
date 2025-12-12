@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('content')
{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="breadcrumb-link">{{ __('Dashboard') }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-medium">{{ __('Edit Gift') }}</span>
</div>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Edit Gift') }}</h1>
    <p class="text-gray-600 mt-1">{{ __('Update the details of your gift.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Form Section --}}
    <div class="lg:col-span-3">
        <div class="card">
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
                                class="h-full pl-4 pr-8 py-3 border border-r-0 border-cream-200 rounded-l-xl bg-cream-50 text-gray-700 font-medium focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 focus:z-10 appearance-none cursor-pointer transition-colors hover:bg-cream-100"
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
                            class="flex-1 min-w-0 px-4 py-3 border border-cream-200 rounded-r-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
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
                <div class="form-actions">
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

        {{-- Danger Zone --}}
        <div class="card border-red-200 mt-6" x-data>
            <h3 class="text-lg font-semibold text-red-600 mb-2">{{ __('Danger Zone') }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ __('Once you delete a gift, there is no going back.') }}</p>
            <button
                type="button"
                x-on:click="$dispatch('open-confirm-delete-gift')"
                class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-xl hover:bg-red-600 transition-colors font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ __('Delete Gift') }}
            </button>
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
                    class="inline-flex items-center gap-2 bg-red-500 text-white px-4 py-2.5 rounded-xl hover:bg-red-600 transition-colors font-medium"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('Delete Gift') }}
                </button>
            </form>
        </x-confirm-modal>
    </div>

    {{-- Info Section --}}
    <div class="lg:col-span-2">
        {{-- Current Image --}}
        @if($gift->image_url)
            <div class="card mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Image') }}</h3>
                <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-full h-48 object-cover rounded-xl border border-cream-200">
            </div>
        @endif

        {{-- Fetch Status --}}
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Fetch Status') }}</h3>
            <p class="form-help mb-4 mt-0">{{ __('We automatically fetch the product image, description, and price from the URL in the background.') }}</p>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Status') }}</span>
                    <div class="flex items-center gap-2">
                        @if($gift->isPending())
                            <span class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full"></span>
                                {{ __('Pending') }}
                            </span>
                        @elseif($gift->isFetching())
                            <span class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full animate-pulse"></span>
                                {{ __('Fetching') }}
                            </span>
                        @elseif($gift->isFetched())
                            <span class="badge badge-success">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Completed') }}
                            </span>
                        @elseif($gift->isFetchFailed())
                            <span class="badge badge-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('Failed') }}
                            </span>
                        @endif

                        @if(auth()->user()->is_admin)
                            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/refresh') }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    title="{{ __('Re-fetch details') }}"
                                    class="p-1.5 text-gray-400 hover:text-coral-600 hover:bg-coral-50 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($gift->fetched_at)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Last fetched') }}</span>
                        <span class="text-gray-900 font-medium">{{ $gift->fetched_at->diffForHumans() }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
