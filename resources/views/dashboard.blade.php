@extends('layouts.app')

@section('title', __('Dashboard'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $currentLocale = $shell['currentLocale'];
@endphp

<x-app-shell
    :title="__('Your GiveTwice HQ')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
    <x-app-content
        :title="__('Welcome back, :name!', ['name' => auth()->user()->name])"
        :description="__('Your wishlists, gift groups, and settings are all here.')"
    >
        <x-slot:actions>
            <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-secondary whitespace-nowrap">
                <x-icons.plus class="w-4 h-4" />
                {{ __('Create List') }}
            </a>
        </x-slot:actions>

        @if($lists->count() > 1)
            <form method="GET" action="{{ route('dashboard.locale', ['locale' => $currentLocale]) }}" class="mb-5 max-w-lg flex flex-col gap-1.5">
                <label for="selected-list" class="form-label mb-0">{{ __('Choose a list') }}</label>
                <div class="flex items-stretch gap-2">
                    <select id="selected-list" name="list" class="form-select min-h-11 sm:min-h-10 py-0 min-w-0 flex-1" onchange="this.form.requestSubmit()">
                        @foreach($lists as $option)
                            <option value="{{ $option->id }}" @selected($selectedListId === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn-secondary">{{ __('View') }}</button>
                </div>
            </form>
        @endif

        @forelse($lists->where('id', $selectedListId) as $list)
            <section id="list-{{ $list->slug }}" class="list-section">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('list.show', ['locale' => $currentLocale, 'list' => $list]) }}" class="hover:text-coral-600 transition-colors">
                            <x-section-heading>{{ $list->name }}</x-section-heading>
                        </a>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                        </p>
                    </div>
                    <x-owner-list-actions :list="$list" />
                </div>

                <x-owner-list-gifts :list="$list" />
            </section>
        @empty
            <div class="py-16 text-center">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-coral-100">
                        <span class="text-4xl">&#127873;</span>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ __('No wishlists yet. Not even one?') }}</h3>
                    <p class="mb-6 text-gray-500">{{ __('Every great gift starts with a great hint.') }}</p>
                    <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-primary">
                        {{ __('Create your first wishlist') }}
                    </a>
                </div>
            </div>
        @endforelse
    </x-app-content>
</x-app-shell>
@endsection
