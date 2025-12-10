@extends('layouts.public')

@section('title', __('Already Claimed'))

@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Gift Already Claimed') }}</h1>

        <p class="text-gray-600 mb-6">
            {{ __('Someone else has already claimed this gift while you were confirming.') }}
        </p>

        <a href="{{ url('/' . app()->getLocale()) }}" class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
            {{ __('Go to Homepage') }}
        </a>
    </div>
</div>
@endsection
