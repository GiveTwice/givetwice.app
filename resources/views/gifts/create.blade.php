@extends('layouts.app')

@section('title', __('Add Gift'))

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-coral-600 transition-colors">{{ __('Dashboard') }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-medium">{{ __('Add Gift') }}</span>
</div>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Add Gift') }}</h1>
    <p class="text-gray-600 mt-1">{{ __('Paste a product URL and we\'ll fetch the details automatically.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Form Section --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts') }}">
                @csrf

                {{-- Product URL --}}
                <div class="mb-6">
                    <label for="url" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Product URL') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url') }}"
                        required
                        placeholder="https://www.bol.com/nl/p/..."
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('url') border-red-500 @enderror"
                    >
                    @error('url')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- List selection - only show if user has multiple lists --}}
                @if(!$isSingleListMode)
                    <div class="mb-6">
                        <label for="list_id" class="block text-gray-700 mb-2 font-medium">
                            {{ __('Add to list') }}
                        </label>
                        <select
                            id="list_id"
                            name="list_id"
                            class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors"
                        >
                            @foreach($lists as $list)
                                <option value="{{ $list->id }}" {{ $list->is_default ? 'selected' : '' }}>
                                    {{ $list->name }}{{ $list->is_default ? ' (' . __('Default') . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Optional details --}}
                <details class="mb-6 group">
                    <summary class="cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        {{ __('Optional: Add details manually') }}
                    </summary>

                    <div class="mt-4 space-y-4 pl-6 border-l-2 border-cream-200">
                        <div>
                            <label for="title" class="block text-gray-700 mb-2 font-medium">
                                {{ __('Title') }}
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="{{ __('Product name') }}"
                                class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('title') border-red-500 @enderror"
                            >
                            @error('title')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-gray-700 mb-2 font-medium">
                                {{ __('Description') }}
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="{{ __('Brief description of the gift') }}"
                                class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors resize-none @error('description') border-red-500 @enderror"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-gray-700 mb-2 font-medium">
                                {{ __('Price') }} (EUR)
                            </label>
                            <input
                                type="number"
                                id="price"
                                name="price"
                                value="{{ old('price') }}"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
                            >
                            @error('price')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </details>

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add Gift') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Helper Section --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('How do I find a product URL?') }}</h3>

            <div class="space-y-4">
                {{-- Step 1 --}}
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Find the product') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Go to the product page in your favorite online store.') }}</p>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Copy the URL') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Copy the URL from your browser\'s address bar.') }}</p>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-sunny-200 text-sunny-700 rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Paste it here') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Paste the URL in the field and we\'ll fetch the details.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Supported stores info --}}
            <div class="mt-6 pt-4 border-t border-cream-200">
                <div class="flex items-center gap-3 text-sm">
                    <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-gray-600">{{ __('Works with any online store!') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
