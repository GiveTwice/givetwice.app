@extends('layouts.app')

@section('title', __('Add Gift'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $dashboardUrl = $shell['dashboardUrl'];
@endphp

<x-app-shell
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
<x-app-content
    :title="__('Add Gift')"
    :description="__('Paste a link or describe what you\'d like.')"
    :breadcrumbs="[
        ['label' => __('Lists'), 'url' => $dashboardUrl],
        ['label' => __('Add Gift')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form id="add-gift" method="POST" action="{{ url('/' . app()->getLocale() . '/gifts') }}">
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

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ $dashboardUrl }}" class="btn-cancel text-center">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary justify-center">
                        <x-icons.plus class="w-5 h-5" />
                        {{ __('Add Gift') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-xl bg-cream-50 p-6"><x-gift-examples /></div>
    </div>
</x-app-content>
</x-app-shell>
@endsection
