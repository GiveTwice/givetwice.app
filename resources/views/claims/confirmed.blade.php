@extends('layouts.public')

@section('title', __('Claim Confirmed'))

@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Claim Confirmed!') }}</h1>

        <p class="text-gray-600 mb-6">
            {{ __('You have successfully claimed this gift. The list owner will see that someone is getting it, but not who.') }}
        </p>

        <div class="bg-gray-50 rounded p-4 mb-6 text-left">
            <h2 class="font-medium text-gray-900">{{ $gift->title ?: __('Untitled gift') }}</h2>
            @if($gift->price)
                <p class="text-gray-600">{{ $gift->currency }} {{ number_format($gift->price, 2) }}</p>
            @endif
            @if($gift->url)
                <a href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-sm">
                    {{ __('View Product') }} &rarr;
                </a>
            @endif
        </div>

        <a href="{{ url('/' . app()->getLocale()) }}" class="inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            {{ __('Create your own wishlist') }}
        </a>
    </div>
</div>
@endsection
