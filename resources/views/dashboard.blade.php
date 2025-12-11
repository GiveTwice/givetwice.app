@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
{{-- Welcome header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</h1>
</div>

@if($isSingleListMode && $defaultList)
    {{-- SINGLE LIST MODE (90% of users) --}}
    {{-- Header with Add Gift button --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('Your Gifts') }}</h2>
            <span class="text-sm text-gray-500 bg-cream-100 px-2.5 py-0.5 rounded-full">
                {{ trans_choice(':count gift|:count gifts', $defaultList->gifts_count, ['count' => $defaultList->gifts_count]) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <x-share-modal :list="$defaultList" />
            <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add a Gift') }}
            </a>
        </div>
    </div>

    {{-- Gift grid or empty state --}}
    @if($defaultList->gifts->isEmpty())
        <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">&#127873;</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No gifts yet') }}</h3>
                <p class="text-gray-500 mb-6">{{ __('Start building your wishlist by adding gifts from any online store.') }}</p>
                <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add Your First Gift') }}
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($defaultList->gifts as $gift)
                <x-gift-card :gift="$gift" :editable="true" />
            @endforeach
        </div>
    @endif

    {{-- Create additional list CTA at bottom --}}
    <div class="mt-12 pt-8 border-t border-cream-200">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-cream-50 rounded-2xl p-6">
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Want more than one list?') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('You can create additional lists for different occasions or recipients.') }}</p>
            </div>
            <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Another List') }}
            </a>
        </div>
    </div>

@else
    {{-- MULTI-LIST MODE --}}
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Your Lists') }}</h2>
    </div>

    @forelse($lists as $list)
        <div class="bg-white rounded-2xl border border-cream-200 shadow-sm mb-8 overflow-hidden">
            {{-- List header --}}
            <div class="p-5 border-b border-cream-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-cream-50/50">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}"
                       class="text-lg font-semibold text-gray-900 hover:text-coral-600 transition-colors">
                        {{ $list->name }}
                    </a>
                    @if($list->is_default)
                        <span class="text-xs bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-medium">{{ __('Default') }}</span>
                    @endif
                    <span class="text-sm text-gray-500">
                        {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <x-share-modal :list="$list" />
                    <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add a Gift') }}
                    </a>
                </div>
            </div>

            {{-- List content --}}
            <div class="p-5">
                @if($list->gifts->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('No gifts in this list yet.') }}</p>
                        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="btn-link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add your first gift') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        @foreach($list->gifts->take(8) as $gift)
                            <x-gift-card :gift="$gift" :editable="true" />
                        @endforeach
                    </div>

                    @if($list->gifts_count > 8)
                        <div class="mt-6 text-center">
                            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}"
                               class="inline-flex items-center gap-2 text-coral-600 hover:text-coral-700 font-medium">
                                {{ __('View all :count gifts', ['count' => $list->gifts_count]) }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">&#127873;</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('You don\'t have any lists yet.') }}</h3>
                <p class="text-gray-500 mb-6">{{ __('Create your first wishlist to get started.') }}</p>
                <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-primary">
                    {{ __('Create your first list') }}
                </a>
            </div>
        </div>
    @endforelse

    {{-- Create new list CTA at bottom --}}
    @if($lists->isNotEmpty())
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-cream-50 rounded-2xl p-6">
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Need another list?') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('Create lists for different occasions or recipients.') }}</p>
            </div>
            <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create List') }}
            </a>
        </div>
    @endif
@endif
@endsection
