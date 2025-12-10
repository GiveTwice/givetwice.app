@extends('layouts.app')

@section('title', __('Add Gift'))

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-gray-700">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('Add Gift') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('Add Gift') }}</h1>
    <p class="text-gray-600">{{ __('Paste a product URL and we\'ll fetch the details automatically.') }}</p>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts') }}">
        @csrf

        <div class="mb-4">
            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Product URL') }} <span class="text-red-500">*</span>
            </label>
            <input
                type="url"
                id="url"
                name="url"
                value="{{ old('url') }}"
                required
                placeholder="https://example.com/product"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('url') border-red-500 @enderror"
            >
            @error('url')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="list_id" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Add to list') }}
            </label>
            <select
                id="list_id"
                name="list_id"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                @foreach($lists as $list)
                    <option value="{{ $list->id }}" {{ $list->is_default ? 'selected' : '' }}>
                        {{ $list->name }}{{ $list->is_default ? ' (' . __('Default') . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <details class="mb-6">
            <summary class="cursor-pointer text-sm text-gray-600 hover:text-gray-900">
                {{ __('Optional: Add details manually') }}
            </summary>

            <div class="mt-4 space-y-4 pl-4 border-l-2 border-gray-200">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="{{ __('Product name') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="{{ __('Brief description of the gift') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
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
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror"
                    >
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </details>

        <div class="flex gap-3">
            <button
                type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            >
                {{ __('Add Gift') }}
            </button>
            <a
                href="{{ url('/' . app()->getLocale() . '/dashboard') }}"
                class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200"
            >
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
