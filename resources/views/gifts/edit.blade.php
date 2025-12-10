@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-gray-700">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('Edit Gift') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('Edit Gift') }}</h1>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Product URL') }}
            </label>
            <input
                type="url"
                id="url"
                name="url"
                value="{{ old('url', $gift->url) }}"
                placeholder="https://example.com/product"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('url') border-red-500 @enderror"
            >
            @error('url')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Title') }}
            </label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $gift->title) }}"
                placeholder="{{ __('Product name') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
            >
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Description') }}
            </label>
            <textarea
                id="description"
                name="description"
                rows="3"
                placeholder="{{ __('Brief description of the gift') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
            >{{ old('description', $gift->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
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
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror"
            >
            @error('price')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        @if($gift->image_url)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Current Image') }}
                </label>
                <img src="{{ $gift->image_url }}" alt="{{ $gift->title }}" class="w-32 h-32 object-cover rounded border">
            </div>
        @endif

        <div class="mb-6 p-3 bg-gray-50 rounded text-sm text-gray-600">
            <p><strong>{{ __('Fetch Status') }}:</strong>
                @if($gift->isPending())
                    <span class="text-yellow-600">{{ __('Pending') }}</span>
                @elseif($gift->isFetching())
                    <span class="text-yellow-600">{{ __('Fetching') }}</span>
                @elseif($gift->isFetched())
                    <span class="text-green-600">{{ __('Completed') }}</span>
                @elseif($gift->isFetchFailed())
                    <span class="text-red-600">{{ __('Failed') }}</span>
                @endif
            </p>
            @if($gift->fetched_at)
                <p><strong>{{ __('Last fetched') }}:</strong> {{ $gift->fetched_at->diffForHumans() }}</p>
            @endif
        </div>

        <div class="flex gap-3">
            <button
                type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                {{ __('Save Changes') }}
            </button>
            <a
                href="{{ url('/' . app()->getLocale() . '/dashboard') }}"
                class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200"
            >
                {{ __('Cancel') }}
            </a>
        </div>
    </form>

    <hr class="my-6">

    <div>
        <h3 class="text-lg font-medium text-red-600 mb-2">{{ __('Danger Zone') }}</h3>
        <p class="text-sm text-gray-600 mb-3">{{ __('Once you delete a gift, there is no going back.') }}</p>
        <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this gift?') }}')">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                {{ __('Delete Gift') }}
            </button>
        </form>
    </div>
</div>
@endsection
