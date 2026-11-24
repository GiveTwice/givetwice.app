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
        @if($lists->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($lists as $list)
                    <x-list-card :list="$list" />
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
