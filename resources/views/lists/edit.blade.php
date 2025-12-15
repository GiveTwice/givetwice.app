@extends('layouts.app')

@section('title', __('Edit List'))

@section('content')
<x-app-content
    :title="__('Edit List')"
    :description="__('Update the details of your list.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => $list->name, 'url' => url('/' . app()->getLocale() . '/list/' . $list->slug)],
        ['label' => __('Edit')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="name" class="form-label">
                        {{ __('Name') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $list->name) }}"
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
                    >{{ old('description', $list->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                @if($list->is_default)
                    <div class="bg-teal-50 border border-teal-100 rounded-xl p-4 mb-6">
                        <div class="flex gap-3">
                            <x-icons.info-circle class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" />
                            <div class="text-sm">
                                <p class="font-medium text-teal-800">{{ __('This is your default list') }}</p>
                                <p class="text-teal-700">{{ __('New gifts will be added here automatically.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="btn-cancel">
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
            <div class="bg-cream-50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('List Info') }}</h2>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Created') }}</span>
                        <span class="text-gray-900 font-medium">{{ $list->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <span class="text-gray-600">{{ __('Shareable link') }}</span>
                        <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}"
                           target="_blank"
                           class="text-sm text-coral-600 hover:text-coral-700 hover:underline break-all bg-white px-3 py-2 rounded-lg flex items-center gap-2 border border-cream-200">
                            <span class="flex-1">{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}</span>
                            <x-icons.external-link class="w-4 h-4 flex-shrink-0" />
                        </a>
                    </div>

                    @if($list->is_default)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Type') }}</span>
                            <span class="badge badge-success">
                                {{ __('Default') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-content>

@unless($list->is_default)
    <div class="mt-8 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-200/60 p-6" x-data>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-red-600">{{ __('Danger Zone') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('Once you delete a list, there is no going back. Gifts in this list will not be deleted.') }}</p>
            </div>
            <button
                type="button"
                x-on:click="$dispatch('open-confirm-delete-list')"
                class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium whitespace-nowrap"
            >
                <x-icons.trash class="w-5 h-5" />
                {{ __('Delete List') }}
            </button>
        </div>
    </div>

    <x-confirm-modal
        id="delete-list"
        :title="__('Delete List')"
        :message="__('Are you sure you want to delete this list? This action cannot be undone. Gifts in this list will not be deleted.')"
        :confirmText="__('Delete List')"
    >
        <form method="POST" action="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
            >
                <x-icons.trash class="w-5 h-5" />
                {{ __('Delete List') }}
            </button>
        </form>
    </x-confirm-modal>
@endunless
@endsection
