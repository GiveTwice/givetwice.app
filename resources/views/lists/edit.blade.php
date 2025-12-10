@extends('layouts.app')

@section('title', __('Edit List'))

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-gray-700">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="hover:text-gray-700">{{ $list->name }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('Edit') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('Edit List') }}</h1>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-xl">
    <form action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $list->name) }}" required
                class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror"
                placeholder="{{ __('e.g., Birthday 2025, Christmas Wishlist') }}">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
            <textarea name="description" id="description" rows="3"
                class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                placeholder="{{ __('Optional description for your list') }}">{{ old('description', $list->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('List Type') }} *</label>
            <div class="space-y-2">
                <label class="flex items-start gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="filter_type" value="manual" {{ old('filter_type', $list->filter_type) === 'manual' ? 'checked' : '' }}
                        class="mt-1">
                    <div>
                        <span class="font-medium">{{ __('Manual') }}</span>
                        <p class="text-sm text-gray-500">{{ __('Manually add gifts to this list') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="filter_type" value="all" {{ old('filter_type', $list->filter_type) === 'all' ? 'checked' : '' }}
                        class="mt-1">
                    <div>
                        <span class="font-medium">{{ __('All Gifts') }}</span>
                        <p class="text-sm text-gray-500">{{ __('Automatically includes all your gifts') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50 opacity-50">
                    <input type="radio" name="filter_type" value="criteria" {{ old('filter_type', $list->filter_type) === 'criteria' ? 'checked' : '' }}
                        class="mt-1" disabled>
                    <div>
                        <span class="font-medium">{{ __('Criteria-based') }}</span>
                        <p class="text-sm text-gray-500">{{ __('Filter gifts by price range, etc.') }} ({{ __('Coming soon') }})</p>
                    </div>
                </label>
            </div>
            @error('filter_type')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $list->is_public) ? 'checked' : '' }}
                    class="rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">{{ __('Make this list public') }}</span>
            </label>
            <p class="text-sm text-gray-500 ml-6">{{ __('Public lists can be shared with others') }}</p>
        </div>

        @if($list->is_default)
            <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm text-blue-800">{{ __('This is your default list. New gifts will be added here automatically.') }}</p>
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                {{ __('Save Changes') }}
            </button>
            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>

@unless($list->is_default)
<div class="bg-white rounded-lg shadow p-6 max-w-xl mt-6">
    <h3 class="text-lg font-semibold text-red-600 mb-2">{{ __('Danger Zone') }}</h3>
    <p class="text-gray-600 text-sm mb-4">{{ __('Once you delete a list, there is no going back. Gifts in this list will not be deleted.') }}</p>
    <form action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" method="POST"
          onsubmit="return confirm('{{ __('Are you sure you want to delete this list?') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            {{ __('Delete List') }}
        </button>
    </form>
</div>
@endunless
@endsection
