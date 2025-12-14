@extends('layouts.app')

@section('title', __('Create List'))

@section('content')
<x-app-content
    :title="__('Create List')"
    :description="__('Create a new wishlist for a specific occasion or recipient.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Create List')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form action="{{ url('/' . app()->getLocale() . '/lists') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="name" class="form-label">
                        {{ __('Name') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="{{ __('e.g., Birthday 2025, Christmas Wishlist') }}"
                        class="form-input @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="{{ __('Optional description for your list') }}"
                        class="form-textarea @error('description') border-red-500 @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-cancel">
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

        <div class="lg:col-span-2">
            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('About Lists') }}</h2>

                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="icon-circle bg-coral-100 text-coral-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Organize your wishes') }}</p>
                            <p class="form-help mt-0">{{ __('Create separate lists for different occasions like birthdays, holidays, or events.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-sunny-200 text-sunny-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Share with different people') }}</p>
                            <p class="form-help mt-0">{{ __('Each list has its own shareable link, so you can send different lists to different people.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-teal-100 text-teal-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Keep it simple') }}</p>
                            <p class="form-help mt-0">{{ __('Most people only need one list. Create more only if you need them!') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-content>
@endsection
