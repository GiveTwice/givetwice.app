@extends('layouts.public')

@section('title', __('Invalid Link'))

@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Invalid or Expired Link') }}</h1>

        <p class="text-gray-600 mb-6">
            {{ __('This confirmation link is invalid or has already been used.') }}
        </p>

        <a href="{{ url('/' . app()->getLocale()) }}" class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
            {{ __('Go to Homepage') }}
        </a>
    </div>
</div>
@endsection
