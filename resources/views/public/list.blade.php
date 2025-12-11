@extends('layouts.app')

@section('title', $list->name . ' - ' . $list->user->name)

@php
    $isOwner = auth()->check() && auth()->id() === $list->user_id;
@endphp

@section('content')
{{-- List Header --}}
<div class="text-center mb-10">
    <p class="text-gray-500 mb-2">{{ __(':name\'s wishlist', ['name' => $list->user->name]) }}</p>
    <h1 class="text-4xl font-bold text-gray-900 mb-3">{{ $list->name }}</h1>
    @if($list->description)
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $list->description }}</p>
    @endif
</div>

{{-- Owner notice --}}
@if($isOwner)
    <div class="mb-8 p-4 bg-sunny-100 border border-sunny-200 rounded-xl text-center">
        <p class="text-sunny-800">
            {{ __('This is your list. You are viewing it as others will see it.') }}
            <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug) }}" class="underline font-medium hover:text-sunny-900">
                {{ __('Go to edit view') }}
            </a>
        </p>
    </div>
@endif

{{-- Gift count and instructions --}}
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <p class="text-gray-600 font-medium">
        {{ trans_choice(':count gift|:count gifts', $gifts->total(), ['count' => $gifts->total()]) }}
    </p>

    @unless($isOwner)
        <div class="text-sm text-gray-500 flex items-center gap-2">
            <span class="text-teal-500">&#10003;</span>
            {{ __('Click "I\'ll get this!" to claim a gift') }}
        </div>
    @endunless
</div>

{{-- Gifts Grid --}}
@if($gifts->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-12 text-center">
        <div class="text-6xl mb-4">&#127873;</div>
        <p class="text-gray-500 text-lg">{{ __('No gifts in this list yet.') }}</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($gifts as $gift)
            <x-gift-card :gift="$gift" :showClaimActions="true" :isOwner="$isOwner" :openModal="true" />
        @endforeach
    </div>

    {{-- Gift detail modals --}}
    @foreach($gifts as $gift)
        <x-gift-modal :gift="$gift" :isOwner="$isOwner" />
    @endforeach

    @if($gifts->hasPages())
        <div class="mt-8">
            {{ $gifts->links() }}
        </div>
    @endif
@endif

{{-- Charity CTA Section --}}
<div class="mt-12 bg-gradient-to-br from-coral-50 to-sunny-50 rounded-2xl p-8 text-center border border-coral-100">
    <div class="text-4xl mb-4">&#10084;&#65039;</div>
    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Gifting That Gives Back') }}</h2>
    <p class="text-gray-600 mb-6 max-w-lg mx-auto">{{ __('When you buy gifts through our links, we donate the affiliate commission to charity.') }}</p>
    @guest
        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="inline-flex items-center px-6 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
            {{ __('Create your own wishlist') }} <span class="ml-2">&#127873;</span>
        </a>
    @else
        <a href="{{ url('/' . app()->getLocale() . '/dashboard') }}" class="inline-flex items-center px-6 py-3 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-semibold transition-colors shadow-md hover:shadow-lg">
            {{ __('Go to My Wishlists') }} <span class="ml-2">&#127873;</span>
        </a>
    @endguest
</div>
@endsection
