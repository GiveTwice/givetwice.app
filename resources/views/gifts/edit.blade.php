@php
    use App\Enums\SupportedCurrency;
@endphp

@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('content')
<x-app-content
    :title="__('Edit Gift')"
    :description="__('Update the details of your gift.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Edit Gift')]
    ]"
>
    <div
        class="grid grid-cols-1 lg:grid-cols-5 gap-8"
        x-data="giftEdit()"
    >

        <div class="lg:col-span-3">
            <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="url" class="form-label">
                        {{ __('Product URL') }}
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        x-model="form.url"
                        placeholder="https://www.bol.com/nl/p/..."
                        class="form-input @error('url') border-red-500 @enderror"
                    >
                    @error('url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="title" class="form-label">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        x-model="form.title"
                        placeholder="{{ __('Product name') }}"
                        class="form-input @error('title') border-red-500 @enderror"
                    >
                    @error('title')
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
                        x-model="form.description"
                        placeholder="{{ __('Brief description of the gift') }}"
                        class="form-textarea @error('description') border-red-500 @enderror"
                    ></textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="price" class="form-label">
                        {{ __('Price') }}
                    </label>
                    <div class="flex">

                        <div class="relative">
                            <select
                                id="currency"
                                name="currency"
                                x-model="form.currency"
                                aria-label="{{ __('Currency') }}"
                                class="h-full pl-4 pr-8 py-3 border border-r-0 border-gray-200 rounded-l-xl bg-gray-50 text-gray-700 font-medium focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 focus:z-10 appearance-none cursor-pointer transition-colors hover:bg-gray-100"
                            >
                                @foreach (SupportedCurrency::cases() as $currency)
                                    <option value="{{ $currency->value }}">
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
                            x-model="form.price"
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

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="btn-cancel">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary">
                        <x-icons.checkmark class="w-5 h-5" />
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">

            <div class="bg-cream-50 rounded-xl p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Image') }}</h2>

                <input
                    type="file"
                    id="image-upload"
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="hidden"
                    x-ref="imageInput"
                    x-on:change="uploadImage($event)"
                >

                <div class="group relative w-full h-48 rounded-xl overflow-hidden cursor-pointer"
                     x-on:click="$refs.imageInput.click()">

                    <template x-if="gift.image_url_card">
                        <img :src="gift.image_url_card" :alt="gift.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </template>

                    <template x-if="!gift.image_url_card">
                        <div class="w-full h-full bg-cream-100 flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <x-icons.image-placeholder class="w-12 h-12 mx-auto mb-2" />
                                <p class="text-sm">{{ __('No image yet') }}</p>
                            </div>
                        </div>
                    </template>

                    <div class="absolute inset-0 bg-gray-900/0 group-hover:bg-gray-900/20 transition-colors duration-300 flex items-center justify-center"
                         x-show="!uploading">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/95 backdrop-blur-sm text-gray-700 px-4 py-2 rounded-full text-sm font-medium shadow-lg inline-flex items-center gap-2">
                            <x-icons.image-placeholder class="w-4 h-4" />
                            {{ __('Upload image') }}
                        </span>
                    </div>

                    <div class="absolute inset-0 bg-gray-900/40 flex items-center justify-center"
                         x-show="uploading" x-cloak>
                        <span class="bg-white/95 backdrop-blur-sm text-gray-700 px-4 py-2 rounded-full text-sm font-medium shadow-lg inline-flex items-center gap-2">
                            <x-icons.spinner class="w-4 h-4 animate-spin" />
                            {{ __('Uploading...') }}
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-2 text-center">{{ __('Click to upload a new image') }}</p>
            </div>

            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Fetch Status') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('We automatically fetch the product image, description, and price from the URL in the background.') }}</p>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Status') }}</span>
                        <div class="flex items-center gap-2">

                            <span x-show="gift.fetch_status === 'pending'" class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full"></span>
                                {{ __('Pending') }}
                            </span>

                            <span x-show="gift.fetch_status === 'fetching'" class="badge badge-warning">
                                <span class="w-2 h-2 bg-sunny-500 rounded-full animate-pulse"></span>
                                {{ __('Fetching') }}
                            </span>

                            <span x-show="gift.fetch_status === 'completed'" x-cloak class="badge badge-success">
                                <x-icons.checkmark class="w-4 h-4" />
                                {{ __('Completed') }}
                            </span>

                            <span x-show="gift.fetch_status === 'failed'" x-cloak class="badge badge-danger">
                                <x-icons.close class="w-4 h-4" />
                                {{ __('Failed') }}
                            </span>

                            @if(auth()->user()->is_admin)
                                <button
                                    type="button"
                                    x-on:click="refresh()"
                                    :disabled="refreshing"
                                    title="{{ __('Re-fetch details') }}"
                                    class="p-1.5 text-gray-400 hover:text-coral-600 hover:bg-white rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <x-icons.refresh class="w-4 h-4" :class="{ 'animate-spin': refreshing }" />
                                </button>
                            @endif
                        </div>
                    </div>

                    <div x-show="gift.fetched_at" class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Last fetched') }}</span>
                        <span class="text-gray-900 font-medium" x-text="gift.fetched_at"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-content>

<div class="mt-8 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-200/60 p-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-red-600">{{ __('Danger Zone') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('Once you delete a gift, there is no going back.') }}</p>
        </div>
        <button
            type="button"
            x-on:click="$dispatch('open-confirm-delete-gift')"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium whitespace-nowrap"
        >
            <x-icons.trash class="w-5 h-5" />
            {{ __('Delete Gift') }}
        </button>
    </div>
</div>

<x-confirm-modal
    id="delete-gift"
    :title="__('Delete Gift')"
    :message="__('Are you sure you want to delete this gift? This action cannot be undone.')"
    :confirmText="__('Delete Gift')"
>
    <form method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
        >
            <x-icons.trash class="w-5 h-5" />
            {{ __('Delete Gift') }}
        </button>
    </form>
</x-confirm-modal>

<script>
    function giftEdit() {
        return {
            form: {
                url: @js(old('url', $gift->url)),
                title: @js(old('title', $gift->title)),
                description: @js(old('description', $gift->description)),
                price: @js(old('price', $gift->getPriceAsDecimal())),
                currency: @js(old('currency', $gift->currency ?? \App\Enums\SupportedCurrency::default()->value))
            },
            gift: {
                id: {{ $gift->id }},
                title: @js($gift->title),
                image_url_card: @js($gift->getImageUrl('card')),
                fetch_status: @js($gift->fetch_status),
                fetched_at: @js($gift->fetched_at?->diffForHumans())
            },
            refreshing: false,
            uploading: false,

            async refresh() {
                this.refreshing = true;
                try {
                    const response = await fetch('{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/refresh') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    if (response.ok) {
                        this.gift.fetch_status = 'pending';
                    }
                } finally {
                    this.refreshing = false;
                }
            },

            async uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploading = true;

                const formData = new FormData();
                formData.append('image', file);

                try {
                    const response = await fetch('{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/upload-image') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        const data = await response.json();
                        alert(data.message || '{{ __('Failed to upload image.') }}');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('{{ __('Failed to upload image.') }}');
                } finally {
                    this.uploading = false;
                    event.target.value = '';
                }
            },

            init() {
                if (window.Echo) {
                    window.Echo.private('user.{{ auth()->id() }}')
                        .listen('.gift.fetch.completed', (e) => {
                            if (e.gift.id === this.gift.id) {
                                // Update sidebar display
                                this.gift.title = e.gift.title;
                                this.gift.image_url_card = e.gift.image_url_card;
                                this.gift.fetch_status = e.gift.fetch_status;
                                this.gift.fetched_at = '{{ __('Just now') }}';

                                // Update form fields with fetched data
                                if (e.gift.url) this.form.url = e.gift.url;
                                if (e.gift.title) this.form.title = e.gift.title;
                                if (e.gift.description) this.form.description = e.gift.description;
                                if (e.gift.price_in_cents) this.form.price = (e.gift.price_in_cents / 100).toFixed(2);
                                if (e.gift.currency) this.form.currency = e.gift.currency;
                            }
                        });
                }
            }
        }
    }
</script>
@endsection
