@php
    use App\Enums\SupportedCurrency;
@endphp

@extends('layouts.app')

@section('title', __('Add Gift'))

@section('content')
<x-app-content
    :title="__('Add Gift')"
    :description="__('Paste a product URL and we\'ll fetch the details automatically.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Add Gift')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts') }}">
                @csrf

                <div class="mb-6">
                    <label for="url" class="form-label">
                        {{ __('Product URL') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url') }}"
                        required
                        autofocus
                        placeholder="https://www.bol.com/nl/p/..."
                        class="form-input @error('url') border-red-500 @enderror"
                    >
                    @error('url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                @if(!$isSingleListMode)
                    <div class="mb-6">
                        <label for="list_id" class="form-label">
                            {{ __('Add to list') }}
                        </label>
                        <select id="list_id" name="list_id" class="form-select">
                            @foreach($lists as $list)
                                <option value="{{ $list->id }}" {{ $list->id === $selectedListId ? 'selected' : '' }}>
                                    {{ $list->name }}{{ $list->is_default ? ' (' . __('Default') . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <details class="mb-6 group" @if($errors->hasAny(['title', 'description', 'price', 'currency'])) open @endif>
                    <summary class="cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <x-icons.chevron-right class="w-4 h-4 transition-transform group-open:rotate-90" />
                        {{ __('Optional: Add details manually') }}
                    </summary>

                    <div class="mt-4 space-y-4 pl-6 border-l-2 border-gray-100">
                        <div>
                            <label for="title" class="form-label">
                                {{ __('Title') }}
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="{{ __('Product name') }}"
                                class="form-input @error('title') border-red-500 @enderror"
                            >
                            @error('title')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="form-label">
                                {{ __('Description') }}
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="{{ __('Brief description of the gift') }}"
                                class="form-textarea @error('description') border-red-500 @enderror"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="form-label">
                                {{ __('Price') }}
                            </label>
                            <div class="flex">

                                <div class="relative">
                                    <select
                                        id="currency"
                                        name="currency"
                                        aria-label="{{ __('Currency') }}"
                                        class="h-full pl-4 pr-8 py-3 border border-r-0 border-gray-200 rounded-l-xl bg-gray-50 text-gray-700 font-medium focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 focus:z-10 appearance-none cursor-pointer transition-colors hover:bg-gray-100"
                                    >
                                        @foreach (SupportedCurrency::cases() as $currency)
                                            <option value="{{ $currency->value }}" {{ old('currency', $defaultCurrency) === $currency->value ? 'selected' : '' }}>
                                                {{ $currency->displayOption() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                                        <x-icons.chevron-down class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    value="{{ old('price') }}"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="flex-1 min-w-0 px-4 py-3 border border-gray-200 rounded-r-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
                                >
                            </div>
                            @error('price')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            @error('currency')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </details>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-cancel">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary">
                        <x-icons.plus class="w-5 h-5" />
                        {{ __('Add Gift') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('How do I find a product URL?') }}</h2>

                <div class="space-y-4">

                    <div class="flex gap-3">
                        <div class="icon-circle bg-coral-100 text-coral-600 text-sm font-semibold">1</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Find the product') }}</p>
                            <p class="form-help mt-0">{{ __('Go to the product page in your favorite online store.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-teal-100 text-teal-600 text-sm font-semibold">2</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Copy the URL') }}</p>
                            <p class="form-help mt-0">{{ __('Copy the URL from your browser\'s address bar.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="icon-circle bg-sunny-200 text-sunny-700 text-sm font-semibold">3</div>
                        <div>
                            <p class="text-gray-700 font-medium">{{ __('Paste it here') }}</p>
                            <p class="form-help mt-0">{{ __('Paste the URL in the field and we\'ll fetch the details.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-cream-200">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="icon-circle bg-teal-100 text-teal-600">
                            <x-icons.checkmark class="w-4 h-4" />
                        </div>
                        <p class="text-gray-600">{{ __('Works with any online store!') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-content>
@endsection
