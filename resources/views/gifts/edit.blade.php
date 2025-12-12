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
                        {{ __('Price') }} ({{ $gift->currency ?? 'EUR' }})
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="{{ old('price', $gift->getPriceAsDecimal()) }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="form-input @error('price') border-red-500 @enderror"
                    >
                    @error('price')
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
        <div class="card border-red-200 mt-6">
            <h3 class="text-lg font-semibold text-red-600 mb-2">{{ __('Danger Zone') }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ __('Once you delete a gift, there is no going back.') }}</p>
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this gift?') }}')">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-xl hover:bg-red-600 transition-colors font-medium"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('Delete Gift') }}
                </button>
            </form>
        </div>
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
