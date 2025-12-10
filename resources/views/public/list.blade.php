@extends('layouts.public')

@section('title', $list->name . ' - ' . $list->user->name . ' | ' . config('app.name'))
@section('description', $list->description ?: __(':name\'s wishlist on :app', ['name' => $list->user->name, 'app' => config('app.name')]))

@section('og_title', $list->name . ' - ' . $list->user->name)
@section('og_description', $list->description ?: __(':name\'s wishlist on :app', ['name' => $list->user->name, 'app' => config('app.name')]))
@if($list->cover_image)
@section('og_image', $list->cover_image)
@elseif($gifts->first()?->image_url)
@section('og_image', $gifts->first()->image_url)
@endif

@php
    $isOwner = auth()->check() && auth()->id() === $list->user_id;
@endphp

@section('content')
<div class="mb-8 text-center">
    <p class="text-gray-500 mb-2">{{ __(':name\'s wishlist', ['name' => $list->user->name]) }}</p>
    <h1 class="text-3xl font-bold text-gray-900">{{ $list->name }}</h1>
    @if($list->description)
        <p class="text-gray-600 mt-2 max-w-2xl mx-auto">{{ $list->description }}</p>
    @endif
</div>

@if($isOwner)
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
        <p class="text-blue-800">
            {{ __('This is your list. You are viewing it as others will see it.') }}
            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="underline font-medium">
                {{ __('Go to edit view') }}
            </a>
        </p>
    </div>
@endif

<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-500">
        {{ trans_choice(':count gift|:count gifts', $gifts->total(), ['count' => $gifts->total()]) }}
    </p>

    @unless($isOwner)
        <div class="text-sm text-gray-500">
            {{ __('Click "I\'ll get this!" to claim a gift') }}
        </div>
    @endunless
</div>

@if($gifts->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <p class="text-gray-500">{{ __('No gifts in this list yet.') }}</p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($gifts as $gift)
            <x-public-gift-card :gift="$gift" :isOwner="$isOwner" />
        @endforeach
    </div>

    @if($gifts->hasPages())
        <div class="mt-6">
            {{ $gifts->links() }}
        </div>
    @endif
@endif

<div class="mt-12 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 text-center">
    <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('All affiliate revenue goes to charity') }}</h2>
    <p class="text-gray-600 mb-4">{{ __('When you buy gifts through our links, we donate the affiliate commission to charity.') }}</p>
    <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
        {{ __('Create your own wishlist') }}
    </a>
</div>
@endsection
