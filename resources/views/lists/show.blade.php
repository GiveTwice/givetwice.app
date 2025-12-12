@extends('layouts.app')

@section('title', $list->name)

@section('content')
<x-app-content
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => $list->name]
    ]"
>
    <x-slot:actions>
        <x-share-modal :list="$list" />
        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add a Gift') }}
        </a>
        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit') }}" class="btn-secondary">
            {{ __('Edit List') }}
        </a>
    </x-slot:actions>

    {{-- Custom title with badge --}}
    <div class="-mt-4 mb-6">
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $list->name }}</h1>
            @if($list->is_default)
                <span class="badge badge-success text-xs">{{ __('Default') }}</span>
            @endif
        </div>
        @if($list->description)
            <p class="text-gray-600">{{ $list->description }}</p>
        @endif
        <p class="text-sm text-gray-500 mt-2">
            {{ trans_choice(':count gift|:count gifts', $gifts->total(), ['count' => $gifts->total()]) }}
        </p>
    </div>

    {{-- Gifts Grid --}}
    @if($gifts->isEmpty())
        <div class="py-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">&#127873;</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No gifts yet') }}</h3>
                <p class="text-gray-500 mb-6">{{ __('Start building your wishlist by adding gifts from any online store.') }}</p>
                <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}?list={{ $list->id }}" class="btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add Your First Gift') }}
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($gifts as $gift)
                <x-gift-card :gift="$gift" :editable="true" />
            @endforeach
        </div>

        @if($gifts->hasPages())
            <div class="mt-8 pt-6 border-t border-gray-100">
                {{ $gifts->links() }}
            </div>
        @endif
    @endif
</x-app-content>
@endsection
