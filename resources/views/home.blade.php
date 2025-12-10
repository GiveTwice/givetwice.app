@extends('layouts.app')

@section('title', __('Home'))

@section('content')
<div class="text-center py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Welcome to the Gifting App') }}</h1>
    <p class="text-lg text-gray-600 mb-8">{{ __('Create and share your wishlists. All affiliate revenue goes to charity.') }}</p>

    <div class="space-x-4">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ __('Get Started') }}
        </a>
    </div>
</div>
@endsection
