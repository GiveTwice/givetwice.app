@extends('layouts.public')

@section('title', __('Claim Gift'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="mb-6">
        @if($list)
            <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="text-blue-600 hover:underline">
                &larr; {{ __('Back to list') }}
            </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-4">{{ __("I'll get this!") }}</h1>

        <div class="bg-gray-50 rounded p-4 mb-6">
            <h2 class="font-medium text-gray-900">{{ $gift->title ?: __('Untitled gift') }}</h2>
            @if($gift->price)
                <p class="text-gray-600">{{ $gift->currency }} {{ number_format($gift->price, 2) }}</p>
            @endif
        </div>

        <p class="text-gray-600 mb-4">
            {{ __('Enter your email to claim this gift. We\'ll send you a confirmation link.') }}
        </p>

        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim-anonymous') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }} *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror"
                    placeholder="{{ __('your@email.com') }}">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} ({{ __('optional') }})</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror"
                    placeholder="{{ __('Your name') }}">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    {{ __('Send Confirmation Email') }}
                </button>
                @if($list)
                    <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                        {{ __('Cancel') }}
                    </a>
                @endif
            </div>
        </form>

        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500 text-center">
                {{ __('Already have an account?') }}
                <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-blue-600 hover:underline">{{ __('Login') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
