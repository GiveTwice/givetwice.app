@extends('layouts.app')

@section('title', $list->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="hover:text-gray-700">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ $list->name }}</span>
    </div>

    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $list->name }}
                @if($list->is_default)
                    <span class="text-sm bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-2 font-normal">{{ __('Default') }}</span>
                @endif
            </h1>
            @if($list->description)
                <p class="text-gray-600 mt-1">{{ $list->description }}</p>
            @endif
        </div>
        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
            {{ __('Edit List') }}
        </a>
    </div>
</div>

<div class="flex justify-between items-center mb-4">
    <p class="text-gray-500">
        {{ trans_choice(':count gift|:count gifts', $gifts->total(), ['count' => $gifts->total()]) }}
    </p>
</div>

@if($gifts->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 mb-4">{{ __('No gifts in this list yet.') }}</p>
        <a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            {{ __('Add your first gift') }}
        </a>
    </div>
@else
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($gifts as $gift)
                <x-gift-card :gift="$gift" />
            @endforeach
        </div>
    </div>

    @if($gifts->hasPages())
        <div class="mt-6">
            {{ $gifts->links() }}
        </div>
    @endif
@endif

<a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="fixed bottom-6 right-6 bg-green-600 text-white w-14 h-14 rounded-full shadow-lg hover:bg-green-700 flex items-center justify-center" title="{{ __('Add Gift') }}">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
</a>
@endsection
