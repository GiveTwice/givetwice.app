@extends('layouts.app')

@section('title', __('Edit List'))

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-coral-600 transition-colors">{{ __('Dashboard') }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="hover:text-coral-600 transition-colors">{{ $list->name }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-medium">{{ __('Edit') }}</span>
</div>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Edit List') }}</h1>
    <p class="text-gray-600 mt-1">{{ __('Update the details of your list.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Form Section --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <form action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-6">
                    <label for="name" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Name') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $list->name) }}"
                        required
                        placeholder="{{ __('e.g., Birthday 2025, Christmas Wishlist') }}"
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('name') border-red-500 @enderror"
                    >
                    @error('name')
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
                        placeholder="{{ __('Optional description for your list') }}"
                        class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors resize-none @error('description') border-red-500 @enderror"
                    >{{ old('description', $list->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Default list info --}}
                @if($list->is_default)
                    <div class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-xl">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm">
                                <p class="font-medium text-teal-800">{{ __('This is your default list') }}</p>
                                <p class="text-teal-700">{{ __('New gifts will be added here automatically.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action buttons - aligned right --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-cream-200">
                    <a
                        href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}"
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
        @unless($list->is_default)
            <div class="bg-white rounded-2xl border border-red-200 p-6 mt-6">
                <h3 class="text-lg font-semibold text-red-600 mb-2">{{ __('Danger Zone') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Once you delete a list, there is no going back. Gifts in this list will not be deleted.') }}</p>
                <form action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" method="POST"
                      onsubmit="return confirm('{{ __('Are you sure you want to delete this list?') }}')">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 bg-red-500 text-white px-5 py-2.5 rounded-xl hover:bg-red-600 transition-colors font-medium"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ __('Delete List') }}
                    </button>
                </form>
            </div>
        @endunless
    </div>

    {{-- Info Section --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('List Info') }}</h3>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ __('Created') }}</span>
                    <span class="text-gray-900 font-medium">{{ $list->created_at->diffForHumans() }}</span>
                </div>

                <div class="flex flex-col gap-1.5">
                    <span class="text-gray-600">{{ __('Shareable link') }}</span>
                    <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}"
                       target="_blank"
                       class="text-sm text-coral-600 hover:text-coral-700 hover:underline break-all bg-coral-50 px-3 py-2 rounded-lg flex items-center gap-2">
                        <span class="flex-1">{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}</span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                @if($list->is_default)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Type') }}</span>
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-700 bg-teal-100 px-3 py-1 rounded-full">
                            {{ __('Default') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
