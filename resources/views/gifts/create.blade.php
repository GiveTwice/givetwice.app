@extends('layouts.app')

@section('title', __('Add Gift'))

@section('robots', 'noindex, nofollow')

@section('content')
<x-app-content
    :title="__('Add Gift')"
    :description="__('Paste a link or describe what you\'d like.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Add Gift')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts') }}">
                @csrf

                @error('list_id')
                    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
                        <p class="form-error mt-0">{{ $message }}</p>
                    </div>
                @enderror

                <div class="mb-6">
                    <label for="input" class="form-label">
                        {{ __('Gift') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="input"
                        name="input"
                        value="{{ old('input') }}"
                        required
                        autofocus
                        placeholder="{{ __('https://www.bol.com/nl/... or describe your gift') }}"
                        class="form-input @error('input') border-red-500 @enderror"
                    >
                    @error('input')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help mt-2">{{ __('Paste a product link and we\'ll fill in the details automatically, or just type what you\'d like.') }}</p>
                </div>

                @if($isSingleListMode)
                    <input type="hidden" name="list_id" value="{{ $selectedListId }}">
                @else
                    <div class="mb-6">
                        <label for="list_id" class="form-label">
                            {{ __('Add to list') }}
                        </label>
                        <select id="list_id" name="list_id" class="form-select">
                            @foreach($lists as $list)
                                <option value="{{ $list->id }}" {{ $list->id === $selectedListId ? 'selected' : '' }}>
                                    {{ $list->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-cancel text-center">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary justify-center">
                        <x-icons.plus class="w-5 h-5" />
                        {{ __('Add Gift') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('How does this work?') }}</h2>

                <div class="space-y-4">

                    <div class="flex gap-3">
                        <div class="icon-circle bg-coral-100 text-coral-600 text-sm font-semibold">1</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Got a link?') }}</p>
                            <p class="form-help mt-0">{{ __("Paste it and we'll grab the title, image, and price automatically.") }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-teal-100 text-teal-600 text-sm font-semibold">2</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('No link? No problem') }}</p>
                            <p class="form-help mt-0">{{ __('Just describe what you\'d like — "contribution to my bike", "a good book", anything.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-sunny-200 text-sunny-700 text-sm font-semibold">3</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Review & tweak') }}</p>
                            <p class="form-help mt-0">{{ __('Add a description, price, or image on the next page.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-cream-200">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="icon-circle bg-teal-100 text-teal-600">
                            <x-icons.checkmark class="w-4 h-4" />
                        </div>
                        <p class="text-gray-600">{{ __('Works with pretty much any store.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-content>
@endsection
