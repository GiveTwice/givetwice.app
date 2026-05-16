@extends('layouts.app')

@section('title', __('Secret Santa'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::secretSanta(auth()->user());
    $currentLocale = $shell['currentLocale'];
    $exchangeSlug = $shell['exchangeSlug'];
@endphp

<x-app-shell
    :title="__('Secret Santa')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
    <x-app-content
        :title="__('Secret Santa')"
        :description="__('Secret Santa groups, draws, and reveals')"
    >
        <x-slot:actions>
            <a href="{{ route('exchanges.landing', ['locale' => $currentLocale, 'exchangeType' => $exchangeSlug]) }}" class="btn-primary whitespace-nowrap">
                <x-icons.plus class="w-4 h-4" />
                {{ __('New group') }}
            </a>
        </x-slot:actions>

        @if($exchanges->isEmpty())
            <div class="py-16 text-center">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-cream-100">
                        <span class="text-4xl">&#127922;</span>
                    </div>
                    <h2 class="mb-2 text-xl font-semibold text-gray-900">{{ __('Secret Santa') }}</h2>
                    <p class="mb-6 text-gray-500">{{ __('Draw names. Buy gifts. Donate to charity. All without anyone knowing who got whom.') }}</p>
                    <a href="{{ route('exchanges.landing', ['locale' => $currentLocale, 'exchangeType' => $exchangeSlug]) }}" class="btn-primary">
                        {{ __('Start a group') }}
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($exchanges as $exchange)
                    <a href="{{ route('exchanges.status', ['locale' => $currentLocale, 'exchange' => $exchange->slug]) }}" class="block rounded-xl border border-cream-200 bg-white p-4 transition-all hover:border-coral-200 hover:shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900">{{ $exchange->name }}</p>
                                <div class="mt-1 flex flex-wrap gap-3 text-sm text-gray-500">
                                    @if($exchange->event_date)
                                        <span>📅 {{ $exchange->event_date->format('M j, Y') }}</span>
                                    @endif
                                    <span>👥 {{ $exchange->participants_count }}</span>
                                    @if($exchange->formatBudget())
                                        <span>💰 {{ $exchange->formatBudget() }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($exchange->isDrawn())
                                <span class="badge badge-success">{{ __('Names drawn') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('Draft') }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-app-content>
</x-app-shell>
@endsection
