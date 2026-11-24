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
    <x-app-content>
        <x-slot:titleSlot>
            <h1 class="text-[1.375rem] sm:text-2xl font-bold tracking-tight text-gray-900">
                {!! __('Welcome back, :name!', ['name' => '<span class="text-coral-600">' . e(auth()->user()->name) . '</span>']) !!}
            </h1>
            <p class="text-gray-600 mt-1">{{ __('Your wishlists, gift groups, and settings are all here.') }}</p>
        </x-slot:titleSlot>
        @if($lists->isNotEmpty())
            {{-- Mobile has no sidebar, so without this the only way to start a list is the card after every existing one. --}}
            <x-slot:actions>
                <a href="{{ route('lists.create', ['locale' => $currentLocale]) }}" class="btn-secondary whitespace-nowrap lg:hidden">
                    <x-icons.plus class="w-4 h-4" />
                    {{ __('Create List') }}
                </a>
            </x-slot:actions>
        @endif

        @if($lists->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($lists as $list)
                    <x-list-card :list="$list" :position="$shell['listPositions'][$list->id]" />
                @endforeach
                <x-new-list-card />
            </div>
        @else
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
        @endif
    </x-app-content>
</x-app-shell>
@endsection
