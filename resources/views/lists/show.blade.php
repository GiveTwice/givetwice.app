@extends('layouts.app')

@section('title', $list->name)

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
@endphp

<x-app-shell
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
    <x-app-content
        :title="$list->name"
        :description="$list->description ?: trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count])"
        :breadcrumbs="[
            ['label' => __('Lists'), 'url' => $shell['dashboardUrl']],
            ['label' => $list->name],
        ]"
    >
        <x-slot:actions>
            <x-owner-list-actions :list="$list" />
        </x-slot:actions>

        <x-owner-list-gifts :list="$list" />
    </x-app-content>
</x-app-shell>
@endsection
