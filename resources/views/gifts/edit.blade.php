@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-coral-600 transition-colors">{{ __('Dashboard') }}</a>
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
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
                @csrf
                @method('PUT')

                {{-- Product URL --}}
                <div class="mb-6">
                    <label for="url" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Product URL') }}
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url', $gift->url) }}"
                        placeholder="https://www.bol.com/nl/p/..."
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('url') border-red-500 @enderror"
                    >
                    @error('url')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div class="mb-6">
                    <label for="title" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $gift->title) }}"
                        placeholder="{{ __('Product name') }}"
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="{{ __('Brief description of the gift') }}"
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors resize-none @error('description') border-red-500 @enderror"
                    >{{ old('description', $gift->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price --}}
                <div class="mb-6">
                    <label for="price" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Price') }} (EUR)
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="{{ old('price', $gift->price) }}"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
                    >
                    @error('price')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons - aligned right --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-cream-200">
                    <a
                        href="{{ url('/' . app()->getLocale() . '/dashboard') }}"
                        class="px-5 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors"
                    >
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
        <div class="bg-white rounded-2xl border border-red-200 p-6 mt-6">
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
            <div class="bg-white rounded-2xl border border-cream-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Image') }}</h3>
                <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-full h-48 object-cover rounded-xl border border-cream-200">
            </div>
        @endif

        {{-- Fetch Status --}}
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Fetch Status') }}</h3>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Status') }}</span>
                    @if($gift->isPending())
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-sunny-700 bg-sunny-100 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 bg-sunny-500 rounded-full"></span>
                            {{ __('Pending') }}
                        </span>
                    @elseif($gift->isFetching())
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-sunny-700 bg-sunny-100 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 bg-sunny-500 rounded-full animate-pulse"></span>
                            {{ __('Fetching') }}
                        </span>
                    @elseif($gift->isFetched())
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-700 bg-teal-100 px-3 py-1 rounded-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Completed') }}
                        </span>
                    @elseif($gift->isFetchFailed())
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-red-700 bg-red-100 px-3 py-1 rounded-full">
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
