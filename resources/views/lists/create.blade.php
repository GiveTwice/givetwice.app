@extends('layouts.app')

@section('title', __('Create List'))

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-coral-600 transition-colors">{{ __('Dashboard') }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-medium">{{ __('Create List') }}</span>
</div>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Create List') }}</h1>
    <p class="text-gray-600 mt-1">{{ __('Create a new wishlist for a specific occasion or recipient.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Form Section --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <form action="{{ url('/' . app()->getLocale() . '/lists') }}" method="POST">
                @csrf

                {{-- Name --}}
                <div class="mb-6">
                    <label for="name" class="block text-gray-700 mb-2 font-medium">
                        {{ __('Name') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
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
                    >{{ old('description') }}</textarea>
                    @error('description')
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Create List') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-cream-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('About Lists') }}</h3>

            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-coral-100 text-coral-600 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Organize your wishes') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Create separate lists for different occasions like birthdays, holidays, or events.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-sunny-200 text-sunny-700 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Share with different people') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Each list has its own shareable link, so you can send different lists to different people.') }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-700 font-medium">{{ __('Keep it simple') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Most people only need one list. Create more only if you need them!') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
