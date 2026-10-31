@extends('layouts.app')
@section('title', __('Your draw'))
@section('content')
<div class="max-w-lg mx-auto py-8 space-y-6">
    <p class="text-center text-sm text-gray-500 break-words">{{ $exchange->name }} · {{ $exchange->event_date?->format('d/m/Y') }} · {{ $exchange->formatBudget() }}</p>
    <div class="bg-white rounded-3xl shadow-sm border border-cream-200 p-6 space-y-5 text-center">
        @if(!$revealed)
            <h1 class="text-2xl font-bold break-words">{{ __('Hi :name!', ['name' => $participant->name]) }}</h1>
            <p class="text-gray-600">{{ __('Your draw is ready. Make sure nobody is peeking.') }}</p>
            <div class="text-6xl text-coral-500 py-6">?</div>
            <form method="POST" action="{{ route('exchanges.reveal.show', [app()->getLocale(), $participant->token]) }}">
                @csrf <button class="btn-primary w-full">{{ __('Show my draw') }}</button>
            </form>
        @else
            <p class="text-gray-600">{{ __('You\'re buying for...') }}</p>
            <h1 class="text-3xl font-bold text-coral-500 break-words">{{ $assignedTo->name }}</h1>
            @if($wishlist)
                <div class="grid grid-cols-2 gap-3 text-left">
                    @foreach($gifts as $gift)<x-gift-preview :gift="$gift" :show-description="false" :show-link="false" />@endforeach
                </div>
                <a href="{{ $wishlist->getPublicUrl(app()->getLocale()) }}" class="btn-primary w-full">{{ __('See wishlist') }}</a>
            @else
                <p class="text-gray-600">{{ $assignedTo->name }} {{ __('hasn\'t made a wishlist yet.') }}</p>
            @endif
            @if(!$participantHasWishlist)
                <div class="bg-sunny-50 rounded-xl p-4 space-y-3">
                    <h2 class="font-bold">{{ __('And what would you like?') }}</h2>
                    <p class="text-sm">{{ __('Link your list so whoever drew you knows what to buy.') }}</p>
                    <a href="{{ route('exchanges.wishlist', [app()->getLocale(), $participant->token]) }}" class="btn-primary">{{ __('Make or link my wishlist') }}</a>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
