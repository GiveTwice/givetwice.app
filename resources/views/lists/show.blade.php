@extends('layouts.app')

@section('title', $list->name)

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user(), $list);
    $colour = \App\Support\ListColour::forPosition($shell['listPositions'][$list->id] ?? 0);
@endphp

<x-app-shell
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
    <x-app-content
        :breadcrumbs="[
            ['label' => __('Lists'), 'url' => $shell['dashboardUrl']],
            ['label' => $list->name],
        ]"
    >
        <x-slot:titleSlot>
            <h1 class="text-[1.375rem] sm:text-2xl font-bold tracking-tight text-gray-900">
                <x-ribbon-underline class="{{ $colour['underline'] }}"><span class="text-gray-900">{{ $list->name }}</span></x-ribbon-underline>
            </h1>
            <p class="text-gray-600 mt-1.5">{{ $list->description ?: trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}</p>
        </x-slot:titleSlot>

        <x-slot:actions>
            <x-owner-list-actions :list="$list" />
        </x-slot:actions>

        <x-owner-list-gifts :list="$list" />
    </x-app-content>
</x-app-shell>
@endsection
