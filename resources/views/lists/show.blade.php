@extends('layouts.app')

@section('title', $list->name)

@section('content')
{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-coral-600 transition-colors">{{ __('Dashboard') }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-medium">{{ $list->name }}</span>
</div>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $list->name }}</h1>
            @if($list->is_default)
                <span class="text-xs bg-teal-100 text-teal-700 px-2.5 py-1 rounded-full font-medium">{{ __('Default') }}</span>
            @endif
        </div>
        @if($list->description)
            <p class="text-gray-600 mt-2">{{ $list->description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        <x-share-modal :list="$list" />
        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add a Gift') }}
        </a>
        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit') }}" class="btn-secondary">
            {{ __('Edit List') }}
        </a>
    </div>
</div>

{{-- Gift count --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-gray-500 font-medium">
        {{ trans_choice(':count gift|:count gifts', $gifts->total(), ['count' => $gifts->total()]) }}
    </p>
</div>

{{-- Gifts Grid --}}
@if($gifts->isEmpty())
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
        @foreach($gifts as $gift)
            <x-gift-card :gift="$gift" :editable="true" />
        @endforeach
    </div>

    @if($gifts->hasPages())
        <div class="mt-8">
            {{ $gifts->links() }}
        </div>
    @endif
@endif
@endsection
