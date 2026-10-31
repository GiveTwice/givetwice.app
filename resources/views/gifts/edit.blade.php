@php
    use App\Enums\SupportedCurrency;
@endphp

@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $dashboardUrl = $shell['dashboardUrl'];
    $giftContext = session('gift_context') ?? request('context');
    $isGiftFlow = $giftContext || session('first_gift') === $gift->id;
@endphp

<x-dynamic-component
    :component="$isGiftFlow ? 'gift-flow-shell' : 'app-shell'"
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
<div class="space-y-8">
<x-dynamic-component
    :component="$isGiftFlow ? 'gift-flow-shell' : 'app-content'"
    :title="__('Edit Gift')"
    :description="__('Tweak the details.')"
    :breadcrumbs="[
        ['label' => __('Lists'), 'url' => $dashboardUrl],
        ['label' => __('Edit Gift')]
    ]"
>
    @if($giftContext === 'fetch_success')
        <h2 class="mb-6 text-xl font-semibold">{{ __('Found it! Does this look right?') }}</h2>
    @elseif($giftContext === 'fetch_failed' || $gift->isFetchFailed())
        <h2 class="mb-2 text-xl font-semibold">{{ __(':shop is keeping the door shut', ['shop' => ucfirst(explode('.', $gift->siteName() ?? '')[0])]) }}</h2>
        <p class="mb-6 text-gray-600">{{ __("We couldn't get the photo and price. Can you fill them in? We'll keep the link.") }}</p>
    @elseif($giftContext === 'manual_entry')
        <h2 class="mb-6 text-xl font-semibold">{{ __('Fill in the details for your gift.') }}</h2>
    @elseif($giftContext === 'fetch_timeout')
        <p class="mb-6 text-gray-600">{{ __('Still fetching in the background. We never overwrite what you fill in.') }}</p>
    @endif

    <div
        class="grid grid-cols-1 gap-8 lg:grid-cols-5"
        x-data="giftEdit()"
        @paste.window="pasteImage($event)"
    >

        <div class="order-last lg:order-first lg:col-span-3">
            <form @input="markEdited($event.target.name)" method="POST" action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="tracks_edits" value="0" :value="1">
                <template x-for="field in edited" :key="field"><input type="hidden" name="edited_fields[]" :value="field"></template>

                <details class="mb-6">
                    <summary class="cursor-pointer text-sm text-gray-500">{{ __('Product URL') }}</summary>
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
                    <div class="flex items-center justify-between mt-2">
                        <a
                            x-show="form.url"
                            :href="form.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-sm text-coral-600 hover:text-coral-700 transition-colors"
                        >
                            {{ __('View gift') }}
                            <x-icons.external-link class="w-4 h-4" />
                        </a>
                        <span
                            x-show="form.url !== originalUrl"
                            x-cloak
                            class="text-xs text-gray-400"
                        >
                            {{ __("We'll re-fetch the details when you save") }}
                        </span>
                    </div>
                </div>
                </details>

                <div class="mb-6">
                    <label for="title" class="form-label">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        required
                        x-model="form.title"
                        placeholder="{{ __('Product name') }}"
                        class="form-input @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="shop_name" class="form-label">{{ __('Shop') }}</label>
                    <input id="shop_name" name="shop_name" x-model="form.shop_name" class="form-input" maxlength="255">
                    @error('shop_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="mb-6">
                    <label for="owner_note" class="form-label">{{ __('Note for the buyer (optional)') }}</label>
                    <textarea id="owner_note" name="owner_note" rows="2" class="form-textarea" maxlength="1500" placeholder="{{ __('E.g. gold, size S...') }}">{{ old('owner_note', $gift->owner_note) }}</textarea>
                    @error('owner_note')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <details class="mb-6">
                    <summary class="cursor-pointer text-sm text-gray-500">{{ __('Description') }}</summary>
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
                </details>

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

                <div class="mb-4">
                    <input type="hidden" name="most_wanted" value="0">
                    <label class="flex items-center gap-2"><input type="checkbox" name="most_wanted" value="1" @checked(old('most_wanted', $gift->most_wanted))> ★ {{ __('Most wanted') }}</label>
                </div>
                <div class="mb-6 flex items-start gap-3 pt-4 border-t border-gray-100">
                        <input
                        type="checkbox"
                        name="allow_multiple_claims"
                        id="allow_multiple_claims"
                        value="1"
                        x-model="form.allow_multiple_claims"
                        class="mt-1 w-4 h-4 text-teal-500 border-cream-300 rounded focus:ring-teal-400"
                    >
                    <div>
                        <label for="allow_multiple_claims" class="form-label mb-0 cursor-pointer">
                            {{ __('Allow multiple claims') }}
                        </label>
                        <p class="form-help mt-1">
                            {{ __('Useful for gift cards or big-ticket items.') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ $dashboardUrl }}" class="btn-cancel text-center">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary justify-center">
                        <x-icons.checkmark class="w-5 h-5" />
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="order-first lg:order-last lg:col-span-2">

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
                                <p class="text-sm">{{ __('Add a photo') }}</p>
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

                    <div class="absolute top-3 right-3 pointer-events-none"
                         x-show="uploadedRecently"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <span class="bg-teal-500 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg inline-flex items-center gap-1.5">
                            <x-icons.check-circle class="w-4 h-4" />
                            {{ __('Saved') }}
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-2 text-center">{{ __('Paste a screenshot or choose a photo') }}</p>
            </div>


        </div>
    </div>
</x-dynamic-component>

@unless($isGiftFlow)
<x-danger-zone
    id="delete-gift"
    :description="__('Deleting a gift is permanent.')"
    :buttonText="__('Delete Gift')"
    :modalTitle="__('Delete Gift')"
    :modalMessage="__('This can\'t be undone. Sure?')"
    :action="url('/' . app()->getLocale() . '/gifts/' . $gift->id)"
/>
@endunless
</div>
</x-dynamic-component>

<script>
    function giftEdit() {
        return {
            edited: @js(old('edited_fields', [])),
            markEdited(name) {
                const field = name === 'price' ? 'price_in_cents' : name;
                if (['title', 'description', 'price_in_cents', 'currency', 'shop_name'].includes(field) && !this.edited.includes(field)) this.edited.push(field);
            },
            pasteImage(event) {
                const file = Array.from(event.clipboardData?.files || []).find(file => file.type.startsWith('image/'));
                if (file) { event.preventDefault(); this.uploadImage({ target: { files: [file], value: '' } }); }
            },
            originalUrl: @js($gift->url),
            form: {
                url: @js(old('url', $gift->url)),
                title: @js(old('title', $gift->title ?: $gift->suggestedTitle())),
                shop_name: @js(old('shop_name', $gift->shop_name ?? $gift->siteName())),
                description: @js(old('description', $gift->description)),
                price: @js(old('price', $gift->getPriceAsDecimal())),
                currency: @js(old('currency', $gift->currency ?? \App\Enums\SupportedCurrency::default()->value)),
                allow_multiple_claims: @js((bool) old('allow_multiple_claims', $gift->allow_multiple_claims))
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
            uploadedRecently: false,

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

                    const data = await response.json();

                    if (!response.ok) {
                        alert(data.message || '{{ __('Failed to upload image.') }}');
                        return;
                    }

                    if (data.image_url_card) {
                        this.gift.image_url_card = data.image_url_card + (data.image_url_card.includes('?') ? '&' : '?') + 't=' + Date.now();
                    }

                    this.uploadedRecently = true;
                    clearTimeout(this.uploadedTimeout);
                    this.uploadedTimeout = setTimeout(() => { this.uploadedRecently = false; }, 2500);
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
                                if (!this.uploadedRecently && !this.uploading) this.gift.image_url_card = e.gift.image_url_card;
                                this.gift.fetch_status = e.gift.fetch_status;
                                this.gift.fetched_at = '{{ __('Just now') }}';

                                for (const field of ['title', 'description', 'currency']) {
                                    if (!this.edited.includes(field) && e.gift[field]) this.form[field] = e.gift[field];
                                }
                                if (!this.edited.includes('price_in_cents') && e.gift.price_in_cents != null) {
                                    this.form.price = (e.gift.price_in_cents / 100).toFixed(2);
                                }
                            }
                        });
                }
            }
        }
    }
</script>
@endsection
