@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Dashboard') }}</h1>
    <p class="text-gray-600">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</p>
</div>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold text-gray-900">{{ __('Your Lists') }}</h2>
    <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        {{ __('Create List') }}
    </a>
</div>

@forelse($lists as $list)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b flex justify-between items-center">
            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="text-lg font-medium text-blue-600 hover:text-blue-800 hover:underline">
                {{ $list->name }}
                @if($list->is_default)
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-2">{{ __('Default') }}</span>
                @endif
            </a>
            <span class="text-sm text-gray-500">
                {{ trans_choice(':count gift|:count gifts', $list->gifts_count, ['count' => $list->gifts_count]) }}
            </span>
        </div>

        <div class="p-4">
            @if($list->gifts->isEmpty())
                <p class="text-gray-500 text-center py-8">
                    {{ __('No gifts in this list yet.') }}
                </p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($list->gifts->take(6) as $gift)
                        <x-gift-card :gift="$gift" />
                    @endforeach
                </div>

                @if($list->gifts_count > 6)
                    <div class="mt-4 text-center">
                        <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                            {{ __('View all :count gifts', ['count' => $list->gifts_count]) }}
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
@empty
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 mb-4">{{ __('You don\'t have any lists yet.') }}</p>
        <a href="{{ url('/' . app()->getLocale() . '/lists/create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            {{ __('Create your first list') }}
        </a>
    </div>
@endforelse

<a href="{{ url('/' . app()->getLocale() . '/gifts/create') }}" class="fixed bottom-6 right-6 bg-green-600 text-white w-14 h-14 rounded-full shadow-lg hover:bg-green-700 flex items-center justify-center" title="{{ __('Add Gift') }}">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
</a>
@endsection
