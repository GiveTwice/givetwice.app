@extends('layouts.app')

@section('title', __('Edit List'))

@section('robots', 'noindex, nofollow')

@section('content')
<x-app-content
    :title="__('Edit List')"
    :description="__('Update the details of your list.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Edit :name', ['name' => $list->name])]
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
                </div>
            </div>
        </div>
    </div>
</x-app-content>

<x-danger-zone
    id="delete-list"
    :description="__('Once you delete a list, there is no going back. Gifts in this list will not be deleted.')"
    :buttonText="__('Delete List')"
    :modalTitle="__('Delete List')"
    :modalMessage="__('Are you sure you want to delete this list? This action cannot be undone. Gifts in this list will not be deleted.')"
    :action="url('/' . app()->getLocale() . '/list/' . $list->slug)"
/>
@endsection
