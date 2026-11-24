@php
    use App\Enums\SupportedCurrency;
@endphp

@extends('layouts.app')

@section('title', __('Edit Gift'))

@section('robots', 'noindex, nofollow')

@section('hide_install_banner', '1')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user(), $returnList);
    $dashboardUrl = $shell['dashboardUrl'];
    $cancelUrl = $returnList ? route('list.show', ['locale' => app()->getLocale(), 'list' => $returnList]) : $dashboardUrl;
    $listQuery = $returnList ? '?list=' . $returnList->id : '';
    $breadcrumbs = collect([['label' => __('Lists'), 'url' => $dashboardUrl]])
        ->when($returnList, fn ($crumbs) => $crumbs->push(['label' => $returnList->name, 'url' => $cancelUrl]))
        ->push(['label' => __('Edit Gift')])
        ->all();
    $giftContext = session('gift_context') ?? request('context');
    $isGiftFlow = $giftContext || session('first_gift') === $gift->id;
    [$pageTitle, $pageDescription] = match (true) {
        $giftContext === 'fetch_success' => [__('Found it! Does this look right?'), null],
        $giftContext === 'fetch_failed', $gift->isFetchFailed() => [
            __(':shop is keeping the door shut', ['shop' => ucfirst(explode('.', $gift->siteName() ?? '')[0])]),
            __("We couldn't get the photo and price. Can you fill them in? We'll keep the link."),
        ],
        $giftContext === 'manual_entry' => [__('Fill in the details for your gift.'), null],
        $giftContext === 'fetch_timeout' => [__('Edit Gift'), __('Still fetching in the background. We never overwrite what you fill in.')],
        default => [__('Edit Gift'), __('Tweak the details.')],
    };
@endphp

<x-app-shell
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
<div class="space-y-8">
<x-app-content
    :title="$pageTitle"
    :description="$pageDescription"
    :breadcrumbs="$breadcrumbs"
    :full-width="true"
>
    <form
        method="POST"
        action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id) . $listQuery }}"
        x-data="giftEdit()"
        @input="markEdited($event.target.name)"
        @submit="submitting = true"
        @pageshow.window="submitting = false"
        @paste.window="pasteImage($event)"
        @dragenter.window="dragEnter($event)"
        @dragover.window="dragOver($event)"
        @dragleave.window="dragLeave($event)"
        @drop.window="dropImage($event)"
        class="[&_:is(input,textarea,select)]:scroll-mb-24"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="tracks_edits" value="0" :value="1">
        <template x-for="field in edited" :key="field"><input type="hidden" name="edited_fields[]" :value="field"></template>

        <div class="grid grid-cols-1 gap-8 p-4 sm:px-6 sm:py-5 lg:grid-cols-5">

            <div class="space-y-6 lg:col-span-3">
                <div>
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

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-5">
                    <div class="sm:col-span-3">
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
                                    class="h-full pl-3 pr-8 py-2.5 border border-r-0 border-stone-300 rounded-l-[10px] bg-gray-50 text-gray-700 font-medium focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 focus:z-10 appearance-none cursor-pointer transition-colors hover:bg-gray-100"
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
                                class="flex-1 min-w-0 px-3 py-2.5 border border-stone-300 rounded-r-[10px] focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors @error('price') border-red-500 @enderror"
                            >
                        </div>
                        @error('price')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        @error('currency')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shop_name" class="form-label">{{ __('Shop') }}</label>
                        <input id="shop_name" name="shop_name" x-model="form.shop_name" class="form-input" maxlength="255">
                        @error('shop_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
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
                    <div class="flex items-center justify-between mt-2" x-show="form.url" x-cloak>
                        <a
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
                            class="text-sm text-gray-400"
                        >
                            {{ __("We'll re-fetch the details when you save") }}
                        </span>
                    </div>
                </div>

                <div>
                    <label for="owner_note" class="form-label">{{ __('Note for the buyer (optional)') }}</label>
                    <textarea id="owner_note" name="owner_note" rows="2" class="form-textarea field-sizing-content min-h-20" maxlength="1500" placeholder="{{ __('E.g. gold, size S...') }}">{{ old('owner_note', $gift->owner_note) }}</textarea>
                    @error('owner_note')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="form-label">
                        {{ __('Description (optional)') }}
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        x-model="form.description"
                        placeholder="{{ __('Brief description of the gift') }}"
                        class="form-textarea field-sizing-content min-h-20 @error('description') border-red-500 @enderror"
                    ></textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4 border-t border-gray-100 pt-5">
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="most_wanted" value="0">
                        <input
                            type="checkbox"
                            name="most_wanted"
                            id="most_wanted"
                            value="1"
                            @checked(old('most_wanted', $gift->most_wanted))
                            class="mt-[0.1875rem] w-4 h-4 shrink-0 text-teal-500 border-cream-300 rounded focus:ring-teal-400"
                        >
                        <label for="most_wanted" class="form-label mb-0 cursor-pointer">
                            {{ __('Most wanted') }}
                        </label>
                    </div>
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="allow_multiple_claims"
                            id="allow_multiple_claims"
                            value="1"
                            x-model="form.allow_multiple_claims"
                            class="mt-[0.1875rem] w-4 h-4 shrink-0 text-teal-500 border-cream-300 rounded focus:ring-teal-400"
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
                </div>
            </div>

            <div class="lg:col-span-2">
                <p class="form-label">{{ __('Photo') }}</p>

                <input
                    type="file"
                    id="image-upload"
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="hidden"
                    x-ref="imageInput"
                    x-on:change="uploadImage($event)"
                >

                <div class="group relative aspect-square w-full rounded-2xl border border-stone-200 bg-white overflow-hidden cursor-pointer"
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

                    <div class="absolute inset-0 rounded-2xl border-2 border-dashed border-coral-500 bg-coral-50/90 flex items-center justify-center pointer-events-none"
                         x-show="dragging && !uploading" x-cloak>
                        <span class="bg-white text-coral-600 px-4 py-2 rounded-full text-sm font-semibold shadow-lg inline-flex items-center gap-2">
                            <x-icons.image-placeholder class="w-4 h-4" />
                            {{ __('Drop it here') }}
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
                        <span class="bg-teal-500 text-white px-3 py-1.5 rounded-full text-sm font-semibold shadow-lg inline-flex items-center gap-1.5">
                            <x-icons.check-circle class="w-4 h-4" />
                            {{ __('Saved') }}
                        </span>
                    </div>
                </div>

                <div class="mt-2.5">
                    <button type="button" class="btn-secondary" x-on:click="$refs.imageInput.click()" :disabled="uploading">
                        <x-icons.image-placeholder class="w-4 h-4" />
                        {{ __('Change photo') }}
                    </button>
                </div>

                <p class="form-help">{{ __('Paste a screenshot, drop a photo here or choose one') }}</p>
            </div>
        </div>

        <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6">
            <a href="{{ $cancelUrl }}" class="btn-cancel">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="btn-primary flex-1 sm:flex-none disabled:cursor-wait disabled:opacity-75" :disabled="submitting">
                <x-icons.checkmark class="w-5 h-5" x-show="!submitting" />
                <x-icons.spinner class="w-5 h-5" x-show="submitting" x-cloak />
                <span x-text="submitting ? @js(__('Saving...')) : @js(__('Save'))">{{ __('Save') }}</span>
            </button>
        </div>
    </form>
</x-app-content>

@unless($isGiftFlow)
<x-danger-zone
    id="delete-gift"
    :description="__('Deleting a gift is permanent.')"
    :buttonText="__('Delete Gift')"
    :modalTitle="__('Delete Gift')"
    :modalMessage="__('This can\'t be undone. Sure?')"
    :action="url('/' . app()->getLocale() . '/gifts/' . $gift->id) . $listQuery"
>
    <x-slot:preview>
        <x-gift-preview :gift="$gift" variant="compact" :show-link="false" />
    </x-slot:preview>
</x-danger-zone>
@endunless
</div>
</x-app-shell>

<script>
    function giftEdit() {
        return {
            edited: @js(old('edited_fields', [])),
            markEdited(name) {
                const field = name === 'price' ? 'price_in_cents' : name;
                if (['title', 'description', 'price_in_cents', 'currency', 'shop_name'].includes(field) && !this.edited.includes(field)) this.edited.push(field);
            },
            dragging: false,
            dragDepth: 0,
            uploadFirstImage(files) {
                const file = Array.from(files || []).find(file => file.type.startsWith('image/'));
                if (file) this.uploadImage({ target: { files: [file], value: '' } });
                return Boolean(file);
            },
            pasteImage(event) {
                if (this.uploadFirstImage(event.clipboardData?.files)) event.preventDefault();
            },
            draggingFiles(event) {
                return Array.from(event.dataTransfer?.types || []).includes('Files');
            },
            dragEnter(event) {
                if (!this.draggingFiles(event)) return;
                this.dragDepth++;
                this.dragging = true;
            },
            dragOver(event) {
                if (this.draggingFiles(event)) event.preventDefault();
            },
            dragLeave(event) {
                if (!this.draggingFiles(event)) return;
                this.dragDepth = Math.max(0, this.dragDepth - 1);
                if (this.dragDepth === 0) this.dragging = false;
            },
            dropImage(event) {
                if (!this.draggingFiles(event)) return;
                event.preventDefault();
                this.dragDepth = 0;
                this.dragging = false;
                if (!this.uploading) this.uploadFirstImage(event.dataTransfer.files);
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
            submitting: false,

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
                        alert(data.message || @js(__('Failed to upload image.')));
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
                    alert(@js(__('Failed to upload image.')));
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
                                this.gift.fetched_at = @js(__('Just now'));

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
